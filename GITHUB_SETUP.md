# GitHub Setup Guide for BattleMaster Update Server

This guide will help you deploy your update server to GitHub so both local development and production can use GitHub-hosted updates.

---

## 🎯 Overview

Instead of serving updates from your local machine:
- **Update files** (ZIPs, JSONs) are hosted on GitHub
- **Both local dev and production** fetch updates from GitHub
- **Admin panel remains private** (not pushed to GitHub)
- **Security tokens are protected** using environment variables

---

## 📋 Prerequisites

1. GitHub account
2. Git installed on your computer
3. Your BattleMaster update server files

---

## 🚀 Step-by-Step Setup

### Step 1: Create GitHub Repository

1. Go to [GitHub](https://github.com/new)
2. Create a new repository:
   - **Name**: `battlemaster-update-server`
   - **Visibility**: **Public** (so updates can be downloaded without authentication)
   - **Don't** initialize with README (we already have files)

### Step 2: Prepare Local Repository

Open PowerShell in your update server directory:

```powershell
cd c:\xampp\htdocs\battlemaster-update-server
```

### Step 3: Configure Git (First Time Only)

```powershell
git config --global user.name "Your Name"
git config --global user.email "your.email@example.com"
```

### Step 4: Initialize and Push

```powershell
# Initialize git repository
git init

# Add all files (sensitive files are excluded by .gitignore)
git add .

# Create first commit
git commit -m "Initial commit - BattleMaster Update Server"

# Add your GitHub repository as remote (replace YOUR-USERNAME)
git remote add origin https://github.com/YOUR-USERNAME/battlemaster-update-server.git

# Push to GitHub
git branch -M main
git push -u origin main
```

### Step 5: Update URLs in version.json

Your `version.json` download URLs should now point to GitHub:

**Format:**
```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/VERSION.zip
```

**Example for version 2.6.3:**
```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/2.6.3.zip
```

### Step 6: Create .env File (Local Only)

Copy the example file:
```powershell
cp .env.example .env
```

Edit `.env` and update:
```env
GITHUB_USERNAME=your-actual-username
GITHUB_REPO=battlemaster-update-server
GITHUB_BRANCH=main

ADMIN_PASSWORD=your_secure_password_here
AUTH_TOKEN=your_secret_token_here_2026

UPDATE_SERVER_URL=https://raw.githubusercontent.com/your-actual-username/battlemaster-update-server/main
```

---

## 📦 Publishing Updates to GitHub

### Method 1: Using Git Commands (Recommended)

Every time you create a new update:

```powershell
cd c:\xampp\htdocs\battlemaster-update-server

# Add the new files
git add updates/2.6.4.zip
git add updates/2.6.4.json
git add version.json

# Commit
git commit -m "Release v2.6.4 - Your update description"

# Push to GitHub
git push origin main
```

### Method 2: Using GitHub Desktop

1. Open GitHub Desktop
2. Select your repository
3. Check the files you want to commit
4. Write commit message
5. Click "Commit to main"
6. Click "Push origin"

---

## 🔒 Security Best Practices

### Files That Are NOT Pushed to GitHub:

These are automatically excluded by `.gitignore`:

- ✅ `admin.php` - Admin panel (keep private)
- ✅ `config.json` - Contains sensitive settings
- ✅ `.env` - Your environment variables
- ✅ `debug-*.php` - Debug scripts
- ✅ `test-*.php` - Test scripts
- ✅ `*.zip` files - Update packages (use GitHub Releases instead)

### Files That ARE Pushed to GitHub:

- ✅ `index.php` - Public API endpoint (read-only)
- ✅ `version.json` - Version metadata (public)
- ✅ `*.json` metadata files in `/updates` folder
- ✅ `README.md` - Documentation
- ✅ `.gitignore` - Git configuration
- ✅ `.env.example` - Environment template

---

## 🌐 Update Your BattleMaster Application Config

In your main BattleMaster app, update `config/update.php`:

```php
<?php

return [
    'enabled' => env('UPDATE_ENABLED', true),
    
    // GitHub-based update server
    'api_url' => 'https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main',
    
    // Security token (must match your .env AUTH_TOKEN)
    'auth_token' => env('UPDATE_AUTH_TOKEN', 'your_secret_token_here_2026'),
    
    'check_endpoint' => '/version.json',
    'download_endpoint' => '/updates/{version}.zip',
    
    'backup_enabled' => true,
    'verify_checksum' => true,
];
```

---

## 📥 Handling Large ZIP Files

Since GitHub has file size limits (100MB), you have two options:

### Option 1: GitHub Releases (Recommended for Large Files)

```powershell
# Create a release on GitHub
# 1. Go to your repo → Releases → Create new release
# 2. Tag: v2.6.4
# 3. Title: BattleMaster v2.6.4
# 4. Upload your ZIP file as release asset
# 5. Publish release

# Update download URL in version.json:
"download_url": "https://github.com/YOUR-USERNAME/battlemaster-update-server/releases/download/v2.6.4/2.6.4.zip"
```

### Option 2: Git LFS (For Automated Workflow)

```powershell
# Install Git LFS (one time)
git lfs install

# Track ZIP files
git lfs track "*.zip"
git add .gitattributes

# Now ZIP files will be stored in Git LFS
git add updates/2.6.4.zip
git commit -m "Add v2.6.4 update"
git push origin main
```

---

## ✅ Verification Checklist

After setup, verify everything works:

- [ ] Can you access version.json on GitHub?
  - `https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/version.json`

- [ ] Can you download a ZIP file from GitHub?
  - `https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/2.6.3.zip`

- [ ] Is admin.php NOT visible on GitHub?
  - Check your repository - admin.php should be missing

- [ ] Can your BattleMaster app check for updates?
  - Test from your update page in admin panel

---

## 🔄 Daily Workflow

### Creating and Publishing a New Update:

```powershell
# 1. Create the update ZIP locally
# (use your existing process)

# 2. Update version.json with GitHub URLs
# Make sure download_url points to GitHub

# 3. Commit and push
git add .
git commit -m "Release v2.6.5 - Fix critical bug"
git push origin main

# 4. Wait 1-2 minutes for GitHub CDN to update
# Then test the update from your app
```

---

## 🐛 Troubleshooting

### "404 Not Found" when downloading updates
- Check if file exists in GitHub repo
- Verify the URL is using `raw.githubusercontent.com`
- Wait a few minutes - GitHub CDN can take time to update

### "admin.php was accidentally pushed"
```powershell
# Remove from git history
git rm --cached admin.php
git commit -m "Remove admin.php from tracking"
git push origin main
```

### ZIP file too large
- Use GitHub Releases instead of committing directly
- Or enable Git LFS (see above)

---

## 📞 Support

For questions or issues, create an issue on GitHub or contact support.

---

**Last Updated**: January 3, 2026
**Version**: 1.0.0
