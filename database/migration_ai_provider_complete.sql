-- =====================================================
-- AI Provider Selection Migration (Complete)
-- =====================================================
-- Run this ONCE in your online database (phpMyAdmin)
-- Adds support for choosing between Gemini and OpenRouter
-- =====================================================

-- 1. Add AI provider setting (default: gemini for backward compatibility)
INSERT INTO system_config (config_key, config_value) 
VALUES ('ai_provider', 'gemini')
ON DUPLICATE KEY UPDATE config_key=config_key;

-- 2. Add OpenRouter API key field (empty by default)
INSERT INTO system_config (config_key, config_value) 
VALUES ('openrouter_api_key', '')
ON DUPLICATE KEY UPDATE config_key=config_key;

-- 3. Ensure AI verification mode exists (if not already created)
INSERT INTO system_config (config_key, config_value) 
VALUES ('ai_verification_mode', 'advisory')
ON DUPLICATE KEY UPDATE config_key=config_key;

-- 4. Ensure Gemini API key field exists (preserve existing value if any)
INSERT INTO system_config (config_key, config_value) 
VALUES ('gemini_api_key', '')
ON DUPLICATE KEY UPDATE config_key=config_key;

-- =====================================================
-- Verification: Check what was created
-- =====================================================
-- Run this to verify the migration worked:
-- SELECT config_key, 
--        CASE 
--          WHEN config_key LIKE '%api_key%' THEN CONCAT(LEFT(config_value, 20), '...')
--          ELSE config_value 
--        END as config_value
-- FROM system_config 
-- WHERE config_key IN ('ai_provider', 'ai_verification_mode', 'gemini_api_key', 'openrouter_api_key')
-- ORDER BY config_key;

-- =====================================================
-- Quick Setup Examples
-- =====================================================

-- OPTION A: Setup for OpenRouter (100% FREE - Recommended)
-- 1. Get key from: https://openrouter.ai/keys
-- 2. Run this (replace YOUR_KEY with actual key):
-- UPDATE system_config SET config_value = 'openrouter' WHERE config_key = 'ai_provider';
-- UPDATE system_config SET config_value = 'sk-or-v1-YOUR_KEY_HERE' WHERE config_key = 'openrouter_api_key';
-- UPDATE system_config SET config_value = 'advisory' WHERE config_key = 'ai_verification_mode';

-- OPTION B: Setup for Google Gemini (Requires Billing)
-- 1. Get key from: https://aistudio.google.com/app/apikey
-- 2. Enable billing: https://aistudio.google.com
-- 3. Run this (replace YOUR_KEY with actual key):
-- UPDATE system_config SET config_value = 'gemini' WHERE config_key = 'ai_provider';
-- UPDATE system_config SET config_value = 'AIzaYOUR_KEY_HERE' WHERE config_key = 'gemini_api_key';
-- UPDATE system_config SET config_value = 'advisory' WHERE config_key = 'ai_verification_mode';

-- OPTION C: Disable AI completely
-- UPDATE system_config SET config_value = 'disabled' WHERE config_key = 'ai_verification_mode';

-- =====================================================
-- Done! Next Steps:
-- =====================================================
-- 1. Go to your admin panel
-- 2. Navigate to Settings page
-- 3. Scroll to "AI Evidence Verification" section
-- 4. You'll see the new provider selection
-- 5. Choose provider and enter API key
-- 6. Save!
-- =====================================================
