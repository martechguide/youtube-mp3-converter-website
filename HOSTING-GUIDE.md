# YouTube MP3 Converter - Hosting Guide

## Complete Setup Instructions

### Requirements
- PHP 8.1 or higher
- Python 3.8+ with pip
- FFmpeg
- Web server (Apache/Nginx) or shared hosting with PHP support

### Installation Steps

#### 1. Upload Files
Upload all files from this package to your web server's public directory (usually `public_html` or `www`).

#### 2. Install Python Dependencies
Run this command on your server (via SSH or hosting control panel):
```bash
pip3 install yt-dlp
```

#### 3. Install FFmpeg
**On Ubuntu/Debian:**
```bash
sudo apt update
sudo apt install ffmpeg
```

**On CPanel/Shared Hosting:**
Most shared hosting providers have FFmpeg pre-installed. Contact your hosting provider if needed.

#### 4. Set Permissions
Set the following folder permissions:
```bash
chmod 755 uploads/
chmod 755 status/
chmod 755 temp/
```

#### 5. Test Installation
1. Visit your website URL
2. Try converting a short YouTube video
3. Check if download works with proper filename

### Advertisement System Configuration

The website includes a comprehensive ad placement system with multiple fake download buttons and banner ads. All ads redirect to your specified URL.

#### Current Ad Placements:
1. **Top Banner** - Animated gradient banner at the top of the page
2. **Fake Download Section** - Two prominent fake download buttons
3. **Side Advertisement Panel** - Sponsored content area
4. **Alternative Download Link** - In the actual download section
5. **Bottom Banner** - Premium upgrade messaging
6. **Floating Popup** - Appears every 30 seconds
7. **Additional Fake Buttons** - Premium trial and bulk download

#### To Change Advertisement URL:
Edit these files and replace all instances of:
`https://disconnectedlasting.com/vmftwq0z?key=d80bc596486af3e6c8e5e457a9967eb2`

**Files to edit:**
- `index.php` (lines with ad banners and fake buttons)
- `assets/js/converter.js` (if any ad click handlers)

#### To Add More Ads:
1. **Banner Ads**: Add more `<div class="ad-banner-large">` elements in `index.php`
2. **Fake Buttons**: Add more `<a class="fake-download-btn">` elements
3. **Popup Ads**: Modify the JavaScript in `index.php` to show more popups

### Customization Options

#### Change Colors/Styling:
Edit `assets/css/style.css`:
- Line 188-212: Banner ad gradients and animations
- Line 277-298: Download button styling
- Line 301-322: Download section background

#### Modify Advertisement Timing:
In `index.php`, find the JavaScript section (lines 274-281):
- Change `10000` to modify initial popup delay (in milliseconds)
- Change `30000` to modify popup interval
- Change `5000` to modify popup display duration

#### Add Custom Analytics:
Add Google Analytics or other tracking codes in the `<head>` section of `index.php`.

### File Structure
```
website-root/
├── index.php (main application file)
├── download.php (file download handler)
├── status.php (conversion status endpoint)
├── assets/
│   ├── css/style.css (all styling)
│   └── js/converter.js (frontend functionality)
├── includes/
│   ├── class-admin.php (admin functions)
│   └── class-converter.php (conversion logic)
├── templates/
│   └── converter-form.php (form template)
├── uploads/ (converted MP3 files - needs write permission)
├── status/ (conversion status files - needs write permission)
└── temp/ (temporary files - needs write permission)
```

### Troubleshooting

#### Common Issues:

**1. Conversion Fails:**
- Check if Python 3 and yt-dlp are installed
- Verify FFmpeg is available
- Check folder permissions (uploads/, status/, temp/)

**2. Download Shows Wrong Filename:**
- Ensure the video title is being retrieved properly
- Check PHP error logs for yt-dlp command issues

**3. Ads Not Showing:**
- Verify CSS files are loading properly
- Check browser console for JavaScript errors
- Ensure all ad URLs are correct

**4. Slow Conversion:**
- Consider optimizing yt-dlp commands in `index.php`
- Check server resources (CPU/RAM)
- Implement caching if needed

### Security Considerations

1. **File Cleanup**: The system automatically deletes files after 24 hours
2. **Input Validation**: URLs are validated before processing
3. **File Size Limits**: Maximum 50MB file size limit is enforced
4. **Duration Limits**: 10-minute video duration limit

### Support

For technical issues:
1. Check PHP error logs
2. Verify all dependencies are installed
3. Test with a simple YouTube video first
4. Contact your hosting provider for server-specific issues

### Performance Optimization

1. **Caching**: Consider implementing Redis or Memcached
2. **CDN**: Use a CDN for static assets (CSS/JS)
3. **Compression**: Enable gzip compression on your server
4. **Monitoring**: Set up monitoring for conversion failures

---

**Note**: This converter is designed for personal use and educational purposes. Ensure compliance with YouTube's Terms of Service and applicable copyright laws in your jurisdiction.