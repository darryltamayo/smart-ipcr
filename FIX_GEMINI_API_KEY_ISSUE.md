# 🔧 Fix: Gemini API Key Issue (HTTP 404)

## ❌ Current Problem

**Error**: `models/gemini-pro is not found for API version v1`

**Root Cause**: Your API key works for **listing models** (test-gemini-key.php ✅) but **NOT for generating content** (upload ❌).

This means your API key is:
- ✅ Valid format
- ✅ Can authenticate
- ❌ **Cannot access generateContent endpoint**

---

## 🎯 Quick Solution (Recommended)

### Option 1: Disable AI Verification (Immediate)

Until you get the proper API key, disable AI to avoid confusion:

1. Go to: **Admin → Settings**
2. Scroll to "**AI Evidence Verification**"
3. Select: **"Disabled"** mode
4. Click "**Save AI Settings**"

**Result**: Uploads will work normally without AI checks.

---

### Option 2: Get Proper API Key (Permanent Fix)

Your current API key is likely from **AI Studio** (free tier) which has limitations.

#### Steps to Get Working API Key:

#### **Step 1: Go to Google AI Studio**
Visit: https://aistudio.google.com/app/apikey

#### **Step 2: Create NEW API Key**
- Click "**Create API Key**"
- Select "**Create API key in new project**"
- Copy the new key

#### **Step 3: Test the New Key**
Before using in system:
1. Visit: https://equipment-borrowing.online/test-gemini-upload.php
2. Temporarily update key in Admin Settings
3. Refresh test page
4. Should show **HTTP 200 ✅**

#### **Step 4: Update in Admin Settings**
1. Go to: **Admin → Settings**
2. Paste new API key
3. Set mode to "**Advisory**"
4. Click "**Save AI Settings**"

---

## 🔍 Why This Happens

### API Key Types:

| Type | Can List Models | Can Generate Content |
|------|----------------|---------------------|
| **AI Studio (Old)** | ✅ Yes | ❌ No (HTTP 404) |
| **AI Studio (New)** | ✅ Yes | ✅ Yes |
| **Cloud Console** | ✅ Yes | ✅ Yes (with API enabled) |

Your key appears to be an **old AI Studio key** or one without proper permissions.

---

## 🧪 Diagnostic Checklist

Run these tests to confirm:

### Test 1: List Models (Currently ✅ Working)
https://equipment-borrowing.online/test-gemini-key.php

**Expected**: ✅ Shows 50 available models

### Test 2: Generate Content (Currently ❌ Failing)
https://equipment-borrowing.online/test-gemini-upload.php

**Expected**: ✅ HTTP 200 with JSON response
**Actual**: ❌ HTTP 404 "model not found"

**Diagnosis**: Key works for auth but not for content generation.

---

## 💡 Alternative: Use Different Model

Some API keys only work with specific model names. Try these in order:

### Try 1: gemini-pro (Currently failing)
```
https://generativelanguage.googleapis.com/v1/models/gemini-pro:generateContent
```

### Try 2: gemini-1.0-pro
```
https://generativelanguage.googleapis.com/v1/models/gemini-1.0-pro:generateContent
```

### Try 3: text-bison (PaLM API - older but more compatible)
```
https://generativelanguage.googleapis.com/v1/models/text-bison:generateText
```

---

## 🚀 Recommended Action Plan

### Immediate (5 minutes):
1. ✅ **Disable AI mode** in Admin Settings
2. ✅ Test upload - should work without AI
3. ✅ System is functional

### Short-term (30 minutes):
1. Create new API key from AI Studio
2. Test with test-gemini-upload.php
3. If works → Update in Admin Settings
4. Enable Advisory mode

### Long-term (Optional):
1. Set up Google Cloud Console project
2. Enable Generative Language API
3. Create Cloud API key
4. Upgrade to paid tier if needed (for higher quota)

---

## 📝 Current System Behavior

With the latest code update:

### If AI Fails (HTTP 404):
- ✅ **Upload still succeeds**
- ℹ️ Shows message: "AI model not available with current API key"
- 📁 File uploaded successfully
- 👤 Will be reviewed manually by evaluator

### No Impact on Users:
- ✅ Faculty can upload evidence
- ✅ Files are stored correctly
- ✅ Dean can review manually
- ✅ System functions normally

**Only missing feature**: AI confidence scores and automatic verification

---

## 🎯 Summary

**Current Status**: 
- ❌ AI verification not working (API key limitation)
- ✅ File uploads working (with fallback)
- ✅ System functional

**Recommended**:
1. **Disable AI mode** for now (clean user experience)
2. **Get new API key** from AI Studio when convenient
3. **Enable AI later** when key is working

**No urgency** - system works fine without AI. AI is just a nice-to-have feature for automatic verification.

---

## 📞 Need Help?

If you want to enable AI verification:
1. Create new key at: https://aistudio.google.com/app/apikey
2. Test it at: https://equipment-borrowing.online/test-gemini-upload.php
3. If HTTP 200 → Update in Admin Settings
4. If still fails → Share screenshot of error

---

**Created**: October 14, 2026
**Issue**: API Key Type Incompatibility
**Severity**: Low (system works, AI unavailable)
**Fix Time**: 5 minutes (disable) or 30 minutes (new key)
