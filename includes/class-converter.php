<?php
/**
 * YouTube to MP3 Converter Engine
 */

if (!defined('ABSPATH')) {
    exit;
}

class YT_MP3_Converter_Engine {
    
    private $max_duration = 600; // 10 minutes max
    private $temp_dir;
    
    public function __construct() {
        $this->temp_dir = YT_MP3_UPLOADS_DIR . 'temp/';
        if (!file_exists($this->temp_dir)) {
            wp_mkdir_p($this->temp_dir);
        }
    }
    
    public function convert($youtube_url) {
        try {
            // Extract video ID
            $video_id = $this->extract_video_id($youtube_url);
            if (!$video_id) {
                return array('success' => false, 'message' => 'Invalid YouTube URL');
            }
            
            // Check if already converted
            $existing = $this->get_existing_conversion($video_id);
            if ($existing && file_exists($existing['file_path'])) {
                return array(
                    'success' => true,
                    'data' => array(
                        'video_id' => $video_id,
                        'title' => $existing['title'],
                        'duration' => $existing['duration'],
                        'file_id' => basename($existing['file_path'], '.mp3'),
                        'download_url' => $this->get_download_url($existing['file_path'])
                    )
                );
            }
            
            // Get video info using yt-dlp
            $video_info = $this->get_video_info($youtube_url);
            if (!$video_info) {
                return array('success' => false, 'message' => 'Unable to fetch video information');
            }
            
            // Check duration limit
            if ($video_info['duration'] > $this->max_duration) {
                return array('success' => false, 'message' => 'Video too long. Maximum duration is 10 minutes.');
            }
            
            // Download and convert
            $output_file = $this->download_and_convert($youtube_url, $video_info);
            if (!$output_file) {
                return array('success' => false, 'message' => 'Conversion failed');
            }
            
            // Save to database
            $this->save_conversion($video_id, $video_info, $output_file);
            
            return array(
                'success' => true,
                'data' => array(
                    'video_id' => $video_id,
                    'title' => $video_info['title'],
                    'duration' => $this->format_duration($video_info['duration']),
                    'file_id' => basename($output_file, '.mp3'),
                    'download_url' => $this->get_download_url($output_file)
                )
            );
            
        } catch (Exception $e) {
            error_log('YT MP3 Converter Error: ' . $e->getMessage());
            return array('success' => false, 'message' => 'An error occurred during conversion');
        }
    }
    
    public function download($file_id) {
        $file_path = YT_MP3_UPLOADS_DIR . sanitize_file_name($file_id) . '.mp3';
        
        if (!file_exists($file_path)) {
            wp_die('File not found');
        }
        
        // Get original title for filename
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_mp3_conversions';
        $conversion = $wpdb->get_row($wpdb->prepare(
            "SELECT title FROM $table_name WHERE file_path = %s",
            $file_path
        ));
        
        $filename = $conversion ? sanitize_file_name($conversion->title) . '.mp3' : $file_id . '.mp3';
        
        header('Content-Type: audio/mpeg');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($file_path));
        header('Cache-Control: no-cache, must-revalidate');
        
        readfile($file_path);
        
        // Clean up file after download
        wp_schedule_single_event(time() + 3600, 'yt_mp3_cleanup_file', array($file_path));
        
        exit;
    }
    
    private function extract_video_id($url) {
        preg_match('/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/)([^"&?\/\s]{11})/', $url, $matches);
        return isset($matches[1]) ? $matches[1] : false;
    }
    
    private function get_video_info($youtube_url) {
        $yt_dlp_path = $this->get_yt_dlp_path();
        if (!$yt_dlp_path) {
            return false;
        }
        
        $command = escapeshellcmd($yt_dlp_path) . ' --dump-json --no-playlist ' . escapeshellarg($youtube_url) . ' 2>/dev/null';
        $output = shell_exec($command);
        
        if (!$output) {
            return false;
        }
        
        $info = json_decode($output, true);
        if (!$info) {
            return false;
        }
        
        return array(
            'title' => $info['title'] ?? 'Unknown Title',
            'duration' => $info['duration'] ?? 0,
            'thumbnail' => $info['thumbnail'] ?? '',
            'uploader' => $info['uploader'] ?? 'Unknown'
        );
    }
    
    private function download_and_convert($youtube_url, $video_info) {
        $yt_dlp_path = $this->get_yt_dlp_path();
        $ffmpeg_path = $this->get_ffmpeg_path();
        
        if (!$yt_dlp_path || !$ffmpeg_path) {
            return false;
        }
        
        $temp_file = $this->temp_dir . uniqid('yt_', true);
        $output_file = YT_MP3_UPLOADS_DIR . uniqid('mp3_', true) . '.mp3';
        
        // Download audio
        $command = escapeshellcmd($yt_dlp_path) . ' -f "bestaudio/best" --extract-audio --audio-format mp3 --audio-quality 192K -o ' . escapeshellarg($temp_file . '.%(ext)s') . ' ' . escapeshellarg($youtube_url) . ' 2>/dev/null';
        
        exec($command, $output, $return_var);
        
        // Find the downloaded file
        $downloaded_files = glob($temp_file . '.*');
        if (empty($downloaded_files)) {
            return false;
        }
        
        $input_file = $downloaded_files[0];
        
        // Convert to MP3 if not already
        if (pathinfo($input_file, PATHINFO_EXTENSION) !== 'mp3') {
            $convert_command = escapeshellcmd($ffmpeg_path) . ' -i ' . escapeshellarg($input_file) . ' -acodec mp3 -ab 192k ' . escapeshellarg($output_file) . ' 2>/dev/null';
            exec($convert_command, $convert_output, $convert_return);
            
            if ($convert_return !== 0) {
                unlink($input_file);
                return false;
            }
            
            unlink($input_file);
        } else {
            rename($input_file, $output_file);
        }
        
        return file_exists($output_file) ? $output_file : false;
    }
    
    private function get_yt_dlp_path() {
        // Try common paths
        $paths = array('/usr/bin/yt-dlp', '/usr/local/bin/yt-dlp', 'yt-dlp');
        
        foreach ($paths as $path) {
            if (is_executable($path) || shell_exec("which $path 2>/dev/null")) {
                return $path;
            }
        }
        
        return false;
    }
    
    private function get_ffmpeg_path() {
        // Try common paths
        $paths = array('/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', 'ffmpeg');
        
        foreach ($paths as $path) {
            if (is_executable($path) || shell_exec("which $path 2>/dev/null")) {
                return $path;
            }
        }
        
        return false;
    }
    
    private function get_existing_conversion($video_id) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_mp3_conversions';
        
        return $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM $table_name WHERE video_id = %s ORDER BY created_at DESC LIMIT 1",
            $video_id
        ), ARRAY_A);
    }
    
    private function save_conversion($video_id, $video_info, $file_path) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_mp3_conversions';
        
        $wpdb->insert(
            $table_name,
            array(
                'video_id' => $video_id,
                'title' => $video_info['title'],
                'duration' => $this->format_duration($video_info['duration']),
                'file_path' => $file_path,
                'created_at' => current_time('mysql')
            ),
            array('%s', '%s', '%s', '%s', '%s')
        );
    }
    
    private function format_duration($seconds) {
        $minutes = floor($seconds / 60);
        $seconds = $seconds % 60;
        return sprintf('%d:%02d', $minutes, $seconds);
    }
    
    private function get_download_url($file_path) {
        return admin_url('admin-ajax.php?action=yt_mp3_download&file_id=' . basename($file_path, '.mp3') . '&nonce=' . wp_create_nonce('yt_mp3_nonce'));
    }
}

// Hook for file cleanup
add_action('yt_mp3_cleanup_file', 'yt_mp3_cleanup_file_callback');
function yt_mp3_cleanup_file_callback($file_path) {
    if (file_exists($file_path)) {
        unlink($file_path);
        
        // Also remove from database
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_mp3_conversions';
        $wpdb->delete($table_name, array('file_path' => $file_path), array('%s'));
    }
}
?>
