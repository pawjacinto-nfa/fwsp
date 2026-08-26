<?php
declare(strict_types=1);

namespace App\Support;

use Endroid\QrCode\Color\Color;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\SvgWriter;

final class QrCode
{
    public static function dataUri(string $data): string
    {
        self::loadComposerAutoloader();
        if (!class_exists(EndroidQrCode::class) || !class_exists(SvgWriter::class)) {
            error_log('QR generator unavailable. Run Composer install in ' . BASE_PATH . '.');
            return self::unavailableDataUri();
        }

        $qrCode = new EndroidQrCode(
            data: $data,
            errorCorrectionLevel: ErrorCorrectionLevel::Medium,
            size: 240,
            margin: 12,
            roundBlockSizeMode: RoundBlockSizeMode::None,
            foregroundColor: new Color(23, 35, 59),
        );

        return (new SvgWriter())->write($qrCode)->getDataUri();
    }

    public static function isAvailable(): bool
    {
        self::loadComposerAutoloader();
        return class_exists(EndroidQrCode::class) && class_exists(SvgWriter::class);
    }

    private static function loadComposerAutoloader(): void
    {
        if (class_exists(EndroidQrCode::class, false)) return;
        $autoload = BASE_PATH . '/vendor/autoload.php';
        if (is_file($autoload)) require_once $autoload;
    }

    /** Keeps the printable short URL usable instead of failing the entire form. */
    private static function unavailableDataUri(): string
    {
        $svg = <<<'SVG'
<svg xmlns="http://www.w3.org/2000/svg" width="240" height="240" viewBox="0 0 240 240">
  <rect width="240" height="240" rx="18" fill="#f6f8fb" stroke="#b8c3d2" stroke-width="6"/>
  <path d="M58 58h42v12H70v30H58zm82 0h42v42h-12V70h-30zm42 82v42h-42v-12h30v-30zM100 182H58v-42h12v30h30z" fill="#27317c"/>
  <text x="120" y="116" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" font-weight="700" fill="#27317c">USE SHORT</text>
  <text x="120" y="140" text-anchor="middle" font-family="Arial,sans-serif" font-size="18" font-weight="700" fill="#27317c">LINK</text>
</svg>
SVG;
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }
}
