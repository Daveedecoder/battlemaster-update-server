# BattleMaster Update Server

Complete update distribution system for BattleMaster gaming platform.

## 🚀 Quick Start

### Installation

1. Copy this folder to your localhost:
   ```
   C:\xampp\htdocs\battlemaster-update-server
   ```

2. Access admin panel:
   ```
   http://localhost/battlemaster-update-server/admin.php
   Password: admin123
   ```

3. Configure your main project to use this update server in `config/update.php`:
   ```php
   'api_url' => 'http://localhost/battlemaster-update-server/api/v1'
   ```

## 📁 Directory Structure

```
battlemaster-update-server/
├── index.php              # Main API endpoint handler
├── admin.php              # Admin control panel (UI)
├── package-update.php     # Update packaging script
├── version.json           # Current version info
├── config.json            # Server configuration
├── changelog.json         # Release notes for all versions
├── .htaccess             # Apache rewrite rules
├── updates/              # ZIP update packages (auto-created)
│   ├── 1.0.5.zip
│   ├── 1.0.4.zip
│   └── ...
└── README.md             # This file
```

## 🔧 Configuration

### version.json
Contains information about the latest version:
- `latest_version` - Current version number
- `download_url` - URL to download the update
- `release_notes` - Brief description of changes
- `migration_required` - Whether database migrations are needed
- `checksum` - MD5 hash for verification

### config.json
Server configuration:
- `global_update_enabled` - Master switch for update distribution
- `require_license` - Whether to validate license keys
- `max_download_size` - Maximum ZIP file size

## 📦 Creating Update Packages

### Method 1: Automatic Packaging Script

1. Edit `package-update.php`:
   ```php
   $sourceProjectPath = 'C:/xampp/htdocs/battlemaster';
   $version = '1.0.5';
   ```

2. Run the script:
   ```bash
   php package-update.php
   ```

3. The script will:
   - Create ZIP from your project
   - Exclude protected files (.env, vendor, storage)
   - Generate checksum
   - Update version.json
   - Save to /updates directory

### Method 2: Manual ZIP Creation

1. Create a ZIP file with these folders:
   ```
   /app
   /config
   /database/migrations
   /resources
   /routes
   /public
   /bootstrap
   artisan
   composer.json
   package.json
   ```

2. Name it: `{version}.zip` (e.g., `1.0.5.zip`)

3. Place in `/updates` directory

4. Update `version.json` manually

### Protected Paths (Never Include)
- `.env` - Environment configuration
- `/vendor/*` - Composer dependencies
- `/node_modules/*` - NPM dependencies
- `/storage/*` - User uploads and logs
- `/public/storage` - Storage symlink
- `*.log` - Log files

## 🌐 API Endpoints

### Check for Updates
```
GET /api/v1/check-update?version=1.0.0&license=XXXX

Response:
{
  "current_version": "1.0.0",
  "latest_version": "1.0.5",
  "update_available": true,
  "download_url": "http://localhost/.../download-update?...",
  "release_notes": "Bug fixes and improvements",
  "migration_required": true,
  "checksum": "a1b2c3d4...",
  "file_size": 5242880
}
```

### Download Update
```
GET /api/v1/download-update?token=SECRET_KEY&version=1.0.5

Response:
ZIP file download
```

### Get Changelog
```
GET /api/v1/changelog?version=1.0.5

Response:
{
  "version": "1.0.5",
  "changes": {
    "added": [...],
    "fixed": [...],
    "improved": [...]
  }
}
```

## 🔐 Security

### Authentication Token
Change the default token in `index.php`:
```php
define('AUTH_TOKEN', 'YOUR_SECRET_KEY_HERE');
```

Also update in buyer's `config/update.php`:
```php
'auth_token' => 'YOUR_SECRET_KEY_HERE'
```

### Admin Password
Change in `admin.php`:
```php
define('ADMIN_PASSWORD', 'your_secure_password');
```

## 🎯 Workflow for Releasing Updates

### During Development (Localhost)

1. **Disable Global Updates**
   - Go to admin panel
   - Toggle OFF "Enable Global Updates"
   - This prevents buyers from seeing test updates

2. **Create Update Package**
   ```bash
   php package-update.php
   ```

3. **Test Update on Clean Installation**
   - Install your app fresh
   - Try the update
   - Verify all features work

4. **Update Changelog**
   - Edit `changelog.json`
   - Add detailed release notes

5. **Enable Global Updates**
   - Toggle ON in admin panel
   - Now buyers can see and download

### For Production Deployment

1. **Push to GitHub**
   ```bash
   git init
   git add .
   git commit -m "Update server v1.0.5"
   git push origin main
   ```

2. **Update Download URLs**
   - In `version.json`, change:
   ```json
   "download_url": "https://your-domain.com/api/v1/download-update?..."
   ```

3. **Deploy to Server**
   - Upload to web hosting
   - Configure Apache/Nginx
   - Update DNS records

## 🤖 Copilot Automation Guide

### Task 1: Create New Update Package
```
Copilot, please:
1. Update $version to '1.0.6' in package-update.php
2. Run the packaging script
3. Update changelog.json with these changes:
   - Fixed wallet sync issues
   - Added new tournament features
4. Update version.json release notes
```

### Task 2: Push Update to GitHub
```
Copilot, please:
1. Commit all changes in update server
2. Push to GitHub repository
3. Update version.json download_url to production URL
```

### Task 3: Toggle Update Distribution
```
Copilot, please:
1. Open config.json
2. Set "global_update_enabled" to true/false
```

## 📊 Admin Panel Features

Access: `http://localhost/battlemaster-update-server/admin.php`

### Features:
- ✅ View current version statistics
- ✅ Toggle global update distribution
- ✅ Update version information
- ✅ View available update packages
- ✅ Monitor package sizes and dates
- ✅ View server configuration

### Dashboard Stats:
- Latest version number
- Total available updates
- Distribution status (ON/OFF)

## 🔄 Update Flow (Buyer Side)

1. Buyer logs into admin panel
2. Clicks "System Updates"
3. Clicks "Check for Updates"
4. System calls: `/api/v1/check-update`
5. If update available, shows details
6. Buyer clicks "Update Now"
7. System:
   - Downloads ZIP
   - Creates backup
   - Extracts files
   - Runs migrations
   - Clears cache
8. Update complete!

## 🐛 Troubleshooting

### Update Not Showing
- Check if `global_update_enabled` is true in config.json
- Verify version numbers are correct
- Check API endpoint is accessible

### Download Fails
- Verify ZIP file exists in /updates directory
- Check file permissions (755 for directories, 644 for files)
- Verify auth token matches

### ZIP Creation Fails
- Check source project path in package-update.php
- Verify PHP ZipArchive extension is enabled
- Check disk space

## 📝 Version Numbering

Use Semantic Versioning:
- **Major**: Breaking changes (2.0.0)
- **Minor**: New features, backward compatible (1.1.0)
- **Patch**: Bug fixes (1.0.1)

## 🚀 Production Deployment

### Option 1: GitHub Pages + Releases
1. Create GitHub repository
2. Push update server code
3. Use GitHub Releases for ZIP files
4. Update `download_url` to GitHub release URL

### Option 2: Dedicated Server
1. Upload to web hosting (cPanel, VPS, etc.)
2. Configure Apache/Nginx
3. Set up SSL certificate
4. Update API URLs in buyer config

### Option 3: CDN Distribution
1. Upload ZIPs to CDN (Cloudflare, DigitalOcean Spaces)
2. Use CDN URLs for downloads
3. Keep API on separate server

## 📞 Support

For issues or questions:
- GitHub: Create an issue
- Email: support@yourdomain.com

---

**Version**: 1.0.0  
**Last Updated**: December 9, 2025  
**Author**: DaveeDeCoder
