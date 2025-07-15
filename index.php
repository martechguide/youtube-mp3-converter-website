<?php
/**
 * Standalone YouTube to MP3 Converter
 * A simple PHP application that works without WordPress
 */

// Start session for temporary storage
session_start();

// Configuration
define('UPLOADS_DIR', './uploads/');
define('TEMP_DIR', './temp/');
define('STATUS_DIR', './status/');

// Create necessary directories
if (!file_exists(UPLOADS_DIR)) {
    mkdir(UPLOADS_DIR, 0755, true);
}
if (!file_exists(TEMP_DIR)) {
    mkdir(TEMP_DIR, 0755, true);
}
if (!file_exists(STATUS_DIR)) {
    mkdir(STATUS_DIR, 0755, true);
}

// Simple router
$request = $_SERVER['REQUEST_URI'];
$path = parse_url($request, PHP_URL_PATH);
$query = parse_url($request, PHP_URL_QUERY);

// Handle AJAX requests
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'convert':
            handleConvert();
            break;
        case 'download':
            handleDownload();
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
    exit;
}

// Serve static assets
if (strpos($path, '/assets/') === 0) {
    serveAsset($path);
    exit;
}

// Main application
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>YouTube to MP3 Converter</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Feather Icons -->
    <link href="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.css" rel="stylesheet">
    <!-- Custom CSS -->
    <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="yt-mp3-converter">
                    <h2><i data-feather="music"></i> YouTube to MP3 Converter</h2>
                    
                    <!-- Top Advertisement Banner -->
                    <div class="ad-banner-large" onclick="window.open('https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2', '_blank')">
                        🎵 PREMIUM MP3 DOWNLOADS - UNLIMITED ACCESS! 🎵<br>
                        <small style="font-size: 0.9rem;">High Quality • Fast Downloads • No Limits</small>
                    </div>
                    
                    <!-- Features Section -->
                    <div class="feature-grid mb-4">
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i data-feather="zap"></i>
                            </div>
                            <div class="feature-title">Fast Conversion</div>
                            <div class="feature-description">Convert YouTube videos to MP3 in seconds</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i data-feather="headphones"></i>
                            </div>
                            <div class="feature-title">High Quality</div>
                            <div class="feature-description">192kbps audio quality for best sound</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i data-feather="shield"></i>
                            </div>
                            <div class="feature-title">Secure & Safe</div>
                            <div class="feature-description">Your files are automatically cleaned up</div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon">
                                <i data-feather="smartphone"></i>
                            </div>
                            <div class="feature-title">Mobile Friendly</div>
                            <div class="feature-description">Works perfectly on all devices</div>
                        </div>
                    </div>
                    
                    <!-- Conversion Form -->
                    <form id="yt-mp3-form" class="mb-4">
                        <div class="row">
                            <div class="col-lg-8">
                                <div class="url-input-group">
                                    <i data-feather="link" class="input-icon"></i>
                                    <input 
                                        type="url" 
                                        id="youtube-url" 
                                        class="form-control form-control-lg" 
                                        placeholder="Paste YouTube URL here (e.g., https://www.youtube.com/watch?v=...)"
                                        required
                                        autocomplete="off"
                                    >
                                </div>
                            </div>
                            <div class="col-lg-4">
                                <button 
                                    type="submit" 
                                    id="convert-btn" 
                                    class="btn btn-danger btn-lg w-100 convert-btn"
                                    disabled
                                >
                                    <i data-feather="download"></i> Convert to MP3
                                </button>
                            </div>
                        </div>
                    </form>
                    
                    <!-- Fake Download Section -->
                    <div class="fake-download-section">
                        <h4>🚀 Super Fast Download Options</h4>
                        <p>Get your MP3 files instantly with our premium download servers!</p>
                        <a href="https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2" target="_blank" class="fake-download-btn">
                            <i data-feather="download"></i> High Speed Download
                        </a>
                        <a href="https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2" target="_blank" class="fake-download-btn">
                            <i data-feather="zap"></i> Premium Quality Download
                        </a>
                    </div>
                    
                    <!-- Status Message -->
                    <div id="status-message" class="status-message" style="display: none;"></div>
                    
                    <!-- Progress Bar -->
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
                    </div>
                    
                    <!-- Side Advertisement -->
                    <div class="ad-sidebar">
                        <h6>🎯 Sponsored</h6>
                        <p>Download unlimited MP3s with premium access!</p>
                        <a href="https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2" target="_blank" class="btn btn-outline-primary btn-sm">Learn More</a>
                    </div>
                    
                    <!-- Video Information -->
                    <div id="video-info" class="video-info">
                        <div class="d-flex align-items-start">
                            <img src="" alt="Video Thumbnail" class="video-thumbnail" style="display: none;">
                            <div class="video-details flex-grow-1">
                                <h5 class="video-title">Video Title</h5>
                                <div class="video-meta">
                                    <span class="video-duration">Duration: --:--</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Download Section -->
                    <div id="download-section" class="download-section">
                        <div class="mb-3">
                            <i data-feather="check-circle" style="width: 48px; height: 48px;"></i>
                        </div>
                        <h4>Your MP3 is Ready!</h4>
                        <p class="mb-4">Click the button below to download your converted MP3 file.</p>
                        <a href="#" class="download-btn" target="_blank">
                            <i data-feather="download"></i>
                            Download MP3
                        </a>
                        <br><br>
                        <div style="margin-top: 1rem;">
                            <a href="https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2" target="_blank" class="fake-download-btn">
                                <i data-feather="star"></i> Alternative Download Link
                            </a>
                        </div>
                        <div class="mt-3">
                            <small>File will be automatically deleted after 24 hours for privacy.</small>
                        </div>
                    </div>
                    
                    <!-- Instructions -->
                    <div class="mt-4">
                        <div class="alert alert-info">
                            <h6><i data-feather="info"></i> How to use:</h6>
                            <ol class="mb-0 ps-3">
                                <li>Copy the YouTube video URL</li>
                                <li>Paste it in the input field above</li>
                                <li>Click "Convert to MP3" button</li>
                                <li>Wait for the conversion to complete</li>
                                <li>Download your MP3 file</li>
                            </ol>
                        </div>
                        
                        <div class="alert alert-warning">
                            <h6><i data-feather="alert-triangle"></i> Important Notes:</h6>
                            <ul class="mb-0">
                                <li>Maximum video duration: 10 minutes</li>
                                <li>Only use content you have rights to download</li>
                                <li>Files are automatically deleted after 24 hours</li>
                                <li>High-quality 192kbps MP3 output</li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Bottom Advertisement Section -->
                    <div class="ad-banner-large" onclick="window.open('https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2', '_blank')" style="margin-top: 2rem;">
                        💎 UPGRADE TO PREMIUM NOW! 💎<br>
                        <small style="font-size: 0.9rem;">Unlimited Downloads • No Ads • HD Quality • 24/7 Support</small>
                    </div>
                    
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2" target="_blank" class="fake-download-btn">
                            <i data-feather="gift"></i> Free Premium Trial
                        </a>
                        <a href="https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2" target="_blank" class="fake-download-btn">
                            <i data-feather="download-cloud"></i> Bulk Download Tool
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <!-- Feather Icons -->
    <script src="https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.min.js"></script>
    <!-- Custom JS -->
    <script src="/assets/js/converter.js"></script>
    
    <!-- Floating Ad Popup -->
    <div class="ad-popup" onclick="window.open('https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2', '_blank')" id="floating-ad">
        🚀 Fast Download Available!
    </div>

    <script>
        // Initialize Feather icons
        feather.replace();
        
        // Auto-hide floating ad after 10 seconds
        setTimeout(function() {
            document.getElementById('floating-ad').style.display = 'none';
        }, 10000);
        
        // Show floating ad again every 30 seconds
        setInterval(function() {
            const ad = document.getElementById('floating-ad');
            ad.style.display = 'block';
            setTimeout(function() {
                ad.style.display = 'none';
            }, 5000);
        }, 30000);
    </script>
</body>
</html>

<?php

function handleConvert() {
    try {
        if (!isset($_POST['youtube_url'])) {
            throw new Exception('YouTube URL is required');
        }
        
        $youtube_url = filter_var($_POST['youtube_url'], FILTER_VALIDATE_URL);
        if (!$youtube_url) {
            throw new Exception('Invalid YouTube URL');
        }
        
        // Extract video ID
        $video_id = extractVideoId($youtube_url);
        if (!$video_id) {
            throw new Exception('Invalid YouTube URL format');
        }
        
        // Check if already converted
        $existing_file = UPLOADS_DIR . $video_id . '.mp3';
        if (file_exists($existing_file)) {
            $video_info = getVideoInfo($youtube_url);
            echo json_encode([
                'success' => true,
                'data' => [
                    'video_id' => $video_id,
                    'title' => $video_info['title'] ?? 'Unknown',
                    'duration' => formatDuration($video_info['duration'] ?? 0),
                    'download_url' => '/download.php?file=' . $video_id
                ]
            ]);
            return;
        }
        
        // Get video info (skip for now to test conversion directly)
        $video_info = [
            'title' => 'YouTube Video',
            'duration' => 180, // Default 3 minutes
            'thumbnail' => ''
        ];
        
        // Try to get actual video info, but don't fail if it doesn't work
        $actual_info = getVideoInfo($youtube_url);
        if ($actual_info) {
            $video_info = $actual_info;
        }
        
        // Check duration limit (10 minutes)
        if ($video_info['duration'] > 600) {
            throw new Exception('Video too long. Maximum duration is 10 minutes.');
        }
        
        // Create status file to track progress
        updateStatus($video_id, 'processing', 10, 'Starting download...', $video_info['title']);
        
        // Start conversion process (this will be faster now)
        $success = downloadAndConvert($youtube_url, $video_id);
        if (!$success) {
            updateStatus($video_id, 'error', 0, 'Conversion failed');
            throw new Exception('Conversion failed');
        }
        
        // Update status to complete with title
        updateStatus($video_id, 'complete', 100, 'Conversion complete', $video_info['title']);
        
        echo json_encode([
            'success' => true,
            'data' => [
                'video_id' => $video_id,
                'title' => $video_info['title'],
                'duration' => formatDuration($video_info['duration']),
                'download_url' => '/download.php?file=' . $video_id
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function handleDownload() {
    try {
        if (!isset($_POST['file_id'])) {
            throw new Exception('File ID is required');
        }
        
        $file_id = basename($_POST['file_id']);
        $file_path = UPLOADS_DIR . $file_id . '.mp3';
        
        if (!file_exists($file_path)) {
            throw new Exception('File not found');
        }
        
        echo json_encode([
            'success' => true,
            'download_url' => '/download.php?file=' . $file_id
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
}

function extractVideoId($url) {
    preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]+)/', $url, $matches);
    return isset($matches[1]) ? $matches[1] : null;
}

function getVideoInfo($url) {
    // Get just the title quickly first
    $title_command = "timeout 15 python3 -m yt_dlp --get-title --no-warnings " . escapeshellarg($url) . " 2>/dev/null";
    $title = shell_exec($title_command);
    
    if ($title && trim($title)) {
        // We got the title, now try to get duration
        $duration_command = "timeout 15 python3 -m yt_dlp --get-duration --no-warnings " . escapeshellarg($url) . " 2>/dev/null";
        $duration_str = shell_exec($duration_command);
        
        $duration = 0;
        if ($duration_str && trim($duration_str)) {
            // Convert duration from MM:SS to seconds
            $parts = explode(':', trim($duration_str));
            if (count($parts) == 2) {
                $duration = intval($parts[0]) * 60 + intval($parts[1]);
            } elseif (count($parts) == 3) {
                $duration = intval($parts[0]) * 3600 + intval($parts[1]) * 60 + intval($parts[2]);
            }
        }
        
        return [
            'title' => trim($title),
            'duration' => $duration,
            'thumbnail' => ''
        ];
    }
    
    // Fallback to the original method if title command fails
    $command = "timeout 20 python3 -m yt_dlp --dump-json --no-download --no-playlist " . 
               escapeshellarg($url) . " 2>&1";
    
    set_time_limit(30);
    $output = shell_exec($command);
    
    if (!$output) {
        error_log("No output from yt-dlp info command for URL: " . $url);
        return null;
    }
    
    // Try to decode JSON directly
    $info = json_decode($output, true);
    
    // If that fails, try line by line
    if (!$info) {
        $lines = explode("\n", trim($output));
        foreach ($lines as $line) {
            if (strpos($line, '{') === 0) {
                $info = json_decode($line, true);
                if ($info && isset($info['title'])) {
                    break;
                }
            }
        }
    }
    
    if (!$info || !isset($info['title'])) {
        error_log("Failed to parse video info for URL: " . $url . " Output: " . $output);
        return null;
    }
    
    return [
        'title' => $info['title'] ?? 'Unknown',
        'duration' => $info['duration'] ?? 0,
        'thumbnail' => $info['thumbnail'] ?? ''
    ];
}

function downloadAndConvert($url, $video_id) {
    $final_path = UPLOADS_DIR . $video_id . '.mp3';
    
    updateStatus($video_id, 'processing', 20, 'Downloading audio...');
    
    // Simple and reliable yt-dlp command with longer timeout
    $command = "timeout 180 python3 -m yt_dlp --extract-audio --audio-format mp3 --audio-quality 5 " .
               "--no-playlist --no-warnings --max-filesize 50M " .
               "-o " . escapeshellarg(UPLOADS_DIR . $video_id . '.%(ext)s') . " " .
               escapeshellarg($url) . " 2>&1";
    
    // Set time limit for PHP execution (3 minutes)
    set_time_limit(200);
    
    updateStatus($video_id, 'processing', 50, 'Converting to MP3...');
    
    // Execute command and capture output
    $output = shell_exec($command);
    
    // Debug: Log the output for troubleshooting
    error_log("yt-dlp command: " . $command);
    error_log("yt-dlp output: " . $output);
    
    updateStatus($video_id, 'processing', 80, 'Finalizing...');
    
    // Check if conversion was successful
    if (file_exists($final_path) && filesize($final_path) > 0) {
        return true;
    }
    
    // Check for any errors in the output
    if (strpos($output, 'ERROR') !== false) {
        $error_msg = 'Download failed';
        if (strpos($output, 'Video unavailable') !== false) {
            $error_msg = 'Video is unavailable or private';
        } elseif (strpos($output, 'Sign in to confirm') !== false) {
            $error_msg = 'Video requires sign-in';
        }
        updateStatus($video_id, 'error', 0, $error_msg);
        return false;
    }
    
    updateStatus($video_id, 'error', 0, 'Conversion failed - please try again');
    return false;
}

function updateStatus($video_id, $status, $progress, $message, $title = null) {
    $status_file = STATUS_DIR . $video_id . '.json';
    $status_data = [
        'status' => $status,
        'progress' => $progress,
        'message' => $message,
        'timestamp' => time()
    ];
    if ($title) {
        $status_data['title'] = $title;
    }
    file_put_contents($status_file, json_encode($status_data));
}

function formatDuration($seconds) {
    $minutes = floor($seconds / 60);
    $seconds = $seconds % 60;
    return sprintf('%02d:%02d', $minutes, $seconds);
}

function serveAsset($path) {
    $file = '.' . $path;
    if (file_exists($file)) {
        $ext = pathinfo($file, PATHINFO_EXTENSION);
        $content_types = [
            'css' => 'text/css',
            'js' => 'application/javascript',
            'png' => 'image/png',
            'jpg' => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'svg' => 'image/svg+xml'
        ];
        
        if (isset($content_types[$ext])) {
            header('Content-Type: ' . $content_types[$ext]);
        }
        
        readfile($file);
    } else {
        http_response_code(404);
        echo 'File not found';
    }
}
?>