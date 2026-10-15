# AI Provider Setup Guide

## Overview

The IPCR system now supports **two AI providers** for evidence verification:

1. **Google Gemini** - Direct from Google (requires billing for full access)
2. **OpenRouter** - 100% FREE alternative with same AI models

## 🆓 Recommended: OpenRouter (FREE)

### Why OpenRouter?

✅ **100% FREE** - No billing required  
✅ **200 requests/day free** - Enough for most IPCR usage  
✅ **Same AI models** - Uses Google Gemini Flash 1.5  
✅ **Easy setup** - 5 minutes, no credit card needed  
✅ **No billing limits** - Free tier doesn't require payment setup  

### Setup Steps (5 minutes)

1. **Create Account**
   - Go to https://openrouter.ai/keys
   - Click "Sign in with Google" (free)
   - Verify your email

2. **Get API Key**
   - After login, you're on the Keys page
   - Click "Create Key"
   - Name it (e.g., "IPCR System")
   - Copy the key (starts with `sk-or-v1-...`)
   - ⚠️ Save it immediately - you won't see it again!

3. **Run Migration**
   ```sql
   -- In phpMyAdmin or your database tool:
   source database/migration_add_ai_provider.sql
   ```

4. **Configure in Admin Panel**
   - Login as Admin
   - Go to **Settings** page
   - Scroll to **AI Evidence Verification** section
   - Select **OpenRouter** as AI Provider
   - Paste your API key in the **OpenRouter API Key** field
   - Select verification mode (Advisory recommended)
   - Click **Save AI Settings**

5. **Test It!**
   - Upload evidence as faculty
   - Check for AI verification results
   - Monitor at: https://openrouter.ai/activity

## Alternative: Google Gemini (Requires Billing)

### When to Use Gemini Direct?

- You already have Google Cloud billing set up
- You need higher rate limits
- You prefer direct Google integration

### Setup Steps

1. **Enable Billing**
   - Go to https://aistudio.google.com
   - Click "Set up billing" on your API key
   - Add payment method (may get $300 free credits)

2. **Get API Key**
   - Go to https://aistudio.google.com/app/apikey
   - Click "Create API Key"
   - Copy the key (starts with `AIza...`)

3. **Configure in Admin Panel**
   - Login as Admin
   - Go to **Settings** page
   - Select **Google Gemini** as AI Provider
   - Paste your API key
   - Click **Save AI Settings**

## AI Verification Modes

### 1. Disabled
- No AI verification
- All uploads allowed
- Use when AI is not configured

### 2. Advisory (Recommended)
- AI checks and provides feedback
- Shows confidence scores and warnings
- **Always allows upload** regardless of score
- Best for most use cases

### 3. Strict
- AI blocks uploads below 50% confidence
- Use only if you need strict validation
- ⚠️ May reject valid files

## Free Tier Limits

### OpenRouter Free Tier
- **10 requests/minute**
- **200 requests/day**
- Some models 100% free forever
- No billing required

### Gemini Free Tier (Without Billing)
- **Very limited** - generateContent often blocked
- **Requires billing** for full access
- Not recommended for production

## Comparison

| Feature | OpenRouter | Gemini Direct |
|---------|-----------|---------------|
| Cost | ✅ **FREE** | ❌ Requires billing |
| Setup Time | ✅ 5 minutes | ⏰ 30+ minutes |
| Free Requests | ✅ 200/day | ⚠️ Very limited |
| AI Model | ✅ Gemini Flash 1.5 | ✅ Gemini models |
| Billing Setup | ✅ Not required | ❌ Required |
| Best For | Most users | Advanced users |

## Troubleshooting

### "No API Key for OPENROUTER" warning
- You selected OpenRouter but haven't added the key yet
- Get key from https://openrouter.ai/keys
- Add it in Settings → OpenRouter API Key field

### "AI verification not configured"
- No API key entered for selected provider
- Check Settings page
- Make sure you saved after entering the key

### "Rate limit exceeded" (OpenRouter)
- Free tier: 10 requests/min, 200/day
- Wait a few minutes and try again
- Consider upgrading if you need more

### Gemini "403 Forbidden" or "404 Not Found"
- Free tier blocked generateContent
- Enable billing at https://aistudio.google.com
- Or switch to OpenRouter (free alternative)

## Files Modified

- `admin/settings.php` - Added provider selection UI
- `api/upload-evidence.php` - Added provider detection
- `api/ai-verify-evidence.php` - Added provider detection  
- `api/pre-upload-ai-check.php` - Added provider detection
- `database/migration_add_ai_provider.sql` - Migration script

## Database Changes

New config keys added:
- `ai_provider` - Selected provider ('gemini' or 'openrouter')
- `openrouter_api_key` - OpenRouter API key (if using OpenRouter)

Existing keys preserved:
- `gemini_api_key` - Gemini API key (if using Gemini)
- `ai_verification_mode` - Verification mode setting

## Support

For issues or questions:
1. Check error logs in hosting cPanel
2. Visit OpenRouter docs: https://openrouter.ai/docs
3. Visit Gemini docs: https://ai.google.dev/docs
