<?php
/**
 * OpenRouter Setup Guide & Test
 * 100% FREE Alternative to Gemini
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

$db = getDB();
$openrouterKey = $db->query("SELECT config_value FROM system_config WHERE config_key='openrouter_api_key'")->fetchColumn();
$hasKey = !empty($openrouterKey);

?>
<!DOCTYPE html>
<html>
<head>
    <title>Setup OpenRouter (FREE)</title>
    <style>
        body { font-family: system-ui; max-width: 1000px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #1a73e8; }
        .section { background: white; border-radius: 12px; padding: 25px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { background: #e8f5e9; border-left: 4px solid #34a853; }
        .info { background: #e8f0fe; border-left: 4px solid #1a73e8; }
        .warning { background: #fef7e0; border-left: 4px solid #fbbc04; }
        .step { background: #f8f9fa; padding: 20px; margin: 15px 0; border-radius: 8px; border-left: 4px solid #1a73e8; }
        .code { background: #2d2d2d; color: #f8f8f2; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; overflow-x: auto; margin: 10px 0; }
        .highlight { background: #fff3cd; padding: 2px 6px; border-radius: 3px; }
        ol { line-height: 1.8; }
        .btn { background: #1a73e8; color: white; padding: 12px 24px; border-radius: 6px; text-decoration: none; display: inline-block; font-weight: bold; margin: 10px 5px; }
        .btn:hover { background: #1557b0; }
        .badge { padding: 6px 12px; border-radius: 12px; font-size: 12px; font-weight: bold; display: inline-block; }
        .badge.free { background: #34a853; color: white; }
        .badge.pro { background: #fbbc04; color: #333; }
    </style>
</head>
<body>
    <h1>🆓 Setup OpenRouter (100% FREE Alternative)</h1>
    
    <!-- Current Status -->
    <div class="section <?php echo $hasKey ? 'success' : 'info'; ?>">
        <h2>📊 Current Status</h2>
        <?php if ($hasKey): ?>
            <p>✅ <strong>OpenRouter API Key is configured!</strong></p>
            <p>Key prefix: <code><?php echo htmlspecialchars(substr($openrouterKey, 0, 20)); ?>...</code></p>
            <p><a href="#test-section" class="btn">Test API Key →</a></p>
        <?php else: ?>
            <p>⚠️ <strong>No OpenRouter API Key found.</strong> Follow the steps below to set it up.</p>
        <?php endif; ?>
    </div>

    <!-- Why OpenRouter? -->
    <div class="section info">
        <h2>💡 Why OpenRouter?</h2>
        <ul>
            <li><span class="badge free">100% FREE</span> - Free credits included, no billing required</li>
            <li>🚀 <strong>Multiple models:</strong> Gemini, GPT, Claude, Llama - all in one API</li>
            <li>⚡ <strong>Fast:</strong> Similar or better performance than direct Gemini</li>
            <li>🔓 <strong>No billing setup:</strong> Just sign up and use</li>
            <li>📈 <strong>Free tier limits:</strong> 
                <ul>
                    <li>10 requests/minute (enough for IPCR usage)</li>
                    <li>200 requests/day free</li>
                    <li>Some models are 100% free forever</li>
                </ul>
            </li>
            <li>✅ <strong>Your code already supports it!</strong> - No major changes needed</li>
        </ul>
    </div>

    <!-- Setup Steps -->
    <div class="section">
        <h2>🔧 Setup Steps (5 minutes)</h2>
        
        <div class="step">
            <h3>Step 1: Create OpenRouter Account</h3>
            <ol>
                <li>Go to <a href="https://openrouter.ai/keys" target="_blank"><strong>https://openrouter.ai/keys</strong></a></li>
                <li>Click <strong>"Sign in with Google"</strong> or create account</li>
                <li>Verify your email</li>
            </ol>
        </div>

        <div class="step">
            <h3>Step 2: Get Your API Key</h3>
            <ol>
                <li>After login, you're automatically on the "Keys" page</li>
                <li>Click <strong>"Create Key"</strong> button</li>
                <li>Give it a name (e.g., "IPCR System")</li>
                <li>Click <strong>"Create"</strong></li>
                <li>Copy the API key (starts with <code>sk-or-v1-...</code>)</li>
            </ol>
            <p><strong>⚠️ Important:</strong> Copy the key immediately - you won't see it again!</p>
        </div>

        <div class="step">
            <h3>Step 3: Add to Your Database</h3>
            <p>Run this SQL query in your phpMyAdmin or database tool:</p>
            <div class="code">
-- Check if key already exists
SELECT * FROM system_config WHERE config_key = 'openrouter_api_key';

-- If no result, INSERT (replace YOUR_KEY_HERE with actual key)
INSERT INTO system_config (config_key, config_value)
VALUES ('openrouter_api_key', 'sk-or-v1-YOUR_KEY_HERE');

-- If key exists, UPDATE instead
UPDATE system_config 
SET config_value = 'sk-or-v1-YOUR_KEY_HERE'
WHERE config_key = 'openrouter_api_key';
            </div>
        </div>

        <div class="step">
            <h3>Step 4: Test It!</h3>
            <p>Scroll down to the <a href="#test-section">Test Section</a> below and click "Test OpenRouter API"</p>
        </div>
    </div>

    <!-- Free Models -->
    <div class="section info">
        <h2>🎁 Free Models Available</h2>
        <p>These models are <strong>100% FREE forever</strong> on OpenRouter:</p>
        <div class="code" style="background: white; color: #333; border: 2px solid #34a853;">
google/gemini-flash-1.5          <span class="badge free">FREE</span> - Fast, good quality
google/gemini-flash-1.5-8b       <span class="badge free">FREE</span> - Faster, lighter
meta-llama/llama-3.2-3b-instruct <span class="badge free">FREE</span> - Very fast
qwen/qwen-2-7b-instruct          <span class="badge free">FREE</span> - Good for text
        </div>
        <p><strong>Recommended:</strong> <code>google/gemini-flash-1.5</code> - Same as Google's Gemini but via OpenRouter!</p>
    </div>

    <!-- Test Section -->
    <div class="section" id="test-section">
        <h2>🧪 Test OpenRouter API</h2>
        
        <?php if ($hasKey): ?>
            <p>Testing your OpenRouter API key...</p>
            
            <?php
            // Test the API key
            $testUrl = "https://openrouter.ai/api/v1/chat/completions";
            
            $testPayload = [
                'model' => 'google/gemini-flash-1.5',
                'messages' => [
                    ['role' => 'user', 'content' => 'Reply with just: OK']
                ]
            ];
            
            $ch = curl_init($testUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($testPayload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Content-Type: application/json',
                    'Authorization: Bearer ' . $openrouterKey,
                    'HTTP-Referer: https://equipment-borrowing.online',
                    'X-Title: IPCR System'
                ],
                CURLOPT_TIMEOUT => 30,
                CURLOPT_SSL_VERIFYPEER => false
            ]);
            
            $startTime = microtime(true);
            $response = curl_exec($ch);
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            if ($curlError) {
                echo '<div class="warning">';
                echo '<h3>⚠️ Network Error</h3>';
                echo '<p>' . htmlspecialchars($curlError) . '</p>';
                echo '</div>';
            } else if ($httpCode === 200) {
                echo '<div class="success">';
                echo '<h3>✅ SUCCESS! OpenRouter is Working!</h3>';
                echo '<p><strong>Response Time:</strong> ' . $duration . 'ms</p>';
                
                $aiResponse = $result['choices'][0]['message']['content'] ?? 'N/A';
                echo '<p><strong>AI Response:</strong> <code>' . htmlspecialchars($aiResponse) . '</code></p>';
                
                echo '<div class="code" style="background: white; color: #333; border: 2px solid #34a853;">';
                echo htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT));
                echo '</div>';
                
                echo '<h4>✅ Next Steps:</h4>';
                echo '<ul>';
                echo '<li>Your OpenRouter is configured correctly!</li>';
                echo '<li>Your IPCR system will now use OpenRouter instead of Gemini</li>';
                echo '<li>Try uploading evidence with AI verification</li>';
                echo '<li>Monitor usage at: <a href="https://openrouter.ai/activity" target="_blank">OpenRouter Dashboard</a></li>';
                echo '</ul>';
                echo '</div>';
                
            } else if ($httpCode === 401) {
                echo '<div class="warning">';
                echo '<h3>❌ Invalid API Key</h3>';
                echo '<p>The API key in your database is invalid or expired.</p>';
                echo '<h4>Solutions:</h4>';
                echo '<ul>';
                echo '<li>Generate a new key at <a href="https://openrouter.ai/keys" target="_blank">OpenRouter Keys</a></li>';
                echo '<li>Make sure you copied the full key (starts with sk-or-v1-)</li>';
                echo '<li>Update the database with the new key</li>';
                echo '</ul>';
                echo '</div>';
                
            } else if ($httpCode === 429) {
                echo '<div class="warning">';
                echo '<h3>⚠️ Rate Limit Exceeded</h3>';
                echo '<p>Too many requests. Wait a minute and try again.</p>';
                echo '<p><strong>Free tier limits:</strong> 10 requests/minute, 200/day</p>';
                echo '</div>';
                
            } else {
                echo '<div class="warning">';
                echo '<h3>⚠️ HTTP ' . $httpCode . ' - Unexpected Response</h3>';
                echo '<p><strong>Error:</strong></p>';
                echo '<div class="code">';
                echo htmlspecialchars(substr($response, 0, 1000));
                echo '</div>';
                echo '</div>';
            }
            ?>
            
        <?php else: ?>
            <div class="warning">
                <p>⚠️ No OpenRouter API key configured yet. Follow the setup steps above first.</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- How it works -->
    <div class="section info">
        <h2>🔄 How Your Code Will Use OpenRouter</h2>
        <p>Your system is already configured to use OpenRouter when available! Here's how it works:</p>
        <ol>
            <li>System checks if <code>openrouter_api_key</code> exists in database</li>
            <li>If YES → Uses OpenRouter (free!)</li>
            <li>If NO → Falls back to Gemini (requires billing)</li>
        </ol>
        <p><strong>Code location:</strong> <code>api/upload-evidence.php</code>, <code>api/ai-verify-evidence.php</code>, <code>api/pre-upload-ai-check.php</code></p>
        <p>✅ No code changes needed - just add the API key!</p>
    </div>

    <!-- Comparison -->
    <div class="section">
        <h2>📊 OpenRouter vs Gemini Direct</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f8f9fa;">
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Feature</th>
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">OpenRouter</th>
                <th style="padding: 12px; text-align: left; border-bottom: 2px solid #dee2e6;">Gemini Direct</th>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Cost</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">✅ <strong>FREE</strong> (with limits)</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">❌ Requires billing</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Free Tier</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">✅ 200 requests/day</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">⚠️ Limited access</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Models</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">✅ Gemini + GPT + Claude + more</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Gemini only</td>
            </tr>
            <tr>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">Setup</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">✅ Sign up, get key, done</td>
                <td style="padding: 12px; border-bottom: 1px solid #dee2e6;">⚠️ Billing setup required</td>
            </tr>
            <tr>
                <td style="padding: 12px;">Speed</td>
                <td style="padding: 12px;">⚡ Fast</td>
                <td style="padding: 12px;">⚡ Fast</td>
            </tr>
        </table>
    </div>

    <div class="section success">
        <h2>🎯 Summary</h2>
        <p><strong>For FREE AI verification, use OpenRouter!</strong></p>
        <ol>
            <li>Sign up at <a href="https://openrouter.ai" target="_blank">OpenRouter.ai</a></li>
            <li>Get API key</li>
            <li>Add to database</li>
            <li>Test on this page</li>
            <li>Done! ✅</li>
        </ol>
        <p><a href="https://openrouter.ai/keys" target="_blank" class="btn">Get OpenRouter API Key →</a></p>
    </div>

</body>
</html>
