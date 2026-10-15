<?php
/**
 * API Key Tester
 * Tests API key from database
 */

// Get API key from database
require_once __DIR__ . '/config/database.php';
$db = getDB();
$testKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

if (!$testKey) {
    die('<div style="padding:20px;background:#f8d7da;color:#721c24;border-radius:8px;max-width:600px;margin:40px auto;">
        <h3>⚠️ No API Key Found</h3>
        <p>Configure your API key in: <strong>Admin → Settings → AI Evidence Verification</strong></p>
    </div>');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API Key Tester</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 800px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c5f2d;
            margin-top: 0;
        }
        .result {
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            font-weight: 500;
        }
        .success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        .warning {
            background: #fff3cd;
            color: #856404;
            border-left: 4px solid #ffc107;
        }
        .info {
            background: #d1ecf1;
            color: #0c5460;
            border-left: 4px solid #17a2b8;
        }
        code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
        }
        .key-display {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            word-break: break-all;
            font-family: 'Courier New', monospace;
            border: 1px solid #dee2e6;
        }
        .btn {
            background: #2c5f2d;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        .btn:hover {
            background: #1e4620;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        td:first-child {
            font-weight: bold;
            width: 200px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔑 API Key Tester</h1>
        <p>Testing your API key format and functionality...</p>
    </div>

    <div class="card">
        <h2>📋 Your API Key</h2>
        <div class="key-display"><?= htmlspecialchars($testKey) ?></div>
        
        <table style="margin-top: 20px;">
            <tr>
                <td>Length:</td>
                <td><?= strlen($testKey) ?> characters</td>
            </tr>
            <tr>
                <td>Starts with:</td>
                <td><code><?= htmlspecialchars(substr($testKey, 0, 10)) ?>...</code></td>
            </tr>
            <tr>
                <td>Format:</td>
                <td>
                    <?php if ($isOldGeminiFormat): ?>
                        <span style="color: #28a745;">✅ Google Gemini format (Legacy)</span>
                    <?php elseif ($isNewGeminiFormat): ?>
                        <span style="color: #28a745;">✅ Google Gemini format (New OAuth)</span>
                    <?php else: ?>
                        <span style="color: #dc3545;">❌ Unknown format</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <?php
    // Key format validation - Support both old and new Google formats
    $isOldGeminiFormat = strpos($testKey, 'AIza') === 0;
    $isNewGeminiFormat = strpos($testKey, 'AQ.') === 0;
    $isGeminiFormat = $isOldGeminiFormat || $isNewGeminiFormat;
    $keyLength = strlen($testKey);
    ?>

    <div class="card">
        <h2>🔍 Format Analysis</h2>
        
        <?php if (!$isGeminiFormat): ?>
        <div class="result error">
            <strong>❌ Invalid Format Detected</strong><br><br>
            This doesn't appear to be a Google Gemini API key.<br><br>
            <strong>Expected formats:</strong><br>
            - Legacy: <code>AIzaSy...</code> (39 characters)<br>
            - New OAuth: <code>AQ....</code> (50+ characters)<br>
            <strong>Your key starts with:</strong> <code><?= htmlspecialchars(substr($testKey, 0, 6)) ?></code>
        </div>
        
        <div class="result warning">
            <strong>⚠️ Possible Issues:</strong>
            <ul style="margin: 10px 0;">
                <li>You may have copied a token from a different service</li>
                <li>You may have an OAuth/refresh token instead of API key</li>
                <li>The key might be from Azure, OpenAI, or another provider</li>
            </ul>
        </div>
        <?php else: ?>
        <div class="result success">
            <strong>✅ Format looks correct!</strong><br>
            This appears to be a valid Gemini API key format
            <?php if ($isNewGeminiFormat): ?>
            <br><small>(New OAuth-style key format)</small>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <?php if ($isGeminiFormat): ?>
    <div class="card">
        <h2>🧪 Testing API Connection</h2>
        
        <?php
        // Test the API - Support new OAuth format
        $testPrompt = "Reply with just: OK";
        // Use correct model name: gemini-1.5-flash (not gemini-1.5-flash-latest)
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent";
        
        $data = [
            'contents' => [
                ['parts' => [['text' => $testPrompt]]]
            ]
        ];
        
        // Use header authentication (supports both old and new key formats)
        $headers = [
            'Content-Type: application/json',
            'x-goog-api-key: ' . $testKey
        ];
        
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        if ($curlError) {
            echo '<div class="result error">';
            echo '<strong>❌ Connection Error</strong><br><br>';
            echo 'Could not connect to Google API: ' . htmlspecialchars($curlError);
            echo '</div>';
        } elseif ($httpCode === 200) {
            $result = json_decode($response, true);
            if (isset($result['candidates'][0]['content']['parts'][0]['text'])) {
                echo '<div class="result success">';
                echo '<strong>✅ API Key Works!</strong><br><br>';
                echo 'Successfully connected to Google Gemini API.<br>';
                echo 'Response: <code>' . htmlspecialchars($result['candidates'][0]['content']['parts'][0]['text']) . '</code>';
                echo '</div>';
                
                echo '<div class="result info">';
                echo '<strong>✅ Next Steps:</strong><br>';
                echo '<ol style="margin: 10px 0; padding-left: 20px;">';
                echo '<li>Go to Admin → Settings</li>';
                echo '<li>Paste this key in "Google Gemini API Key" field</li>';
                echo '<li>Select "Advisory Only" mode</li>';
                echo '<li>Click Save</li>';
                echo '</ol>';
                echo '<a href="admin/settings.php" class="btn">Go to Settings</a>';
                echo '</div>';
            } else {
                echo '<div class="result warning">';
                echo '<strong>⚠️ Unexpected Response</strong><br><br>';
                echo 'API returned success but response format is unexpected:<br>';
                echo '<code>' . htmlspecialchars(substr($response, 0, 200)) . '...</code>';
                echo '</div>';
            }
        } elseif ($httpCode === 400) {
            $result = json_decode($response, true);
            echo '<div class="result error">';
            echo '<strong>❌ API Key Invalid</strong><br><br>';
            echo 'The API key was rejected by Google.<br>';
            if (isset($result['error']['message'])) {
                echo 'Error: ' . htmlspecialchars($result['error']['message']);
            }
            echo '</div>';
        } elseif ($httpCode === 403) {
            echo '<div class="result error">';
            echo '<strong>❌ API Key Not Authorized</strong><br><br>';
            echo 'The API key exists but is not authorized to use Gemini API.<br>';
            echo 'Make sure you created the key at: <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com</a>';
            echo '</div>';
        } else {
            echo '<div class="result error">';
            echo '<strong>❌ API Error (HTTP ' . $httpCode . ')</strong><br><br>';
            echo 'Response: <code>' . htmlspecialchars(substr($response, 0, 300)) . '</code>';
            echo '</div>';
        }
        ?>
    </div>
    <?php endif; ?>

    <div class="card">
        <h2>📖 How to Get a Valid Gemini API Key</h2>
        <ol style="line-height: 2;">
            <li>Visit: <a href="https://aistudio.google.com/app/apikey" target="_blank">https://aistudio.google.com/app/apikey</a></li>
            <li>Sign in with your Google account</li>
            <li>Click <strong>"Get API Key"</strong> or <strong>"Create API Key"</strong></li>
            <li>Choose <strong>"Create API key in new project"</strong></li>
            <li>Copy the key (it will start with <code>AIza...</code>)</li>
            <li>Paste it in your Admin Settings</li>
        </ol>
        
        <div class="result info" style="margin-top: 20px;">
            <strong>ℹ️ Note:</strong> The key should be exactly 39 characters and start with "AIza"
        </div>
    </div>

    <div class="card">
        <p style="text-align: center; color: #666; margin: 0;">
            <strong>🔒 Security:</strong> Delete this test file after use: <code>test-api-key.php</code>
        </p>
    </div>
</body>
</html>
