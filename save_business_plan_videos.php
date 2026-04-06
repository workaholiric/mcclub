<?php
declare(strict_types=1);

session_start();
require_once __DIR__ . '/db_config.php';
require_once __DIR__ . '/ngc_site_settings.php';

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: funnel.php');
    exit;
}

$id1 = ngc_youtube_id_from_input($_POST['video_compliance'] ?? '');
$id2 = ngc_youtube_id_from_input($_POST['video_passion'] ?? '');
$id3 = ngc_youtube_id_from_input($_POST['video_earn'] ?? '');

try {
    ngc_site_setting_set($pdo, 'business_plan_video_compliance', $id1);
    ngc_site_setting_set($pdo, 'business_plan_video_passion', $id2);
    ngc_site_setting_set($pdo, 'business_plan_video_earn', $id3);
} catch (PDOException $e) {
    error_log('save_business_plan_videos: ' . $e->getMessage());
}

header('Location: funnel.php?videos_saved=1');
exit;
