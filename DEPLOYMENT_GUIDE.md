# 🚀 COMPLETE GITHUB DEPLOYMENT GUIDE

## What You're About to Do

You're moving from **localhost-based updates** to **GitHub-hosted updates**. This means:

✅ Updates will be served from GitHub (not your computer)  
✅ Both local development AND production will use GitHub  
✅ Admin panel and secrets stay private (never pushed to GitHub)  
✅ Anyone can access update files (but downloads need auth token)

---

## 📝 Prerequisites Checklist

Before starting, make sure you have:

- [ ] GitHub account created
- [ ] Git installed on your computer
- [ ] Your GitHub username ready
- [ ] Admin password decided (for .env)
- [ ] Auth token decided (for .env)

---

## 🎯 Step-by-Step Instructions

### STEP 1: Create Environment File

```powershell
# Run this in PowerShell from your update server directory
cd c:\xampp\htdocs\battlemaster-update-server
cp .env.example .env
notepad .env
```

**Edit `.env` and update these values:**

```env
# Your GitHub username (IMPORTANT!)
GITHUB_USERNAME=YourActualGitHubUsername

# Repository name (keep as is unless you name it differently)
GITHUB_REPO=battlemaster-update-server

# Branch (keep as is)
GITHUB_BRANCH=main

# Security - Change these!
ADMIN_PASSWORD=your_super_secure_password_123
AUTH_TOKEN=BattleMaster_Secret_Update_Key_2026_XYZ

# This will be auto-filled based on your username
UPDATE_SERVER_URL=https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main
```

💾 **Save and close the file**

---

### STEP 2: Run Setup Script

```powershell
# This will update all URLs from localhost to GitHub
.\setup-github.ps1
```

This script will:
- ✅ Check your .env configuration
- ✅ Update all localhost URLs to GitHub URLs in version.json
- ✅ Update all JSON files in the updates folder
- ✅ Initialize git if needed
- ✅ Show you what files are protected

---

### STEP 3: Create GitHub Repository

1. **Go to GitHub:** https://github.com/new

2. **Fill in:**
   - Repository name: `battlemaster-update-server`
   - Description: `Update distribution server for BattleMaster`
   - Visibility: **Public** ⚠️ (must be public for raw URLs to work)
   - ❌ **DO NOT** check "Add README" (we have one)
   - ❌ **DO NOT** check "Add .gitignore" (we have one)

3. **Click "Create repository"**

---

### STEP 4: Push to GitHub

Copy your repository URL, then run:

```powershell
# Add all files (sensitive ones are automatically excluded)
git add .

# Create first commit
git commit -m "Initial commit - BattleMaster Update Server"

# Add GitHub as remote (REPLACE YOUR-USERNAME!)
git remote add origin https://github.com/YOUR-USERNAME/battlemaster-update-server.git

# Set main branch
git branch -M main

# Push to GitHub
git push -u origin main
```

**IMPORTANT:** Replace `YOUR-USERNAME` with your actual GitHub username!

---

### STEP 5: Verify GitHub Deployment

Open these URLs in your browser (replace YOUR-USERNAME):

**1. Check version.json:**
```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/version.json
```

**2. Check a specific version metadata:**
```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/2.6.3.json
```

If both URLs show JSON data, ✅ **SUCCESS!**

---

### STEP 6: Update Your BattleMaster Application

In your **main BattleMaster project**, update the config file:

**File:** `config/update.php`

```php
<?php

return [
    'enabled' => env('UPDATE_ENABLED', true),
    
    // CHANGE THIS - Use your GitHub username
    'api_url' => 'https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main',
    
    // This token must match your .env AUTH_TOKEN
    'auth_token' => env('UPDATE_AUTH_TOKEN', 'BattleMaster_Secret_Update_Key_2026_XYZ'),
    
    'check_endpoint' => '/version.json',
    'download_endpoint' => '/updates/{version}.zip',
    
    'backup_enabled' => true,
    'verify_checksum' => true,
    'timeout' => 300,
];
```

**Add to your BattleMaster's `.env` file:**
```env
UPDATE_ENABLED=true
UPDATE_AUTH_TOKEN=BattleMaster_Secret_Update_Key_2026_XYZ
```

⚠️ **The `UPDATE_AUTH_TOKEN` must match your update server's `AUTH_TOKEN`**

---

### STEP 7: Test the Update System

1. **Open your BattleMaster admin panel**
2. **Go to:** System → Updates
3. **Click:** "Check for Updates"
4. **You should see:** Latest version info from GitHub! 🎉

---

## 📦 How to Publish New Updates

### For Small Updates (<100MB)

```powershell
# 1. Create your update ZIP
# (use your existing process)

# 2. Update version.json and create version metadata file
# Make sure download_url points to GitHub

# 3. Commit and push
cd c:\xampp\htdocs\battlemaster-update-server
git add version.json updates/2.6.4.json updates/2.6.4.zip
git commit -m "Release v2.6.4 - Bug fixes"
git push origin main

# 4. Wait 1-2 minutes for GitHub CDN to propagate
# Then test the update!
```

### For Large Updates (>100MB)

Use **GitHub Releases** instead:

1. **Create update ZIP locally** (don't commit it)

2. **Go to your GitHub repo** → Releases → "Create new release"

3. **Fill in:**
   - Tag: `v2.6.4`
   - Title: `BattleMaster v2.6.4`
   - Description: Your changelog
   - Upload ZIP file as attachment

4. **Update version.json** to use release URL:
   ```json
   "download_url": "https://github.com/YOUR-USERNAME/battlemaster-update-server/releases/download/v2.6.4/2.6.4.zip"
   ```

5. **Commit and push version.json:**
   ```powershell
   git add version.json updates/2.6.4.json
   git commit -m "Release v2.6.4 metadata"
   git push origin main
   ```

---

## 🔒 Security Overview

### Files Protected (NOT on GitHub)

These are in `.gitignore` and will NEVER be pushed:

- ✅ `admin.php` - Your admin panel
- ✅ `config.json` - Sensitive config
- ✅ `.env` - Your passwords and tokens
- ✅ `debug-*.php` - Debug scripts
- ✅ `test-*.php` - Test scripts
- ✅ `patch_*.ps1` - Patch scripts with local paths

### Files Public (on GitHub)

- ✅ `version.json` - Version information (safe, no secrets)
- ✅ `index-github.php` - API endpoint (uses env vars, safe)
- ✅ `updates/*.json` - Metadata files (safe, public info)
- ✅ `*.md` - Documentation
- ✅ `.gitignore` - Git config
- ✅ `.env.example` - Template only

### How Downloads Stay Secure

Even though files are on GitHub:
- ✅ Download URLs require `AUTH_TOKEN` parameter
- ✅ Token is in your private `.env` (not on GitHub)
- ✅ Only your BattleMaster app knows the token
- ✅ Without correct token, downloads fail with 403 Forbidden

---

## 🌍 Production vs Development

### Development (Localhost)
- Update server runs on: `http://localhost/battlemaster-update-server`
- BattleMaster app points to: GitHub URLs
- You can test updates before pushing to GitHub

### Production (Live Server)
- Update server doesn't need to be hosted anywhere!
- BattleMaster app points to: Same GitHub URLs
- Both environments use the same source

**This is the beauty of GitHub hosting!** 🎉

---

## 🔄 Workflow Example

### Scenario: You fixed a critical bug

```powershell
# 1. Create update package
cd c:\xampp\htdocs\battlemaster
# ... create your ZIP file: 2.6.5.zip

# 2. Move to update server
cd c:\xampp\htdocs\battlemaster-update-server

# 3. Copy ZIP to updates folder
cp ../battlemaster/2.6.5.zip updates/

# 4. Calculate checksum
(Get-FileHash updates/2.6.5.zip -Algorithm MD5).Hash.ToLower()

# 5. Create metadata file: updates/2.6.5.json
# (copy from 2.6.4.json and modify)

# 6. Update version.json to reference 2.6.5

# 7. Commit and push
git add version.json updates/2.6.5.json updates/2.6.5.zip
git commit -m "Release v2.6.5 - Critical bug fix"
git push origin main

# 8. Done! Users can now update
```

---

## ❓ Troubleshooting

### "404 Not Found" when accessing GitHub URLs

**Problem:** GitHub says file doesn't exist  
**Solution:** 
- Check repository is **Public** (not Private)
- Verify file was actually pushed: `git status`
- Wait 1-2 minutes for CDN propagation

### "Updates not showing" in BattleMaster

**Problem:** Check update shows no updates  
**Solution:**
- Verify `config/update.php` has correct GitHub URL
- Check your BattleMaster version is older than latest
- Test the version.json URL directly in browser

### "Token invalid" error

**Problem:** Downloads fail with 403  
**Solution:**
- Make sure `AUTH_TOKEN` in update server `.env` matches
- Make sure `UPDATE_AUTH_TOKEN` in BattleMaster `.env` matches
- Tokens are case-sensitive!

### "Admin panel still showing on GitHub"

**Problem:** admin.php is visible in repository  
**Solution:**
```powershell
# Remove it from git tracking
git rm --cached admin.php
git commit -m "Remove admin.php from tracking"
git push origin main
```

---

## 📞 Need Help?

If you get stuck:
1. Check the [GITHUB_SETUP.md](GITHUB_SETUP.md) for detailed explanations
2. Review `.gitignore` to verify files are excluded
3. Run `git status` to see what will be committed

---

## ✅ Final Checklist

Before going live, verify:

- [ ] .env file created with your values
- [ ] GitHub repository created (public)
- [ ] setup-github.ps1 executed successfully
- [ ] Git push completed without errors
- [ ] version.json accessible on GitHub
- [ ] BattleMaster config/update.php updated
- [ ] AUTH_TOKEN matches between both apps
- [ ] Test update shows latest version
- [ ] admin.php NOT visible on GitHub
- [ ] .env NOT visible on GitHub

---

## 🎉 You're Done!

Your update server is now:
- ✅ Hosted on GitHub
- ✅ Accessible from anywhere
- ✅ Secure (admin panel private)
- ✅ Free forever (GitHub's gift to you)
- ✅ Fast (GitHub's CDN)

**Welcome to the cloud! ☁️**

---

**Last Updated:** January 3, 2026  
**Version:** 1.0.0
