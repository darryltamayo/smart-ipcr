# 🤖 AI Evidence Verification - Complete Implementation Guide

## ✅ What Was Fixed

### 1. **Missing Gemini API Implementation** (ROOT CAUSE)
- **Problem**: Code only had OpenRouter implementation, but your API key is for Gemini
- **Solution**: Added complete Gemini API integration
- **Location**: `api/upload-evidence.php` lines 260-340

### 2. **API Quota/Error Handling**
- **Added**: Proper handling for HTTP 429 (quota exceeded)
- **Added**: Proper handling for HTTP 400 (invalid API key)
- **Added**: User-friendly error messages when quota is exceeded
- **Behavior**: In advisory mode, uploads continue even when AI fails

### 3. **Better User Feedback**
- **Before**: Generic "AI verification disabled" message
- **After**: Specific messages:
  - ✅ "AI Verified (85%)" - Valid evidence
  - ⚠️ "Uncertain Evidence (65%)" - Needs manual review
  - ⚠️ "AI Quota Exceeded" - Quota limit reached
  - ⚠️ "AI Configuration Issue" - API key problem
  - ℹ️ "AI is disabled in settings" - Disabled mode

### 4. **Enhanced Debug Logging**
- Added detailed error_log entries to track:
  - AI mode detection
  - API key validation
  - API calls and responses
  - Quota errors
  - Success/failure reasons

---

## 🎯 How It Works

### Upload Flow:
```
1. Faculty uploads file → api/upload-evidence.php
2. Check AI mode and API key from database
3. If enabled + has key → Call Gemini API
4. Get verdict (valid/uncertain/invalid) + confidence score
5. In ADVISORY mode → Always allow upload (just show warning)
6. In STRICT mode → Block only if confidence < 50%
7. Return response to frontend
8. Show appropriate toast notification
```

### AI Modes:

| Mode | AI Checks? | Blocks Upload? | Use Case |
|------|-----------|----------------|----------|
| **Disabled** | ❌ No | ❌ Never | No API key or AI not needed |
| **Advisory** | ✅ Yes | ❌ Never | Recommended - shows confidence but allows all uploads |
| **Strict** | ✅ Yes | ⚠️ If < 50% | Only if you need hard validation (may reject valid files) |

---

## 🧪 Testing Checklist

### Before Testing:
- [ ] Check database has API key: `SELECT config_value FROM system_config WHERE config_key='gemini_api_key'`
- [ ] Check AI mode is 'advisory': `SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'`
- [ ] Verify files uploaded to the server (check `uploads/` folder)

### Test Cases:

#### Test 1: Valid Evidence (Should Pass)
1. Upload a file that matches the requirement (e.g., "Syllabus.pdf" for Syllabus indicator)
2. **Expected**: ✅ "AI Verified (80-100%): [filename] uploaded!"
3. **Check**: File appears in evidence list with green verified badge

#### Test 2: Uncertain Evidence (Should Warn)
1. Upload a file with ambiguous name (e.g., "Document.pdf")
2. **Expected**: ⚠️ "Uploaded (50-79% confidence): [filename] - AI suggests manual review"
3. **Check**: File uploaded but marked as "pending" verification

#### Test 3: Invalid Evidence (Advisory Mode)
1. Upload completely wrong file (e.g., "Random.jpg" for "Class Schedule" requirement)
2. **Expected**: ⚠️ Warning but upload still succeeds (advisory mode allows all)
3. **Check**: File uploaded, low confidence score shown

#### Test 4: API Quota Exceeded
1. If quota exceeded (429 error from API)
2. **Expected**: ⚠️ "Gemini API Quota Exceeded - AI verification unavailable"
3. **Check**: File still uploaded successfully, will be reviewed manually

#### Test 5: No API Key
1. Remove API key from admin settings
2. **Expected**: ℹ️ "Upload Complete (No API key configured)"
3. **Check**: File uploaded without AI check

#### Test 6: Disabled Mode
1. Set AI mode to "Disabled" in admin settings
2. **Expected**: ℹ️ "Upload Complete (AI is disabled in settings)"
3. **Check**: File uploaded, no AI processing

---

## 📍 Key Files Modified

### Backend:
1. **`api/upload-evidence.php`** (Lines 130-340)
   - Added complete Gemini API implementation
   - Enhanced error handling for quota/API errors
   - Better debug logging

### Frontend:
2. **`faculty/ipcr-form.php`** (Lines 1050-1120)
   - Improved toast notifications
   - Quota error detection and messaging
   - Mode-specific user feedback

### Admin:
3. **`admin/settings.php`** (Lines 220-240)
   - Added quota management guidance
   - Clear explanation of API limits
   - Links to increase quota

---

## ⚠️ API Quota Limits (Gemini Free Tier)

### Default Limits:
- **15 requests per minute**
- **1,000,000 tokens per day**
- **1,500 requests per day**

### When Quota is Exceeded:
- Users see: "⚠️ AI verification temporarily unavailable"
- Files are **still uploaded successfully**
- System logs the quota error
- Admin gets notified through error logs

### To Increase Quota:
1. Enable billing on Google Cloud Console: https://console.cloud.google.com/billing
2. Upgrade to paid tier for higher limits
3. Or wait for daily reset (midnight Pacific Time)

---

## 🔍 Troubleshooting

### "AI verification disabled" message still appears:
1. Check database connection is working
2. Verify API key exists: `SELECT * FROM system_config WHERE config_key='gemini_api_key'`
3. Check error logs for specific error message
4. Test API key manually: run `test-gemini-key.php`

### AI not running but should be:
1. Check `error_log` file for debug output
2. Look for ">>> AI CHECK STARTING <<<" in logs
3. If not found, check:
   - AI mode is not 'disabled'
   - API key is not empty
   - Database query succeeded

### Quota exceeded often:
1. Monitor usage in Google Cloud Console
2. Consider enabling billing for higher limits
3. Or set AI mode to "Disabled" to stop using quota
4. Advisory mode is quota-friendly (doesn't block users)

### Files not uploading at all:
1. Check `uploads/` folder exists and is writable
2. Verify file size is under 10MB
3. Check file type is allowed (pdf, doc, docx, xls, xlsx, jpg, jpeg, png, gif, webp)
4. Look at browser console for JavaScript errors

---

## 📊 Success Indicators

### Everything is working correctly when:
- ✅ Files upload successfully
- ✅ AI confidence scores appear in toast notifications
- ✅ Evidence list shows uploaded files
- ✅ Dean can see AI verification status in review queue
- ✅ Quota errors show helpful messages (not generic errors)
- ✅ Advisory mode never blocks uploads
- ✅ Error logs show detailed AI processing info

---

## 🚀 Next Steps

### Recommended:
1. **Test with real faculty uploads** - Have 2-3 faculty test uploading different file types
2. **Monitor error logs** - Check for any API errors or quota issues
3. **Review AI verdicts** - In dean review queue, check if AI verdicts are accurate
4. **Adjust mode if needed** - Stay on Advisory for now, only use Strict if you need it

### Optional Enhancements:
1. Add email notification to admin when quota is exceeded
2. Display API usage statistics in admin dashboard
3. Add "Retry AI verification" button in dean review interface
4. Log AI verdict accuracy for future improvements

---

## 📞 Support

If you encounter issues:
1. Check error logs first (hosting cPanel → Error Logs)
2. Look for "AI UPLOAD DEBUG" entries
3. Share the specific error message
4. Check if issue is quota-related or code-related

---

**Last Updated**: October 14, 2026
**Implementation**: Complete ✅
**Status**: Production Ready 🚀
