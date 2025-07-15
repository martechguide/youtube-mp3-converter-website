<?php
/**
 * Plugin Name: YouTube to MP3 Converter
 * Plugin URI: https://example.com/youtube-mp3-converter
 * Description: A powerful YouTube to MP3 converter tool with download functionality
 * Version: 1.0.0
 * Author: Your Name
 * License: GPL v2 or later
 * Text Domain: youtube-mp3-converter
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('YT_MP3_PLUGIN_URL', plugin_dir_url(__FILE__));
define('YT_MP3_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('YT_MP3_UPLOADS_DIR', wp_upload_dir()['basedir'] . '/youtube-mp3/');
define('YT_MP3_UPLOADS_URL', wp_upload_dir()['baseurl'] . '/youtube-mp3/');

// Include required files
require_once YT_MP3_PLUGIN_PATH . 'includes/class-converter.php';
require_once YT_MP3_PLUGIN_PATH . 'includes/class-admin.php';

class YouTube_MP3_Converter {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('youtube_mp3_converter', array($this, 'shortcode_handler'));
        add_action('wp_ajax_yt_mp3_convert', array($this, 'ajax_convert'));
        add_action('wp_ajax_nopriv_yt_mp3_convert', array($this, 'ajax_convert'));
        add_action('wp_ajax_yt_mp3_download', array($this, 'ajax_download'));
        add_action('wp_ajax_nopriv_yt_mp3_download', array($this, 'ajax_download'));
        
        // Create upload directory
        $this->create_upload_directory();
        
        // Initialize admin
        new YT_MP3_Admin();
    }
    
    public function init() {
        // Plugin initialization
        load_plugin_textdomain('youtube-mp3-converter', false, dirname(plugin_basename(__FILE__)) . '/languages/');
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
        wp_enqueue_style('feather-icons', 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.css');
        wp_enqueue_style('yt-mp3-style', YT_MP3_PLUGIN_URL . 'assets/css/style.css', array(), '1.0.0');
        
        wp_enqueue_script('bootstrap', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array(), '5.3.0', true);
        wp_enqueue_script('feather-icons', 'https://cdn.jsdelivr.net/npm/feather-icons@4.29.0/dist/feather.min.js', array(), '4.29.0', true);
        wp_enqueue_script('yt-mp3-converter', YT_MP3_PLUGIN_URL . 'assets/js/converter.js', array('jquery'), '1.0.0', true);
        
        // Localize script for AJAX
        wp_localize_script('yt-mp3-converter', 'yt_mp3_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('yt_mp3_nonce'),
            'messages' => array(
                'processing' => __('Processing...', 'youtube-mp3-converter'),
                'converting' => __('Converting to MP3...', 'youtube-mp3-converter'),
                'download_ready' => __('Download Ready!', 'youtube-mp3-converter'),
                'error' => __('An error occurred. Please try again.', 'youtube-mp3-converter'),
                'invalid_url' => __('Please enter a valid YouTube URL.', 'youtube-mp3-converter')
            )
        ));
    }
    
    public function shortcode_handler($atts) {
        $atts = shortcode_atts(array(
            'theme' => 'default'
        ), $atts);
        
        ob_start();
        include YT_MP3_PLUGIN_PATH . 'templates/converter-form.php';
        return ob_get_clean();
    }
    
    public function ajax_convert() {
        check_ajax_referer('yt_mp3_nonce', 'nonce');
        
        $youtube_url = sanitize_url($_POST['youtube_url']);
        
        if (!$this->is_valid_youtube_url($youtube_url)) {
            wp_send_json_error('Invalid YouTube URL');
        }
        
        $converter = new YT_MP3_Converter_Engine();
        $result = $converter->convert($youtube_url);
        
        if ($result['success']) {
            wp_send_json_success($result['data']);
        } else {
            wp_send_json_error($result['message']);
        }
    }
    
    public function ajax_download() {
        check_ajax_referer('yt_mp3_nonce', 'nonce');
        
        $file_id = sanitize_text_field($_POST['file_id']);
        $converter = new YT_MP3_Converter_Engine();
        $converter->download($file_id);
    }
    
    private function is_valid_youtube_url($url) {
        return preg_match('/^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/(watch\?v=|embed\/|v\/|.+\?v=)?([^&=%\?]{11})/', $url);
    }
    
    private function create_upload_directory() {
        if (!file_exists(YT_MP3_UPLOADS_DIR)) {
            wp_mkdir_p(YT_MP3_UPLOADS_DIR);
            
            // Create .htaccess for security
            $htaccess_content = "Options -Indexes\n";
            $htaccess_content .= "deny from all\n";
            file_put_contents(YT_MP3_UPLOADS_DIR . '.htaccess', $htaccess_content);
        }
    }
}

// Initialize the plugin
new YouTube_MP3_Converter();

// Activation hook
register_activation_hook(__FILE__, 'yt_mp3_activate');
function yt_mp3_activate() {
    // Create database table if needed
    global $wpdb;
    
    $table_name = $wpdb->prefix . 'yt_mp3_conversions';
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        video_id varchar(20) NOT NULL,
        title text NOT NULL,
        duration varchar(10) DEFAULT '',
        file_path varchar(255) NOT NULL,
        created_at datetime DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}

// Deactivation hook
register_deactivation_hook(__FILE__, 'yt_mp3_deactivate');
function yt_mp3_deactivate() {
    // Clean up temporary files
    $upload_dir = YT_MP3_UPLOADS_DIR;
    if (is_dir($upload_dir)) {
        $files = glob($upload_dir . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }
    }
}
?>
