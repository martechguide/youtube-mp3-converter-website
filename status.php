<?php
/**
 * Status checker for conversion progress
 */
session_start();

// Configuration
define('UPLOADS_DIR', './uploads/');
define('STATUS_DIR', './status/');

// Create status directory if it doesn't exist
if (!file_exists(STATUS_DIR)) {
    mkdir(STATUS_DIR, 0755, true);
}

header('Content-Type: application/json');

if (!isset($_GET['video_id'])) {
    echo json_encode(['success' => false, 'message' => 'Video ID required']);
    exit;
}

$video_id = basename($_GET['video_id']);
$status_file = STATUS_DIR . $video_id . '.json';
$mp3_file = UPLOADS_DIR . $video_id . '.mp3';

// Check if conversion is complete
if (file_exists($mp3_file) && filesize($mp3_file) > 0) {
    // Remove status file as conversion is complete
    if (file_exists($status_file)) {
        unlink($status_file);
    }
    
    echo json_encode([
        'success' => true,
        'status' => 'complete',
        'progress' => 100,
        'download_url' => '/download.php?file=' . $video_id
    ]);
    exit;
}

// Check status file for progress
if (file_exists($status_file)) {
    $status_data = json_decode(file_get_contents($status_file), true);
    echo json_encode([
        'success' => true,
        'status' => $status_data['status'] ?? 'processing',
        'progress' => $status_data['progress'] ?? 0,
        'message' => $status_data['message'] ?? 'Converting...'
    ]);
} else {
    echo json_encode([
        'success' => true,
        'status' => 'starting',
        'progress' => 0,
        'message' => 'Starting conversion...'
    ]);
}
?>