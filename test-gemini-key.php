<?php
/**
 * Gemini API Key Tester v2
 * Tests API key from database
 */

// Get API key from database instead of hardcoding
require_once __DIR__ . '/config/database.php';
$db = getDB();
$apiKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

if (!$apiKey) {
    die('<div style="padding:20px;background:#f8d7da;color:#721c24;border-radius:8px;max-width:600px;margin:40px auto;">
        <h3>⚠️ No API Key Configured</h3>
        <p>Please configure your Gemini API key in the admin panel first:</p>
        <p><strong>Admin → Settings → AI Evidence Verification</strong></p>
    </div>');
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gemini API Test</title>
    <style>
        body { font-family: system-ui; max-width: 900px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #2c5f2d; margin-top: 0; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; border-left: 4px solid #dc3545; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; border-left: 4px solid #ffc107; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 6px; border-left: 4px solid #17a2b8; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 6px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        td:first-child { font-weight: bold; width: 200px; color: #666; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 Gemini API Key Tester v2</h1>
        <p>Testing key: <code><?= substr($apiKey, 0, 20) ?>...</code></p>
    </div>

    <?php
    // Step 1: List available models
    echo '<div class="card"><h2>📋 Step 1: List Available Models</h2>';
    
    $listUrl = "https://generativelanguage.googleapis.com/v1beta/models";
    $headers = [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $apiKey
    ];
    
    $ch = curl_init($listUrl);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode === 200) {
        $result = json_decode($response, true);
        if (isset($result['models'])) {
            echo '<div class="success"><strong>✅ API Key Works!</strong><br>Found ' . count($result['models']) . ' available models</div>';
            
            echo '<table>';
            echo '<tr><th>Model Name</th><th>Supports generateContent</th></tr>';
            
            $workingModels = [];
            foreach ($result['models'] as $model) {
                $name = $model['name'] ?? '';
                $methods = $model['supportedGenerationMethods'] ?? [];
                $supportsGenerate = in_array('generateContent', $methods);
                
                if ($supportsGenerate) {
                    $workingModels[] = $name;
                }
                
                echo '<tr>';
                echo '<td><code>' . htmlspecialchars($name) . '</code></td>';
                echo '<td>' . ($supportsGenerate ? '<span style="color:#28a745">✅ Yes</span>' : '<span style="color:#dc3545">❌ No</span>') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
            
            if (count($workingModels) > 0) {
                echo '<div class="info">';
                echo '<strong>✅ Models you can use:</strong><br>';
                foreach ($workingModels as $m) {
                    echo '<code>' . htmlspecialchars($m) . '</code><br>';
                }
                echo '</div>';
            }
        } else {
            echo '<div class="warning"><strong>⚠️ Unexpected Response</strong><br><pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre></div>';
        }
    } else {
        echo '<div class="error"><strong>❌ API Error (HTTP ' . $httpCode . ')</strong><br><pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre></div>';
    }
    echo '</div>';
    
    // Step 2: Test a working model
    if (!empty($workingModels)) {
        echo '<div class="card"><h2>🧪 Step 2: Test Model</h2>';
        
        // Try the first working model
        $testModel = str_replace('models/', '', $workingModels[0]);
        echo '<p>Testing model: <code>' . htmlspecialchars($testModel) . '</code></p>';
        
        $testUrl = "https://generativelanguage.googleapis.com/v1beta/models/{$testModel}:generateContent";
        $data = [
            'contents' => [
                ['parts' => [['text' => 'Say OK']]]
            ]
        ];
        
        $ch = curl_init($testUrl);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
            
            echo '<div class="success">';
            echo '<strong>✅ Model Works!</strong><br><br>';
            echo 'Request: <code>Say OK</code><br>';
            echo 'Response: <code>' . htmlspecialchars($text) . '</code>';
            echo '</div>';
            
            echo '<div class="info">';
            echo '<strong>✅ Configuration Instructions:</strong><br><br>';
            echo '<ol>';
            echo '<li>Go to Admin → Settings</li>';
            echo '<li>Paste your API key: <code>' . substr($apiKey, 0, 20) . '...</code></li>';
            echo '<li>The system will use model: <code>' . htmlspecialchars($testModel) . '</code></li>';
            echo '<li>Select "Advisory Only" mode</li>';
            echo '<li>Click Save</li>';
            echo '</ol>';
            echo '</div>';
            
        } else {
            echo '<div class="error">';
            echo '<strong>❌ Test Failed (HTTP ' . $httpCode . ')</strong><br>';
            echo '<pre>' . htmlspecialchars(substr($response, 0, 500)) . '</pre>';
            echo '</div>';
        }
        
        echo '</div>';
    }
    ?>

    <div class="card">
        <h2>📖 Next Steps</h2>
        <?php if (!empty($workingModels)): ?>
        <div class="success">
            <strong>✅ Your API key is valid and working!</strong><br><br>
            You can now configure it in your system:
            <ol style="margin: 10px 0;">
                <li>Go to: <strong>Admin → Settings</strong></li>
                <li>Scroll to "AI Evidence Verification" section</li>
                <li>Paste your API key</li>
                <li>Select "Advisory Only" mode</li>
                <li>Click "Save AI Settings"</li>
            </ol>
        </div>
        <?php else: ?>
        <div class="error">
            <strong>❌ API Key Issue</strong><br>
            The key format looks correct but couldn't access models.<br>
            Try creating a new API key at: <a href="https://aistudio.google.com/app/apikey" target="_blank">aistudio.google.com</a>
        </div>
        <?php endif; ?>
    </div>

    <div class="card">
        <p style="text-align: center; color: #666; margin: 0;">
            <strong>🔒 Security:</strong> Delete this file after testing: <code>test-gemini-key.php</code>
        </p>
    </div>
</body>
</html>
