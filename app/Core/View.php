<?php
declare(strict_types=1);

namespace App\Core;

final class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        require BASE_PATH . '/app/Views/' . $view . '.php';
        $content = ob_get_clean();

        ob_start();
        require BASE_PATH . '/app/Views/layout.php';
        $html = ob_get_clean();

        echo preg_replace_callback(
            '/<form\b(?=[^>]*\bmethod\s*=\s*(["\']?)post\1)[^>]*>/i',
            static fn (array $match): string => $match[0] . csrf_field(),
            $html
        ) ?? $html;
    }
}
