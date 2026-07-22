<?php
declare(strict_types=1);

namespace App\Models;

use App\Core\Database;

final class DisplayPhoto
{
    public static function slides(): array
    {
        self::ensureSchema();
        return Database::connection()->query("SELECT *, COALESCE(optimized_path, image_path) AS display_path FROM display_photos WHERE status = 'Approved' ORDER BY position ASC, id ASC")->fetchAll();
    }

    public static function all(): array
    {
        self::ensureSchema();
        return Database::connection()->query("SELECT p.*, u.full_name AS submitter_name FROM display_photos p LEFT JOIN users u ON u.id = p.submitted_by ORDER BY FIELD(p.status, 'Pending', 'Approved', 'Rejected'), p.created_at DESC")->fetchAll();
    }

    public static function settings(): array
    {
        self::ensureSchema();
        return Database::connection()->query('SELECT * FROM display_settings WHERE id = 1')->fetch() ?: ['loop_duration' => 7, 'panning_enabled' => 1];
    }

    public static function create(int $userId, string $title, string $photographer, string $location, string $path, int $width, int $height): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("INSERT INTO display_photos (submitted_by, title, photographer_name, location, image_path, source, image_width, image_height, status) VALUES (:submitted_by, :title, :photographer, :location, :path, 'User submission', :width, :height, 'Pending')");
        $stmt->execute(['submitted_by' => $userId, 'title' => $title, 'photographer' => $photographer, 'location' => $location, 'path' => $path, 'width' => $width, 'height' => $height]);
    }

    public static function review(int $id, string $status, int $position, ?string $optimizedPath): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare("UPDATE display_photos SET status = :status, position = :position, optimized_path = COALESCE(:optimized_path, optimized_path), reviewed_at = NOW() WHERE id = :id");
        $stmt->execute(['id' => $id, 'status' => $status, 'position' => $position, 'optimized_path' => $optimizedPath]);
    }

    public static function updatePosition(int $id, int $position): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('UPDATE display_photos SET position = :position WHERE id = :id');
        $stmt->execute(['id' => $id, 'position' => $position]);
    }

    public static function updateSettings(int $duration, bool $panning): void
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('UPDATE display_settings SET loop_duration = :duration, panning_enabled = :panning WHERE id = 1');
        $stmt->execute(['duration' => $duration, 'panning' => $panning ? 1 : 0]);
    }

    public static function find(int $id): ?array
    {
        self::ensureSchema();
        $stmt = Database::connection()->prepare('SELECT * FROM display_photos WHERE id = :id LIMIT 1');
        $stmt->execute(['id' => $id]);
        return $stmt->fetch() ?: null;
    }

    private static function ensureSchema(): void
    {
        static $ready = false;
        if ($ready) return;
        $db = Database::connection();
        $db->exec("CREATE TABLE IF NOT EXISTS display_photos (id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, submitted_by BIGINT UNSIGNED NULL, title VARCHAR(160) NOT NULL, photographer_name VARCHAR(160) NOT NULL, location VARCHAR(160) NOT NULL DEFAULT '', image_path VARCHAR(255) NOT NULL, optimized_path VARCHAR(255) NULL, source VARCHAR(80) NOT NULL DEFAULT 'User submission', image_width INT UNSIGNED NULL, image_height INT UNSIGNED NULL, position INT UNSIGNED NOT NULL DEFAULT 999, status ENUM('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending', reviewed_at TIMESTAMP NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, INDEX display_photo_status_position (status, position), FOREIGN KEY (submitted_by) REFERENCES users(id) ON DELETE SET NULL)");
        $db->exec("CREATE TABLE IF NOT EXISTS display_settings (id TINYINT UNSIGNED PRIMARY KEY, loop_duration TINYINT UNSIGNED NOT NULL DEFAULT 7, panning_enabled TINYINT(1) NOT NULL DEFAULT 1)");
        $db->exec('INSERT IGNORE INTO display_settings (id, loop_duration, panning_enabled) VALUES (1, 7, 1)');
        $defaults = [
            ['Rainy-day rice field', 'Ruth Bolano', 'assets/images/landing-slides/palay-01.avif'], ['Rice at dusk', 'Stijn Dijkstra', 'assets/images/landing-slides/palay-02.avif'], ['Terraced fields', 'Charlie Dogaong', 'assets/images/landing-slides/palay-03.avif'], ['Morning over the fields', 'Aria Batula', 'assets/images/landing-slides/palay-04.avif'], ['Aerial rice landscape', 'Bobby Mc Gee Lee', 'assets/images/landing-slides/palay-05.avif'], ['Working the paddy', 'Dave', 'assets/images/landing-slides/palay-06.avif'], ['After the rain', 'Denniz Futalan', 'assets/images/landing-slides/palay-07.avif'], ['Watered terraces', 'Dada', 'assets/images/landing-slides/palay-08.avif'], ['Planting season', 'Neil Clark Ongchangco', 'assets/images/landing-slides/palay-09.avif'], ['Fields in afternoon light', 'XT7CORE', 'assets/images/landing-slides/palay-10.avif'],
        ];
        $insert = $db->prepare("INSERT INTO display_photos (title, photographer_name, image_path, source, position, status) SELECT :title, :photographer, :path, 'Pexels', :position, 'Approved' FROM DUAL WHERE NOT EXISTS (SELECT 1 FROM display_photos WHERE image_path = :path_check)");
        foreach ($defaults as $position => [$title, $photographer, $path]) $insert->execute(['title' => $title, 'photographer' => $photographer, 'path' => $path, 'position' => $position + 1, 'path_check' => $path]);
        $ready = true;
    }
}
