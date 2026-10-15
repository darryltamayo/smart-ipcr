-- Migration: Add AI Provider Selection
-- Adds support for choosing between Gemini and OpenRouter

-- Add ai_provider config (default to 'gemini' for backward compatibility)
INSERT INTO system_config (config_key, config_value) 
VALUES ('ai_provider', 'gemini')
ON DUPLICATE KEY UPDATE config_key=config_key;

-- Add openrouter_api_key config (empty by default)
INSERT INTO system_config (config_key, config_value) 
VALUES ('openrouter_api_key', '')
ON DUPLICATE KEY UPDATE config_key=config_key;

-- Note: Existing gemini_api_key will be preserved
