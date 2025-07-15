<?php
/**
 * YouTube MP3 Converter Form Template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="yt-mp3-converter">
    <h2><i data-feather="music"></i> YouTube to MP3 Converter</h2>
    
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
    
    <!-- Status Message -->
    <div id="status-message" class="status-message" style="display: none;"></div>
    
    <!-- Progress Bar -->
    <div class="progress">
        <div class="progress-bar" role="progressbar" style="width: 0%"></div>
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
        <h4 style="color: white !important; font-weight: 700 !important; text-shadow: 2px 2px 4px rgba(0,0,0,0.7) !important;">Your MP3 is Ready!</h4>
        <p class="mb-4 filename-text" style="color: white !important; font-weight: 600 !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.5) !important; font-size: 1.1rem !important;">Click the button below to download your converted MP3 file.</p>
        <a href="#" class="download-btn" target="_blank" style="background: linear-gradient(135deg, #dc3545, #c82333) !important; color: white !important; padding: 15px 40px !important; border-radius: 30px !important; text-decoration: none !important; display: inline-block !important; font-weight: 700 !important; font-size: 1.2rem !important; border: 3px solid #fff !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.5) !important; visibility: visible !important; opacity: 1 !important;">
            <i data-feather="download"></i>
            Download MP3
        </a>
        <div class="mt-3">
            <small style="color: white !important; font-weight: 600 !important; text-shadow: 1px 1px 2px rgba(0,0,0,0.5) !important;">File will be automatically deleted after 24 hours for privacy.</small>
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
</div>

<script>
// Initialize Feather icons when template is loaded
document.addEventListener('DOMContentLoaded', function() {
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
});
</script>
