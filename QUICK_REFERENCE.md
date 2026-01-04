# 🎯 Quick Reference - GitHub Setup

## What Changed?

### ❌ Before (Localhost)
```
BattleMaster App → http://localhost/battlemaster-update-server → Your PC
```

### ✅ After (GitHub)
```
BattleMaster App → GitHub CDN → Anywhere in the world! 🌍
```

---

## The Setup (In 3 Commands)

```powershell
# 1. Setup environment
.\setup-github.ps1

# 2. Push to GitHub
git add . && git commit -m "Initial commit" && git push -u origin main

# 3. Test
# Open: https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/version.json
```

---

## File Protection Summary

| File | Status | Why |
|------|--------|-----|
| `admin.php` | 🔒 PRIVATE | Admin control panel |
| `config.json` | 🔒 PRIVATE | Sensitive settings |
| `.env` | 🔒 PRIVATE | Passwords & tokens |
| `index.php` | 🔒 PRIVATE | Has hardcoded token |
| `debug-*.php` | 🔒 PRIVATE | Development files |
| `test-*.php` | 🔒 PRIVATE | Test files |
| |||
| `index-github.php` | 🌐 PUBLIC | Safe API (uses .env) |
| `version.json` | 🌐 PUBLIC | Version metadata |
| `updates/*.json` | 🌐 PUBLIC | Update metadata |
| `*.md` | 🌐 PUBLIC | Documentation |

---

## URL Structure

### Version Check
```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/version.json
```

### Metadata
```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/2.6.3.json
```

### Download (Small files)
```
https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main/updates/2.6.3.zip
```

### Download (Large files - use Releases)
```
https://github.com/YOUR-USERNAME/battlemaster-update-server/releases/download/v2.6.3/2.6.3.zip
```

---

## BattleMaster Config

**File:** `config/update.php`

```php
'api_url' => 'https://raw.githubusercontent.com/YOUR-USERNAME/battlemaster-update-server/main',
'auth_token' => env('UPDATE_AUTH_TOKEN', 'your_token_here'),
```

**File:** `.env`

```env
UPDATE_AUTH_TOKEN=your_token_here
```

---

## Publishing Updates

### Quick Process
```powershell
# Create update
cd c:\xampp\htdocs\battlemaster-update-server

# Add to git
git add version.json updates/2.6.4.json updates/2.6.4.zip

# Push
git commit -m "Release v2.6.4"
git push origin main

# Done! Live in ~1 minute
```

---

## Emergency: Undo Git Push

If you accidentally pushed sensitive files:

```powershell
# Remove from git
git rm --cached admin.php
git commit -m "Remove sensitive file"
git push origin main

# OR - Delete entire repo and start over
# (Delete on GitHub, then re-create)
```

---

## Support Links

- 📖 [Full Guide](DEPLOYMENT_GUIDE.md)
- 📋 [Checklist](CHECKLIST.md)
- 🔧 [GitHub Setup](GITHUB_SETUP.md)
- 📦 [Create Updates](HOW_TO_CREATE_UPDATES.md)

---

## Common Commands

```powershell
# Check what will be committed
git status

# See what's ignored
git status --ignored

# View remote URL
git remote -v

# Pull latest from GitHub
git pull origin main

# Check if .env is ignored
git check-ignore .env
```

---

**Remember:** `.env` and `admin.php` should NEVER appear in `git status`! 🔒
