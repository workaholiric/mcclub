<?php

declare(strict_types=1);

function ngc_site_settings_ensure_table(PDO $pdo): void
{
    static $done = false;
    if ($done) {
        return;
    }
    $done = true;
    $pdo->exec(
        'CREATE TABLE IF NOT EXISTS site_settings (
            setting_key VARCHAR(64) NOT NULL PRIMARY KEY,
            setting_value TEXT,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4'
    );
}

function ngc_site_setting_get(PDO $pdo, string $key, string $default = ''): string
{
    try {
        ngc_site_settings_ensure_table($pdo);
        $st = $pdo->prepare('SELECT setting_value FROM site_settings WHERE setting_key = ?');
        $st->execute([$key]);
        $v = $st->fetchColumn();
        return $v !== false ? (string) $v : $default;
    } catch (PDOException $e) {
        error_log('ngc_site_setting_get: ' . $e->getMessage());
        return $default;
    }
}

function ngc_site_setting_set(PDO $pdo, string $key, string $value): void
{
    ngc_site_settings_ensure_table($pdo);
    $st = $pdo->prepare(
        'INSERT INTO site_settings (setting_key, setting_value) VALUES (?, ?)
         ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)'
    );
    $st->execute([$key, $value]);
}

/** Accepts 11-char ID or full youtube URL; returns 11-char ID or ''. */
function ngc_youtube_id_from_input(string $input): string
{
    $input = trim($input);
    if ($input === '') {
        return '';
    }
    if (preg_match('/^[a-zA-Z0-9_-]{11}$/', $input)) {
        return $input;
    }
    if (preg_match('#[?&]v=([a-zA-Z0-9_-]{11})#', $input, $m)) {
        return $m[1];
    }
    if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $input, $m)) {
        return $m[1];
    }
    if (preg_match('#youtube\.com/embed/([a-zA-Z0-9_-]{11})#', $input, $m)) {
        return $m[1];
    }
    if (preg_match('#youtube\.com/shorts/([a-zA-Z0-9_-]{11})#', $input, $m)) {
        return $m[1];
    }
    return '';
}

function ngc_youtube_embed_html(string $videoId): string
{
    if (!preg_match('/^[a-zA-Z0-9_-]{11}$/', $videoId)) {
        return '';
    }
    $id = htmlspecialchars($videoId, ENT_QUOTES, 'UTF-8');
    $src = 'https://www.youtube-nocookie.com/embed/' . $id . '?rel=0&modestbranding=1';
    return '<div class="relative w-full overflow-hidden rounded-2xl border border-amber-500/20 bg-black shadow-[0_0_40px_rgba(245,158,11,0.12)]" style="padding-top:56.25%">'
        . '<iframe class="absolute top-0 left-0 h-full w-full" src="' . htmlspecialchars($src, ENT_QUOTES, 'UTF-8') . '" title="YouTube video" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe></div>';
}
