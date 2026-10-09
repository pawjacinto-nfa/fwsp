<?php
declare(strict_types=1);

namespace App\Support;

use InvalidArgumentException;

/** Reads dump literals only. Uploaded SQL is never executed. */
final class SqlBackupReader
{
    public static function statements(string $file): \Generator
    {
        $stream = fopen($file, 'rb');
        if (!$stream) throw new InvalidArgumentException('The uploaded file could not be read.');
        $statement = ''; $quote = ''; $block = false; $lineComment = false; $escape = false; $comment = '';
        try {
            while (($line = fgets($stream, 32 * 1024 * 1024 + 2)) !== false) {
                if (strlen($line) > 32 * 1024 * 1024) throw new InvalidArgumentException('A SQL line exceeds 32 MB. Export smaller INSERT batches.');
                for ($i = 0, $length = strlen($line); $i < $length; $i++) {
                    $char = $line[$i]; $next = $line[$i + 1] ?? '';
                    if ($lineComment) { if ($char === "\n") $lineComment = false; continue; }
                    if ($block) {
                        if ($char === '*' && $next === '/') {
                            $block = false; $i++;
                            if (preg_match('/^(?:!|M!)/', $comment)) {
                                $directive = trim(preg_replace('/^(?:!|M!)\d*\s*/', '', $comment));
                                if (!preg_match('/^(?:SET\s|ALTER TABLE\s+`?[a-zA-Z0-9_]+`?\s+(?:DISABLE|ENABLE) KEYS$)/i', $directive)
                                    || stripos($directive, 'NO_BACKSLASH_ESCAPES') !== false) throw new InvalidArgumentException('Unsupported executable SQL comment. Export plain tables without routines, triggers, or special SQL modes.');
                            }
                            $comment = '';
                        } else {
                            $comment .= $char;
                            if (strlen($comment) > 1024 * 1024) throw new InvalidArgumentException('SQL comment is too large.');
                        }
                        continue;
                    }
                    if ($quote !== '') {
                        $statement .= $char;
                        if ($escape) { $escape = false; continue; }
                        if ($char === '\\' && $quote !== '`') { $escape = true; continue; }
                        if ($char === $quote) {
                            if ($next === $quote) { $statement .= $next; $i++; } else $quote = '';
                        }
                    } elseif ($char === "'" || $char === '"' || $char === '`') {
                        $quote = $char; $statement .= $char;
                    } elseif (($char === '-' && $next === '-' && ctype_space($line[$i + 2] ?? ' ')) || $char === '#') {
                        $lineComment = true; $statement .= ' ';
                    } elseif ($char === '/' && $next === '*') {
                        $block = true; $statement .= ' '; $i++;
                    } elseif ($char === ';') {
                        if (trim($statement) !== '') yield trim($statement);
                        $statement = '';
                    } else $statement .= $char;
                    if (strlen($statement) > 32 * 1024 * 1024) throw new InvalidArgumentException('A SQL statement exceeds the 32 MB safety limit. Export with smaller INSERT batches.');
                }
            }
            if ($quote !== '' || $block) throw new InvalidArgumentException('The SQL file is incomplete or has an unterminated value.');
            if (trim($statement) !== '') yield trim($statement);
        } finally { fclose($stream); }
    }

    public static function identifier(string $value): string
    {
        $value = trim($value, " `\t\r\n");
        if (!preg_match('/^[a-zA-Z0-9_]+$/D', $value)) throw new InvalidArgumentException('The dump contains an unsupported or qualified table/column name.');
        return $value;
    }

    public static function splitDefinitions(string $text): array
    {
        $parts = []; $start = 0; $depth = 0; $quote = '';
        for ($i = 0, $n = strlen($text); $i < $n; $i++) {
            $ch = $text[$i];
            if ($quote !== '') {
                if ($ch === '\\') { $i++; continue; }
                if ($ch === $quote) { if (($text[$i + 1] ?? '') === $quote) $i++; else $quote = ''; }
            } elseif (in_array($ch, ["'", '"', '`'], true)) $quote = $ch;
            elseif ($ch === '(') $depth++;
            elseif ($ch === ')') $depth--;
            elseif ($ch === ',' && $depth === 0) { $parts[] = trim(substr($text, $start, $i - $start)); $start = $i + 1; }
        }
        $parts[] = trim(substr($text, $start));
        return $parts;
    }

    /** @return \Generator<array<int,?string>> */
    public static function rows(string $values): \Generator
    {
        $i = 0; $n = strlen($values);
        $space = static function () use (&$i, $n, $values): void { while ($i < $n && ctype_space($values[$i])) $i++; };
        while ($i < $n) {
            $space();
            if (($values[$i++] ?? '') !== '(') throw new InvalidArgumentException('Unsupported INSERT syntax. Only literal VALUES rows are accepted.');
            $row = [];
            while (true) {
                $space(); $ch = $values[$i] ?? ''; $value = '';
                if ($ch === "'") {
                    $i++; $closed = false;
                    while ($i < $n) {
                        $ch = $values[$i++];
                        if ($ch === "'") {
                            if (($values[$i] ?? '') === "'") { $value .= "'"; $i++; continue; }
                            $closed = true; break;
                        }
                        if ($ch === '\\') {
                            $ch = $values[$i++] ?? '';
                            $value .= match ($ch) { '0' => "\0", 'n' => "\n", 'r' => "\r", 't' => "\t", 'b' => "\x08", 'Z' => "\x1a", '_', '%' => '\\' . $ch, default => $ch };
                        } else $value .= $ch;
                    }
                    if (!$closed) throw new InvalidArgumentException('An INSERT string is incomplete.');
                } elseif (preg_match('/\G(?:0x([0-9a-f]+)|[xX]\x27([0-9a-f]*)\x27)/Ai', $values, $match, 0, $i)) {
                    $hex = $match[1] !== '' ? $match[1] : ($match[2] ?? '');
                    if (strlen($hex) % 2) $hex = '0' . $hex;
                    $value = hex2bin($hex); $i += strlen($match[0]);
                } elseif (preg_match('/\GNULL\b/Ai', $values, $match, 0, $i)) {
                    $value = null; $i += 4;
                } elseif (preg_match('/\G[+-]?(?:\d+(?:\.\d*)?|\.\d+)(?:[eE][+-]?\d+)?/A', $values, $match, 0, $i)) {
                    $value = $match[0]; $i += strlen($match[0]);
                } else throw new InvalidArgumentException('An INSERT contains an expression or unsupported literal. Export plain INSERT statements.');
                $row[] = $value; $space(); $separator = $values[$i++] ?? '';
                if ($separator === ')') break;
                if ($separator !== ',') throw new InvalidArgumentException('An INSERT row is malformed.');
            }
            yield $row; $space();
            if ($i === $n) break;
            if (($values[$i++] ?? '') !== ',') throw new InvalidArgumentException('Extra SQL after INSERT values is not allowed.');
            $space();
            if ($i === $n) throw new InvalidArgumentException('The INSERT ends with an incomplete row.');
        }
    }
}
