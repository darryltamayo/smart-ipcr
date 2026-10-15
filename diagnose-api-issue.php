<?php
/**
 * Comprehensive API Diagnostics
 * Checks why Gemini API is not working
 */
require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');

$db = getDB();
$apiKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

?>
<!DOCTYPE html>
<html>
<head>
    <title>API Diagnostics</title>
    <style>
        body { font-family: system-ui; max-width: 1000px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        h1 { color: #1a73e8; }
        .section { background: white; border-radius: 12px; padding: 25px; margin: 20px 0; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        .success { background: #e8f5e9; border-left: 4px solid #34a853; }
        .error { background: #fce8e6; border-left: 4px solid #ea4335; }
        .warning { background: #fef7e0; border-left: 4px solid #fbbc04; }
        .info { background: #e8f0fe; border-left: 4px solid #1a73e8; }
        .code { background: #f8f9fa; padding: 15px; border-radius: 6px; font-family: monospace; font-size: 13px; overflow-x: auto; }
        h3 { margin-top: 0; }
        .check-item { margin: 15px 0; padding: 15px; background: #f8f9fa; border-radius: 6px; }
        .check-item strong { display: block; margin-bottom: 5px; }
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 12px; font-weight: bold; display: inline-block; margin-left: 10px; }
        .badge.ok { background: #34a853; color: white; }
        .badge.fail { background: #ea4335; color: white; }
        .badge.warn { background: #fbbc04; color: #333; }
    </style>
</head>
<body>
    <h1>🔍 API Diagnostics</h1>
    <p>Comprehensive check to find why Gemini API is not working...</p>

    <!-- Check 1: API Key exists -->
    <div class="section <?php echo $apiKey ? 'success' : 'error'; ?>">
        <h3>1️⃣ API Key Configuration <?php echo $apiKey ? '<span class="badge ok">✓ OK</span>' : '<span class="badge fail">✗ MISSING</span>'; ?></h3>
        <?php if ($apiKey): ?>
            <p>✅ API Key found in database</p>
            <p><strong>Key prefix:</strong> <code><?php echo htmlspecialchars(substr($apiKey, 0, 20)); ?>...</code></p>
            <p><strong>Key length:</strong> <?php echo strlen($apiKey); ?> characters</p>
        <?php else: ?>
            <p>❌ No API key configured in system_config table</p>
        <?php endif; ?>
    </div>

    <?php if ($apiKey): ?>
    
    <!-- Check 2: Can we reach Google API? -->
    <div class="section info">
        <h3>2️⃣ Network Connectivity Test</h3>
        <p>Testing if server can reach Google Gemini API...</p>
        
        <?php
        $testUrl = "https://generativelanguage.googleapis.com/v1beta/models?key=" . $apiKey;
        
        $ch = curl_init($testUrl);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10,
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_HTTPHEADER => ['Content-Type: application/json']
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            echo '<div class="error">❌ <strong>Network Error:</strong> ' . htmlspecialchars($curlError) . '</div>';
        } else {
            echo '<div class="success">✅ <strong>Network OK:</strong> Server can reach Google API (HTTP ' . $httpCode . ')</div>';
        }
        ?>
    </div>

    <!-- Check 3: Test Model List API -->
    <div class="section">
        <h3>3️⃣ API Key Permissions Test</h3>
        <p>Testing if API key can list available models...</p>
        
        <?php
        $result = json_decode($response, true);
        
        if ($httpCode === 200 && isset($result['models'])) {
            echo '<div class="success">';
            echo '<p>✅ <strong>API Key is VALID!</strong> Can access model list.</p>';
            echo '<p><strong>Available models:</strong></p>';
            echo '<div class="code"><pre>';
            foreach ($result['models'] as $model) {
                $modelName = $model['name'] ?? 'Unknown';
                $displayName = $model['displayName'] ?? '';
                echo htmlspecialchars($modelName) . ' - ' . htmlspecialchars($displayName) . "\n";
            }
            echo '</pre></div>';
            echo '</div>';
            
            // Check if generateContent is supported
            $hasGenerateContent = false;
            foreach ($result['models'] as $model) {
                if (isset($model['supportedGenerationMethods']) && 
                    in_array('generateContent', $model['supportedGenerationMethods'])) {
                    $hasGenerateContent = true;
                    break;
                }
            }
            
            if ($hasGenerateContent) {
                echo '<div class="success">✅ <strong>generateContent method is supported!</strong></div>';
            } else {
                echo '<div class="error">❌ <strong>generateContent method NOT supported</strong> - This might be why it\'s failing!</div>';
            }
            
        } else if ($httpCode === 403) {
            echo '<div class="error">';
            echo '<h4>❌ HTTP 403 - Permission Denied</h4>';
            $errorMsg = $result['error']['message'] ?? 'Unknown error';
            echo '<p><strong>Error:</strong> ' . htmlspecialchars($errorMsg) . '</p>';
            echo '<h4>🔧 Solutions:</h4>';
            echo '<ul>';
            echo '<li><strong>Enable Billing:</strong> Free tier has limited access. Go to <a href="https://aistudio.google.com" target="_blank">Google AI Studio</a> → Set up billing</li>';
            echo '<li><strong>API Restrictions:</strong> Check if API key has restrictions in Google Cloud Console</li>';
            echo '<li><strong>Project Issues:</strong> Make sure Gemini API is enabled for your project</li>';
            echo '</ul>';
            echo '</div>';
            
        } else if ($httpCode === 429) {
            echo '<div class="warning">';
            echo '<h4>⚠️ HTTP 429 - Rate Limit Exceeded</h4>';
            echo '<p>Too many requests. Wait a few minutes and try again.</p>';
            echo '</div>';
            
        } else if ($httpCode === 400) {
            echo '<div class="error">';
            echo '<h4>❌ HTTP 400 - Bad Request</h4>';
            $errorMsg = $result['error']['message'] ?? 'Unknown error';
            echo '<p>' . htmlspecialchars($errorMsg) . '</p>';
            echo '</div>';
            
        } else {
            echo '<div class="error">';
            echo '<h4>❌ HTTP ' . $httpCode . ' - Unexpected Error</h4>';
            echo '<div class="code"><pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre></div>';
            echo '</div>';
        }
        ?>
    </div>

    <!-- Check 4: Test generateContent specifically -->
    <?php if ($httpCode === 200): ?>
    <div class="section">
        <h3>4️⃣ Generate Content Test</h3>
        <p>Testing actual content generation (the failing endpoint)...</p>
        
        <?php
        $testModels = ['gemini-1.5-flash', 'gemini-1.5-pro', 'gemini-pro'];
        
        foreach ($testModels as $testModel) {
            $genUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$testModel}:generateContent?key=" . $apiKey;
            
            $payload = [
                'contents' => [[
                    'parts' => [['text' => 'Say: OK']]
                ]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 10
                ]
            ];
            
            $ch = curl_init($genUrl);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json']
            ]);
            
            $genResponse = curl_exec($ch);
            $genCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            $genResult = json_decode($genResponse, true);
            
            echo '<div class="check-item">';
            echo '<strong>Testing: ' . htmlspecialchars($testModel) . '</strong>';
            
            if ($genCode === 200) {
                echo ' <span class="badge ok">✓ WORKS</span>';
                $text = $genResult['candidates'][0]['content']['parts'][0]['text'] ?? 'N/A';
                echo '<p>✅ Success! Response: <code>' . htmlspecialchars($text) . '</code></p>';
            } else if ($genCode === 403) {
                echo ' <span class="badge fail">✗ BLOCKED</span>';
                $errorMsg = $genResult['error']['message'] ?? 'Permission denied';
                echo '<p>❌ ' . htmlspecialchars($errorMsg) . '</p>';
                echo '<p><strong>Reason:</strong> Likely FREE TIER limitation - generateContent requires billing</p>';
            } else if ($genCode === 404) {
                echo ' <span class="badge warn">Model not found</span>';
            } else {
                echo ' <span class="badge fail">✗ ERROR ' . $genCode . '</span>';
                $errorMsg = $genResult['error']['message'] ?? 'Unknown';
                echo '<p>' . htmlspecialchars(substr($errorMsg, 0, 150)) . '</p>';
            }
            
            echo '</div>';
            
            usleep(300000); // 0.3s delay
        }
        ?>
    </div>
    <?php endif; ?>

    <!-- Final Recommendations -->
    <div class="section info">
        <h3>💡 Recommendations</h3>
        
        <?php if ($httpCode === 403 || ($httpCode === 200 && !$hasGenerateContent)): ?>
            <div class="warning" style="margin: 15px 0;">
                <h4>⚠️ FREE TIER LIMITATION DETECTED</h4>
                <p>Your API key works but <strong>generateContent</strong> is blocked on free tier.</p>
                
                <h4>✅ Solution 1: Enable Billing (Recommended)</h4>
                <ol>
                    <li>Go to <a href="https://aistudio.google.com" target="_blank">Google AI Studio</a></li>
                    <li>Click <strong>"Set up billing"</strong> on your API key</li>
                    <li>Add payment method (may get $300 free credits)</li>
                    <li>Enable billing for your project</li>
                    <li>Wait 2-5 minutes for changes to propagate</li>
                </ol>
                
                <h4>✅ Solution 2: Use OpenRouter (Alternative)</h4>
                <p>OpenRouter provides access to multiple AI models with free tier:</p>
                <ol>
                    <li>Go to <a href="https://openrouter.ai" target="_blank">OpenRouter.ai</a></li>
                    <li>Sign up and get API key (has free tier)</li>
                    <li>Add to your system config:
                        <div class="code">INSERT INTO system_config (config_key, config_value) 
VALUES ('openrouter_api_key', 'YOUR_OPENROUTER_KEY');</div>
                    </li>
                    <li>Your code already supports OpenRouter!</li>
                </ol>
            </div>
            
        <?php elseif ($httpCode === 200): ?>
            <div class="success">
                <p>✅ Your API key seems to be working! If you're still having issues:</p>
                <ul>
                    <li>Check server error logs for detailed messages</li>
                    <li>Verify the exact model name being used in your code</li>
                    <li>Make sure request payload format is correct</li>
                </ul>
            </div>
            
        <?php else: ?>
            <div class="error">
                <p>❌ Your API key has issues. Try these steps:</p>
                <ol>
                    <li>Generate a new API key at <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
                    <li>Make sure to select a project with Gemini API enabled</li>
                    <li>Set up billing if required</li>
                    <li>Update the key in your database</li>
                </ol>
            </div>
        <?php endif; ?>
    </div>

    <?php endif; // if $apiKey ?>

</body>
</html>
