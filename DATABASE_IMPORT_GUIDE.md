# 📦 Complete Database Import Guide

## ✅ Updated: `database/ipcr_system.sql`

Ang buong database export ay **UPDATED NA** with AI Provider support!

### 🆕 What's New in this Database:

- ✅ **All existing tables** (users, periods, departments, etc.)
- ✅ **All existing data** (sample users, audit logs, etc.)
- ✅ **NEW: `ai_provider` config** - Choose between Gemini or OpenRouter
- ✅ **NEW: `openrouter_api_key` config** - Store OpenRouter API key
- ✅ **Existing: `gemini_api_key` config** - Store Gemini API key (preserved)
- ✅ **Existing: `ai_verification_mode` config** - Advisory/Strict/Disabled

---

## 📋 How to Import (Online Database)

### Method 1: phpMyAdmin (Recommended)

1. **Backup your current database first!**
   - Export current database as backup
   - Download the .sql file

2. **Drop existing database (if fresh install)**
   ```sql
   DROP DATABASE IF EXISTS ipcr_system;
   CREATE DATABASE ipcr_system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

3. **Import the file**
   - Login to phpMyAdmin
   - Select `ipcr_system` database
   - Click **Import** tab
   - Choose file: `database/ipcr_system.sql`
   - Click **Go**
   - Wait for completion (may take 1-2 minutes)

4. **Verify import**
   ```sql
   SELECT * FROM system_config 
   WHERE config_key IN ('ai_provider', 'openrouter_api_key', 'gemini_api_key', 'ai_verification_mode');
   ```

   Expected result:
   ```
   ai_provider          | gemini
   openrouter_api_key   | (empty)
   gemini_api_key       | (empty)
   ai_verification_mode | advisory
   ```

---

### Method 2: Command Line (Advanced)

```bash
mysql -u username -p ipcr_system < database/ipcr_system.sql
```

---

## 🔧 After Import: Setup AI Provider

### Option A: OpenRouter (100% FREE - Recommended)

1. **Get API Key**
   - Go to https://openrouter.ai/keys
   - Sign in with Google
   - Create key (starts with `sk-or-v1-...`)

2. **Configure in Database**
   ```sql
   UPDATE system_config SET config_value = 'openrouter' WHERE config_key = 'ai_provider';
   UPDATE system_config SET config_value = 'sk-or-v1-YOUR_KEY' WHERE config_key = 'openrouter_api_key';
   UPDATE system_config SET config_value = 'advisory' WHERE config_key = 'ai_verification_mode';
   ```

3. **OR configure in Admin Panel**
   - Login as Admin
   - Go to Settings
   - Scroll to "AI Evidence Verification"
   - Select **OpenRouter**
   - Paste API key
   - Save

---

### Option B: Google Gemini (Requires Billing)

1. **Get API Key**
   - Go to https://aistudio.google.com/app/apikey
   - Enable billing at https://aistudio.google.com
   - Create API key (starts with `AIza...`)

2. **Configure in Database**
   ```sql
   UPDATE system_config SET config_value = 'gemini' WHERE config_key = 'ai_provider';
   UPDATE system_config SET config_value = 'AIzaYOUR_KEY' WHERE config_key = 'gemini_api_key';
   ```

3. **OR configure in Admin Panel**
   - Same as above, but select **Gemini**

---

## 📊 What's Included in the Database

### Tables (Complete Structure + Data):
- ✅ `users` - All user accounts
- ✅ `departments` - All departments
- ✅ `rating_periods` - Academic periods
- ✅ `templates` - IPCR templates
- ✅ `mfos` - Major Final Outputs
- ✅ `kras` - Key Result Areas
- ✅ `success_indicators` - Performance indicators
- ✅ `ipcr_forms` - Faculty IPCR forms
- ✅ `form_si_values` - SI ratings and values
- ✅ `evidence_files` - Uploaded evidence files
- ✅ `form_ratings` - Dean ratings
- ✅ `edit_requests` - Faculty edit requests
- ✅ `extensions` - Deadline extensions
- ✅ `submission_tracking` - Submission status
- ✅ `audit_log` - System audit trail
- ✅ `system_config` - System settings (UPDATED!)

### Sample Data Included:
- ✅ Default admin user
- ✅ Sample faculty users
- ✅ Sample departments
- ✅ Sample periods
- ✅ Default system settings
- ✅ **NEW:** AI provider settings

---

## 🔍 Verify Installation

Run these queries after import:

### 1. Check Tables
```sql
SHOW TABLES;
```
Should show 18+ tables

### 2. Check AI Config
```sql
SELECT config_key, 
       CASE 
         WHEN config_key LIKE '%api_key%' THEN CONCAT(LEFT(config_value, 20), '...')
         ELSE config_value 
       END as value
FROM system_config 
WHERE config_key LIKE '%ai%' OR config_key LIKE '%openrouter%'
ORDER BY id;
```

Expected:
- `gemini_api_key` - (empty or masked)
- `ai_verification_mode` - advisory
- `ai_provider` - gemini
- `openrouter_api_key` - (empty)

### 3. Check Users
```sql
SELECT id, employee_id, CONCAT(first_name, ' ', last_name) as name, role 
FROM users 
LIMIT 5;
```

---

## 🚨 Troubleshooting

### Error: "Table already exists"
- Drop the database first
- Or use "DROP TABLE IF EXISTS" before import

### Error: "Access denied"
- Check database user permissions
- Make sure user has CREATE, DROP, INSERT privileges

### Error: "Lost connection during query"
- File too large for phpMyAdmin
- Increase `max_execution_time` and `post_max_size` in php.ini
- Or use command line import

### Import stuck/slow
- Large database, be patient
- Check server resources
- Use command line for faster import

---

## 📝 Important Notes

1. **Backup First!** - Always backup before importing
2. **Fresh Install Recommended** - Drop old database for clean install
3. **Update Credentials** - Change default passwords after import
4. **Configure AI** - Don't forget to add API key after import
5. **Test Upload** - Test file upload with AI verification
6. **Check Logs** - Monitor error logs for issues

---

## 🎯 Next Steps After Import

1. ✅ Import `ipcr_system.sql` (DONE)
2. 📤 Upload ALL updated PHP files to server
3. 🔧 Configure AI provider in Settings
4. 🧪 Test with sample upload
5. 👥 Update user accounts
6. 📊 Create rating periods
7. 🚀 Go live!

---

## 🆘 Need Help?

- Check server error logs
- Review AI verification guide: `AI_PROVIDER_SETUP_GUIDE.md`
- Test API keys with diagnostic tools
- Contact support if issues persist

---

**File Location:** `database/ipcr_system.sql`  
**Last Updated:** October 14, 2026  
**Version:** 2.0 (with AI Provider Selection)
