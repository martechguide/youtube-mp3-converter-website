# YouTube MP3 Converter

A standalone PHP-based YouTube to MP3 converter with integrated advertisement system.

## Features

✅ **Fast Conversion**: High-speed YouTube to MP3 conversion using yt-dlp
✅ **Proper Filenames**: Downloads use actual video titles instead of video IDs
✅ **Advertisement System**: Multiple ad placements with animated banners and fake download buttons
✅ **Responsive Design**: Works perfectly on desktop and mobile devices
✅ **Auto Cleanup**: Files automatically deleted after 24 hours for privacy
✅ **High Quality**: 192kbps MP3 output quality

## Quick Start

1. Upload all files to your web server
2. Install Python 3 and yt-dlp: `pip3 install yt-dlp`
3. Install FFmpeg on your server
4. Set folder permissions: `chmod 755 uploads/ status/ temp/`
5. Visit your website and start converting!

## Advertisement Configuration

The website includes 7 strategic ad placements:
- Animated top banner
- Fake download buttons
- Side advertisement panel
- Alternative download links
- Bottom premium banner
- Floating popup ads
- Premium trial buttons

All ads redirect to: `https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2`

To change the advertisement URL, edit `index.php` and replace all instances of the above URL.

## File Structure

- `index.php` - Main application
- `download.php` - File download handler
- `assets/` - CSS and JavaScript files
- `includes/` - PHP classes and functions
- `templates/` - HTML templates
- `uploads/` - Converted MP3 files (requires write permission)
- `status/` - Conversion status files (requires write permission)
- `temp/` - Temporary files (requires write permission)

## Requirements

- PHP 8.1+
- Python 3.8+
- yt-dlp
- FFmpeg
- Web server with PHP support

## Support

See `HOSTING-GUIDE.md` for detailed setup instructions and troubleshooting.

---

**Created for educational and personal use. Ensure compliance with YouTube's Terms of Service.**