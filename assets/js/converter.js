/**
 * YouTube MP3 Converter JavaScript
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize Feather icons
    if (typeof feather !== 'undefined') {
        feather.replace();
    }
    
    const form = document.getElementById('yt-mp3-form');
    const urlInput = document.getElementById('youtube-url');
    const convertBtn = document.getElementById('convert-btn');
    const progressBar = document.querySelector('.progress');
    const progressBarFill = document.querySelector('.progress-bar');
    const statusMessage = document.getElementById('status-message');
    const videoInfo = document.getElementById('video-info');
    const downloadSection = document.getElementById('download-section');
    
    if (!form) return; // Exit if form not found
    
    form.addEventListener('submit', function(e) {
        e.preventDefault();
        convertVideo();
    });
    
    urlInput.addEventListener('input', function() {
        const url = this.value.trim();
        if (url && isValidYouTubeUrl(url)) {
            convertBtn.disabled = false;
        } else {
            convertBtn.disabled = true;
        }
    });
    
    function convertVideo() {
        const youtubeUrl = urlInput.value.trim();
        
        if (!isValidYouTubeUrl(youtubeUrl)) {
            showStatus('error', 'Please enter a valid YouTube URL.');
            return;
        }
        
        // Reset UI
        hideElements([videoInfo, downloadSection]);
        showStatus('info', 'Processing...');
        setProgress(0);
        showProgress();
        
        // Disable form
        convertBtn.disabled = true;
        convertBtn.innerHTML = '<span class="loading-spinner"></span> Converting...';
        
        // Simulate progress
        simulateProgress();
        
        // Make AJAX request using fetch
        const formData = new FormData();
        formData.append('action', 'convert');
        formData.append('youtube_url', youtubeUrl);
        
        fetch('/', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                setProgress(100);
                setTimeout(() => {
                    hideProgress();
                    showVideoInfo(data.data);
                    showDownloadSection(data.data);
                    showStatus('success', 'Download Ready!');
                }, 500);
            } else {
                hideProgress();
                showStatus('error', data.message || 'An error occurred. Please try again.');
            }
        })
        .catch(error => {
            hideProgress();
            showStatus('error', 'An error occurred. Please try again.');
            console.error('Error:', error);
        })
        .finally(() => {
            // Re-enable form
            convertBtn.disabled = false;
            convertBtn.innerHTML = '<i data-feather="download"></i> Convert to MP3';
            feather.replace();
        });
    }
    
    function isValidYouTubeUrl(url) {
        const regex = /^(https?:\/\/)?(www\.)?(youtube\.com|youtu\.be)\/(watch\?v=|embed\/|v\/|.+\?v=)?([^&=%\?]{11})/;
        return regex.test(url);
    }
    
    function showStatus(type, message) {
        statusMessage.className = `status-message ${type}`;
        statusMessage.textContent = message;
        statusMessage.style.display = 'block';
    }
    
    function showProgress() {
        progressBar.classList.add('show');
    }
    
    function hideProgress() {
        progressBar.classList.remove('show');
    }
    
    function setProgress(percent) {
        progressBarFill.style.width = percent + '%';
    }
    
    function simulateProgress() {
        let progress = 0;
        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress >= 90) {
                progress = 90;
                clearInterval(interval);
            }
            setProgress(progress);
        }, 500);
    }
    
    function showVideoInfo(data) {
        const thumbnail = videoInfo.querySelector('.video-thumbnail');
        const title = videoInfo.querySelector('.video-title');
        const duration = videoInfo.querySelector('.video-duration');
        
        if (data.thumbnail) {
            thumbnail.src = data.thumbnail;
            thumbnail.style.display = 'block';
        } else {
            thumbnail.style.display = 'none';
        }
        
        title.textContent = data.title;
        duration.textContent = `Duration: ${data.duration}`;
        
        videoInfo.classList.add('show');
    }
    
    function showDownloadSection(data) {
        const downloadBtn = downloadSection.querySelector('.download-btn');
        const filenameText = downloadSection.querySelector('.filename-text');
        
        downloadBtn.href = data.download_url;
        downloadBtn.download = data.title + '.mp3';
        
        // Update filename display with better visibility
        if (filenameText) {
            filenameText.textContent = `File: ${data.title}.mp3`;
            filenameText.style.color = 'white';
            filenameText.style.fontWeight = '700';
            filenameText.style.textShadow = '2px 2px 4px rgba(0,0,0,0.7)';
            filenameText.style.fontSize = '1.1rem';
        }
        
        // Ensure download button is visible
        downloadBtn.style.visibility = 'visible';
        downloadBtn.style.opacity = '1';
        
        downloadSection.classList.add('show');
    }
    
    function hideElements(elements) {
        elements.forEach(element => {
            element.classList.remove('show');
        });
    }
    
    // Add click tracking for download button
    document.addEventListener('click', function(e) {
        if (e.target.classList.contains('download-btn')) {
            // Track download
            if (typeof gtag !== 'undefined') {
                gtag('event', 'download', {
                    'event_category': 'YouTube MP3 Converter',
                    'event_label': 'MP3 Download'
                });
            }
        }
    });
    
    // Add keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to convert
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            if (urlInput.value.trim() && !convertBtn.disabled) {
                convertVideo();
            }
        }
        
        // Escape to clear form
        if (e.key === 'Escape') {
            clearForm();
        }
    });
    
    function clearForm() {
        urlInput.value = '';
        convertBtn.disabled = true;
        hideElements([videoInfo, downloadSection, statusMessage]);
        hideProgress();
    }
    
    // Add paste detection for URLs
    urlInput.addEventListener('paste', function(e) {
        setTimeout(() => {
            const pastedText = this.value.trim();
            if (isValidYouTubeUrl(pastedText)) {
                this.classList.add('is-valid');
                convertBtn.disabled = false;
            } else {
                this.classList.add('is-invalid');
                convertBtn.disabled = true;
            }
        }, 10);
    });
    
    // Remove validation classes on input
    urlInput.addEventListener('input', function() {
        this.classList.remove('is-valid', 'is-invalid');
    });
});

// Utility functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
}

function copyToClipboard(text) {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(text);
    } else {
        // Fallback for older browsers
        const textArea = document.createElement('textarea');
        textArea.value = text;
        document.body.appendChild(textArea);
        textArea.select();
        document.execCommand('copy');
        document.body.removeChild(textArea);
    }
}

// Service Worker registration for offline support
if ('serviceWorker' in navigator) {
    window.addEventListener('load', function() {
        navigator.serviceWorker.register('/sw.js').then(function(registration) {
            console.log('ServiceWorker registration successful');
        }).catch(function(err) {
            console.log('ServiceWorker registration failed');
        });
    });
}
