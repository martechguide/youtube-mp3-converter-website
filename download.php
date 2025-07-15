<?php
/**
 * Download handler for converted MP3 files
 */

session_start();

// Configuration
define('UPLOADS_DIR', './uploads/');

// Check if file parameter is provided
if (!isset($_GET['file'])) {
    http_response_code(400);
    die('File parameter is required');
}

// Sanitize file name
$file_id = basename($_GET['file']);
$file_path = UPLOADS_DIR . $file_id . '.mp3';

// Check if file exists
if (!file_exists($file_path)) {
    http_response_code(404);
    die('File not found');
}

// Get file info
$file_size = filesize($file_path);

// Try to get the actual video title for filename from multiple sources
$video_title = 'YouTube_Audio';

// Check status file
$status_file = './status/' . $file_id . '.json';
if (file_exists($status_file)) {
    $status_data = json_decode(file_get_contents($status_file), true);
    if (isset($status_data['title']) && !empty($status_data['title'])) {
        $video_title = $status_data['title'];
    }
}

// If still no title, try to get it using yt-dlp directly
if ($video_title === 'YouTube_Audio') {
    $url = "https://www.youtube.com/watch?v=" . $file_id;
    $command = "timeout 10 python3 -m yt_dlp --get-title --no-warnings " . escapeshellarg($url) . " 2>/dev/null";
    $title_output = shell_exec($command);
    if ($title_output && trim($title_output)) {
        $video_title = trim($title_output);
    }
}

// Clean filename and ensure it's safe
$safe_title = preg_replace('/[^a-zA-Z0-9\-_\s]/', '', $video_title);
$safe_title = trim(preg_replace('/\s+/', '_', $safe_title));
$safe_title = substr($safe_title, 0, 50); // Limit length
$file_name = $safe_title . '.mp3';

// Set headers for download
header('Content-Type: audio/mpeg');
header('Content-Disposition: attachment; filename="' . $file_name . '"');
header('Content-Length: ' . $file_size);
header('Cache-Control: must-revalidate');
header('Pragma: public');

// Output file
readfile($file_path);

// Optional: Delete file after download (uncomment if desired)
// unlink($file_path);

exit;
?>