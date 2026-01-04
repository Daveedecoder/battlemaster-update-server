# 📋 GitHub Deployment Checklist

Use this checklist to ensure you complete all steps correctly.

---

## Pre-Deployment

- [ ] I have a GitHub account
- [ ] Git is installed on my computer
- [ ] I know my GitHub username: ___________________
- [ ] I have decided on an admin password
- [ ] I have decided on an auth token

---

## Step 1: Environment Setup

- [ ] Copied `.env.example` to `.env`
- [ ] Updated `GITHUB_USERNAME` in `.env`
- [ ] Updated `ADMIN_PASSWORD` in `.env`
- [ ] Updated `AUTH_TOKEN` in `.env`
- [ ] Saved `.env` file

---

## Step 2: GitHub Repository

- [ ] Created new repository on GitHub
- [ ] Repository name: `battlemaster-update-server`
- [ ] Repository is set to **Public**
- [ ] Did NOT add README (we have one)
- [ ] Did NOT add .gitignore (we have one)
- [ ] Repository URL: ___________________________________

---

## Step 3: Update URLs

- [ ] Ran `.\setup-github.ps1` successfully
- [ ] Script updated version.json
- [ ] Script updated updates/*.json files
- [ ] Verified GitHub URLs in files

---

## Step 4: Git Push

- [ ] Ran `git add .`
- [ ] Ran `git commit -m "Initial commit"`
- [ ] Ran `git remote add origin [URL]`
- [ ] Ran `git push -u origin main`
- [ ] Push completed without errors

---

## Step 5: Verification

- [ ] Can access version.json on GitHub:
  ```
  https://raw.githubusercontent.com/[USERNAME]/battlemaster-update-server/main/version.json
  ```
- [ ] Can access metadata file on GitHub:
  ```
  https://raw.githubusercontent.com/[USERNAME]/battlemaster-update-server/main/updates/2.6.3.json
  ```
- [ ] Both URLs return valid JSON (not 404)

---

## Step 6: Security Check

Verify these files are NOT on GitHub:

- [ ] `admin.php` - NOT visible in repository
- [ ] `config.json` - NOT visible in repository
- [ ] `.env` - NOT visible in repository
- [ ] `debug-*.php` - NOT visible in repository
- [ ] `test-*.php` - NOT visible in repository

Verify these files ARE on GitHub:

- [ ] `version.json` - VISIBLE in repository
- [ ] `index-github.php` - VISIBLE in repository
- [ ] `README-GITHUB.md` - VISIBLE in repository
- [ ] `.gitignore` - VISIBLE in repository
- [ ] `.env.example` - VISIBLE in repository

---

## Step 7: BattleMaster App Configuration

- [ ] Updated `config/update.php` with GitHub URL
- [ ] Added `UPDATE_AUTH_TOKEN` to BattleMaster's `.env`
- [ ] Token matches update server's `AUTH_TOKEN`

---

## Step 8: Testing

- [ ] Opened BattleMaster admin panel
- [ ] Navigated to System → Updates
- [ ] Clicked "Check for Updates"
- [ ] Saw latest version information
- [ ] Version shows from GitHub (not localhost)

---

## Post-Deployment

- [ ] Documented my GitHub username for future reference
- [ ] Saved `.env` file backup (in secure location, NOT on GitHub)
- [ ] Tested complete update process (optional but recommended)
- [ ] Read [DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md) for publishing updates

---

## 🎉 Completion

If all checkboxes are checked, you're done! 

Your update server is now:
✅ Hosted on GitHub  
✅ Accessible globally  
✅ Secure and private admin  
✅ Ready for production

---

## 🐛 Troubleshooting Reference

If you encounter issues, refer to:

1. **[DEPLOYMENT_GUIDE.md](DEPLOYMENT_GUIDE.md)** - Complete guide
2. **[GITHUB_SETUP.md](GITHUB_SETUP.md)** - Detailed setup instructions
3. **[HOW_TO_CREATE_UPDATES.md](HOW_TO_CREATE_UPDATES.md)** - Creating updates

---

**Date Completed:** ___________________  
**GitHub Username:** ___________________  
**Repository URL:** ___________________
