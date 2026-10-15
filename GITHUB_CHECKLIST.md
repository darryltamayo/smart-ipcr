# ✅ GitHub Upload Checklist

Complete this checklist before pushing to GitHub.

## 🔒 Security Check

### Sensitive Files Removed/Secured
- [x] `config/database.php` - Credentials removed, using safe defaults
- [x] `config/email.php` - Credentials removed, using placeholders
- [x] `.gitignore` created and includes sensitive files
- [ ] No API keys in code (all in database)
- [ ] No hardcoded passwords
- [ ] No personal email addresses in code

### Configuration Templates Created
- [x] `config/database.example.php` - Template file exists
- [x] `config/email.config.example.php` - Template file exists
- [ ] Instructions in README for setup

## 📝 Documentation

### Essential Files
- [x] `README.md` or `GITHUB_README.md` - Project overview
- [x] `SECURITY.md` - Security guidelines
- [x] `.gitignore` - Ignore rules configured
- [ ] `LICENSE` - License file (optional)
- [ ] `CONTRIBUTING.md` - Contribution guidelines (optional)

### Technical Documentation
- [x] Database schema (`database/ipcr_system.sql`)
- [x] Migration files included
- [x] AI Provider setup guide
- [x] Deployment guide
- [ ] API documentation (if needed)

## 🧹 Code Cleanup

### Remove Development/Test Files (Optional)
Consider removing or moving to `.gitignore`:
- [ ] `test-*.php` files (keep if useful for others)
- [ ] `debug-*.php` files
- [ ] `fix-*.php` files (one-time fixes)
- [ ] Personal test files

### Clean Comments
- [ ] Remove personal notes/TODOs
- [ ] Remove debug comments
- [ ] Keep useful documentation comments

## 📦 Dependencies

### Composer
- [x] `composer.json` exists
- [ ] `vendor/` in `.gitignore`
- [ ] Dependencies documented in README

### External Libraries
- [ ] PHPMailer included/documented
- [ ] Bootstrap CDN links working
- [ ] All external dependencies documented

## 🗂️ File Structure

### Directory Structure
- [x] Organized by role (admin, dean, faculty)
- [x] Separate API directory
- [x] Config in config/ directory
- [x] Database files in database/ directory

### Required Directories (with .gitkeep if needed)
```bash
# Create .gitkeep files for empty directories
touch uploads/.gitkeep
touch evidence/.gitkeep
touch exports/.gitkeep
```

## 🔍 Final Verification

### Before First Push
```bash
# 1. Check git status
git status

# Should NOT see:
# - config/database.php (with real credentials)
# - config/email.php (with real credentials)
# - uploads/ directory contents
# - Any .log files

# 2. Verify .gitignore is working
git check-ignore config/database.php
# Should output: config/database.php

# 3. Review what will be committed
git add .
git status

# 4. Remove cached sensitive files if needed
git rm --cached config/database.php
git rm --cached config/email.php
```

### Repository Settings (After First Push)

On GitHub:
- [ ] Set repository description
- [ ] Add topics/tags (php, mysql, ipcr, education)
- [ ] Choose license
- [ ] Enable Issues (for bug reports)
- [ ] Configure branch protection (optional)
- [ ] Add collaborators (if team project)

## 📋 Optional Enhancements

### GitHub Features
- [ ] Create GitHub Actions for CI/CD
- [ ] Add issue templates
- [ ] Add pull request template
- [ ] Setup GitHub Pages for documentation
- [ ] Add project board for task tracking

### Documentation
- [ ] Add screenshots to README
- [ ] Create user manual
- [ ] Add API documentation
- [ ] Create video tutorial links

### Code Quality
- [ ] Run PHP linter
- [ ] Check for SQL injection vulnerabilities
- [ ] Review XSS protection
- [ ] Validate file upload security

## 🚀 Ready to Push!

Once all items are checked:

```bash
# Initialize git (if not done)
git init

# Add all files
git add .

# First commit
git commit -m "Initial commit: ISU IPCR Management System"

# Add remote
git remote add origin https://github.com/yourusername/ipcr-system.git

# Push to GitHub
git branch -M main
git push -u origin main
```

## 📝 After Upload

### Verify on GitHub
- [ ] All files uploaded correctly
- [ ] README displays properly
- [ ] No sensitive data visible
- [ ] Links in documentation work
- [ ] License file present

### Share
- [ ] Share repository link with team
- [ ] Add to portfolio
- [ ] Update LinkedIn/resume
- [ ] Star your own repo! ⭐

---

## ⚠️ Emergency: If You Accidentally Committed Secrets

If you accidentally pushed sensitive data:

1. **Remove from history:**
```bash
# Remove file from all commits
git filter-branch --force --index-filter \
  "git rm --cached --ignore-unmatch config/database.php" \
  --prune-empty --tag-name-filter cat -- --all

# Force push
git push origin --force --all
```

2. **Immediately change:**
- Database password
- Email password
- API keys
- Any exposed credentials

3. **Alternative (easier but loses history):**
- Delete repository from GitHub
- Remove sensitive files
- Create fresh repository and push again

---

**Remember:** Once pushed to GitHub, consider data as public even if repository is private!
