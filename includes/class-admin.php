<?php
/**
 * Admin functionality for YouTube MP3 Converter
 */

if (!defined('ABSPATH')) {
    exit;
}

class YT_MP3_Admin {
    
    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('admin_init', array($this, 'admin_init'));
    }
    
    public function add_admin_menu() {
        add_options_page(
            'YouTube MP3 Converter Settings',
            'YT MP3 Converter',
            'manage_options',
            'yt-mp3-converter',
            array($this, 'admin_page')
        );
    }
    
    public function admin_init() {
        register_setting('yt_mp3_settings', 'yt_mp3_options');
        
        add_settings_section(
            'yt_mp3_general',
            'General Settings',
            array($this, 'general_section_callback'),
            'yt-mp3-converter'
        );
        
        add_settings_field(
            'max_duration',
            'Maximum Duration (seconds)',
            array($this, 'max_duration_callback'),
            'yt-mp3-converter',
            'yt_mp3_general'
        );
        
        add_settings_field(
            'cleanup_time',
            'File Cleanup Time (hours)',
            array($this, 'cleanup_time_callback'),
            'yt-mp3-converter',
            'yt_mp3_general'
        );
    }
    
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>YouTube MP3 Converter Settings</h1>
            
            <div class="notice notice-info">
                <p><strong>Requirements:</strong> This plugin requires yt-dlp and FFmpeg to be installed on your server.</p>
                <p><strong>Shortcode:</strong> Use <code>[youtube_mp3_converter]</code> to display the converter form on any page or post.</p>
            </div>
            
            <form method="post" action="options.php">
                <?php
                settings_fields('yt_mp3_settings');
                do_settings_sections('yt-mp3-converter');
                submit_button();
                ?>
            </form>
            
            <h2>System Status</h2>
            <table class="form-table">
                <tr>
                    <th scope="row">yt-dlp Status</th>
                    <td><?php echo $this->check_yt_dlp() ? '<span style="color: green;">✓ Available</span>' : '<span style="color: red;">✗ Not Found</span>'; ?></td>
                </tr>
                <tr>
                    <th scope="row">FFmpeg Status</th>
                    <td><?php echo $this->check_ffmpeg() ? '<span style="color: green;">✓ Available</span>' : '<span style="color: red;">✗ Not Found</span>'; ?></td>
                </tr>
                <tr>
                    <th scope="row">Upload Directory</th>
                    <td><?php echo is_writable(YT_MP3_UPLOADS_DIR) ? '<span style="color: green;">✓ Writable</span>' : '<span style="color: red;">✗ Not Writable</span>'; ?></td>
                </tr>
            </table>
            
            <h2>Recent Conversions</h2>
            <?php $this->display_recent_conversions(); ?>
        </div>
        <?php
    }
    
    public function general_section_callback() {
        echo '<p>Configure the general settings for the YouTube MP3 converter.</p>';
    }
    
    public function max_duration_callback() {
        $options = get_option('yt_mp3_options');
        $value = isset($options['max_duration']) ? $options['max_duration'] : 600;
        echo '<input type="number" name="yt_mp3_options[max_duration]" value="' . esc_attr($value) . '" min="60" max="3600" />';
        echo '<p class="description">Maximum video duration allowed for conversion (60-3600 seconds)</p>';
    }
    
    public function cleanup_time_callback() {
        $options = get_option('yt_mp3_options');
        $value = isset($options['cleanup_time']) ? $options['cleanup_time'] : 24;
        echo '<input type="number" name="yt_mp3_options[cleanup_time]" value="' . esc_attr($value) . '" min="1" max="168" />';
        echo '<p class="description">How long to keep converted files before automatic cleanup (1-168 hours)</p>';
    }
    
    private function check_yt_dlp() {
        $paths = array('/usr/bin/yt-dlp', '/usr/local/bin/yt-dlp', 'yt-dlp');
        foreach ($paths as $path) {
            if (is_executable($path) || shell_exec("which $path 2>/dev/null")) {
                return true;
            }
        }
        return false;
    }
    
    private function check_ffmpeg() {
        $paths = array('/usr/bin/ffmpeg', '/usr/local/bin/ffmpeg', 'ffmpeg');
        foreach ($paths as $path) {
            if (is_executable($path) || shell_exec("which $path 2>/dev/null")) {
                return true;
            }
        }
        return false;
    }
    
    private function display_recent_conversions() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'yt_mp3_conversions';
        
        $conversions = $wpdb->get_results(
            "SELECT * FROM $table_name ORDER BY created_at DESC LIMIT 10"
        );
        
        if (empty($conversions)) {
            echo '<p>No conversions yet.</p>';
            return;
        }
        
        echo '<table class="wp-list-table widefat fixed striped">';
        echo '<thead><tr><th>Title</th><th>Duration</th><th>Date</th><th>File Size</th><th>Actions</th></tr></thead>';
        echo '<tbody>';
        
        foreach ($conversions as $conversion) {
            $file_size = file_exists($conversion->file_path) ? size_format(filesize($conversion->file_path)) : 'File not found';
            echo '<tr>';
            echo '<td>' . esc_html($conversion->title) . '</td>';
            echo '<td>' . esc_html($conversion->duration) . '</td>';
            echo '<td>' . esc_html($conversion->created_at) . '</td>';
            echo '<td>' . esc_html($file_size) . '</td>';
            echo '<td>';
            if (file_exists($conversion->file_path)) {
                echo '<a href="#" onclick="deleteConversion(' . $conversion->id . ')" class="button button-secondary">Delete</a>';
            } else {
                echo '<span style="color: #ccc;">File missing</span>';
            }
            echo '</td>';
            echo '</tr>';
        }
        
        echo '</tbody>';
        echo '</table>';
        
        ?>
        <script>
        function deleteConversion(id) {
            if (confirm('Are you sure you want to delete this conversion?')) {
                // AJAX call to delete conversion
                jQuery.post(ajaxurl, {
                    action: 'yt_mp3_delete_conversion',
                    id: id,
                    nonce: '<?php echo wp_create_nonce('yt_mp3_admin'); ?>'
                }, function(response) {
                    if (response.success) {
                        location.reload();
                    } else {
                        alert('Error deleting conversion');
                    }
                });
            }
        }
        </script>
        <?php
    }
}

// AJAX handler for deleting conversions
add_action('wp_ajax_yt_mp3_delete_conversion', 'yt_mp3_delete_conversion');
function yt_mp3_delete_conversion() {
    check_ajax_referer('yt_mp3_admin', 'nonce');
    
    if (!current_user_can('manage_options')) {
        wp_send_json_error('Insufficient permissions');
    }
    
    $id = intval($_POST['id']);
    
    global $wpdb;
    $table_name = $wpdb->prefix . 'yt_mp3_conversions';
    
    $conversion = $wpdb->get_row($wpdb->prepare(
        "SELECT file_path FROM $table_name WHERE id = %d",
        $id
    ));
    
    if ($conversion && file_exists($conversion->file_path)) {
        unlink($conversion->file_path);
    }
    
    $result = $wpdb->delete($table_name, array('id' => $id), array('%d'));
    
    if ($result) {
        wp_send_json_success();
    } else {
        wp_send_json_error('Database error');
    }
}
?>
