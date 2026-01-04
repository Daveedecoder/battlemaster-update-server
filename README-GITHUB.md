# BattleMaster Update Server 🚀

[![GitHub](https://img.shields.io/badge/GitHub-Hosted-blue)](https://github.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-purple)](https://php.net)
[![License](https://img.shields.io/badge/License-Proprietary-red)](LICENSE)

> **GitHub-hosted update distribution system for BattleMaster gaming platform**

This server manages and distributes software updates to your BattleMaster application, similar to WordPress or Laravel Forge update systems. **No server hosting required** - everything runs from GitHub!

---

## 🌟 Features

- ✅ **GitHub-Hosted Updates** - Serve updates from GitHub (no server required)
- ✅ **Sequential Update System** - Enforces ordered version progression
- ✅ **Checksum Verification** - Ensures file integrity
- ✅ **Migration Support** - Database migrations during updates
- ✅ **Automatic Backups** - Rollback capability
- ✅ **Multi-version Support** - Complete version history
- ✅ **RESTful API** - Simple JSON endpoints
- ✅ **Secure Downloads** - Token-based authentication

---

## 📋 Requirements

- PHP 8.2 or higher
- Git (for version control)
- GitHub account (for hosting updates)

---

## 🚀 Quick Start

### 1. Clone or Download

```bash
git clone https://github.com/YOUR-USERNAME/battlemaster-update-server.git
cd battlemaster-update-server
```

### 2. Configure Environment

```bash
# Copy example environment file
cp .env.example .env

# Edit .env with your details
notepad .env  # Windows
nano .env     # Linux/Mac
```

Update these values in `.env`:
```env
GITHUB_USERNAME=your-github-username
GITHUB_REPO=battlemaster-update-server
AUTH_TOKEN=your_secret_token_here_2026
```

### 3. Test Locally

Access the API:
```
http://localhost/battlemaster-update-server/version.json
```

---

## 📡 API Endpoints

### Check for Updates
```
GET /version.json
```

Returns latest version information and metadata.

**Response Example:**
```json
{
  "latest_version": "2.6.3",
  "min_supported_version": "2.0.0",
  "versions": [...]
}
```

### Get Version Metadata
```
GET /updates/2.6.3.json
```

Returns specific version information including changelog, requirements, and download URL.

### Download Update (Token Required)
```
GET /updates/2.6.3.zip
```

Downloads the update package (requires authentication token in production).

---

## 📦 Version Structure

Each version includes:

```json
{
  "version": "2.6.3",
  "release_date": "2026-01-03",
  "release_notes": "Critical bug fixes",
  "download_url": "https://raw.githubusercontent.com/.../2.6.3.zip",
  "checksum": "91e17a7c89d9895b8fe1c40bdc29589a",
  "file_size": 5400,
  "migration_required": false,
  "critical_update": true,
  "changelog": [
    "Fixed authentication bug",
    "Improved performance"
  ],
  "php_requirement": "^8.2",
  "laravel_requirement": "^11.0"
}
```

---

## 🔒 Security

### Protected Files (Not in Repository)

These files are **automatically excluded** from Git:

- `admin.php` - Admin control panel (private)
- `config.json` - Server configuration (private)
- `.env` - Environment variables (private)
- Debug and test scripts (private)

### Public Files (Safe to Share)

- `version.json` - Public version metadata
- `updates/*.json` - Public version details
- `index-github.php` - Public API (no secrets)

### Authentication

Downloads require a secret token that matches between:
1. Your `.env` file: `AUTH_TOKEN=your_secret_here`
2. Client app config: `config/update.php`

---

## 📝 Creating New Updates

### Step 1: Prepare Update Files

Create your update ZIP package with the necessary files.

### Step 2: Update Metadata

Add version information to `version.json`:

```json
{
  "version": "2.6.4",
  "release_date": "2026-01-04",
  "download_url": "https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/2.6.4.zip",
  "checksum": "your-md5-checksum-here",
  ...
}
```

### Step 3: Create Version Metadata File

Create `updates/2.6.4.json` with detailed information.

### Step 4: Commit and Push

```bash
git add version.json updates/2.6.4.json
git commit -m "Release v2.6.4"
git push origin main
```

For large ZIP files, use **GitHub Releases** instead of committing directly.

---

## 🌐 GitHub Integration

### Using GitHub for Update Hosting

Your updates are served from GitHub's CDN using raw URLs:

```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/VERSION.zip
```

**Benefits:**
- ✅ Free hosting
- ✅ Global CDN
- ✅ Version control
- ✅ Works for both local dev and production

### For Large Files (>100MB)

Use **GitHub Releases**:

1. Create a new release on GitHub
2. Upload ZIP as release asset
3. Use release URL in `version.json`:

```
https://github.com/YOUR-USERNAME/battlemaster-update-server/releases/download/v2.6.4/2.6.4.zip
```

---

## 📖 Documentation

- **[GITHUB_SETUP.md](GITHUB_SETUP.md)** - Complete GitHub deployment guide
- **[HOW_TO_CREATE_UPDATES.md](HOW_TO_CREATE_UPDATES.md)** - Step-by-step update creation
- **[README.md](README.md)** - Original project documentation

---

## 🔧 Configuration

### Client App Setup

In your BattleMaster application, update `config/update.php`:

```php
return [
    'enabled' => true,
    'api_url' => 'https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main',
    'auth_token' => env('UPDATE_AUTH_TOKEN', 'your_secret_token'),
    'check_endpoint' => '/version.json',
    'download_endpoint' => '/updates/{version}.zip',
];
```

---

## 🛠️ Helper Scripts

### Update GitHub URLs

Automatically convert localhost URLs to GitHub URLs:

```powershell
.\update-github-urls.ps1 -GitHubUsername "your-username"
```

---

## 📂 Directory Structure

```
battlemaster-update-server/
├── .env.example              # Environment template
├── .gitignore                # Git exclusions (protects sensitive files)
├── env.php                   # Environment loader
├── index-github.php          # Public API (GitHub-ready)
├── version.json              # Main version registry
├── GITHUB_SETUP.md           # Setup guide
├── README-GITHUB.md          # This file
└── updates/                  # Update packages
    ├── 2.6.3.json            # Version metadata
    ├── 2.6.3.zip             # Update package
    └── ...
```

---

## 🤝 Contributing

This is a private update server for BattleMaster. For issues or suggestions, contact the repository owner.

---

## 📄 License

Proprietary - For BattleMaster platform only.

---

## 📞 Support

For questions or issues:
- Create an issue on GitHub
- Contact: your-email@example.com

---

## ✨ Version History

See [version.json](version.json) for complete version history.

**Latest Version:** 2.6.3 (January 3, 2026)

---

**Made with ❤️ for BattleMaster**
