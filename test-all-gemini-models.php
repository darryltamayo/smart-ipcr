<?php
/**
 * Test ALL Gemini Models - Find which one works!
 */
require_once __DIR__ . '/config/database.php';

$db = getDB();
$apiKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

if (!$apiKey) {
    die("No API key configured");
}

header('Content-Type: text/html; charset=utf-8');

// List of models to test
$modelsToTest = [
    'gemini-1.5-flash' => 'Gemini 1.5 Flash (Recommended)',
    'gemini-1.5-pro' => 'Gemini 1.5 Pro (More powerful)',
    'gemini-1.5-flash-latest' => 'Gemini 1.5 Flash Latest (Old)',
    'gemini-flash-latest' => 'Gemini Flash Latest (Old)',
    'gemini-pro' => 'Gemini Pro (Legacy)',
    'gemini-1.0-pro' => 'Gemini 1.0 Pro',
    'gemini-pro-vision' => 'Gemini Pro Vision',
];

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test All Gemini Models</title>
    <style>
        body { 
            font-family: system-ui; 
            max-width: 1200px; 
            margin: 40px auto; 
            padding: 20px;
            background: #f5f5f5;
        }
        h1 { color: #1a73e8; }
        .model-card {
            background: white;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            padding: 20px;
            margin: 20px 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .model-card.success {
            border-color: #34a853;
            background: #e8f5e9;
        }
        .model-card.error {
            border-color: #ea4335;
            background: #fce8e6;
        }
        .model-card.testing {
            border-color: #fbbc04;
            background: #fef7e0;
        }
        .model-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .model-name {
            font-size: 18px;
            font-weight: bold;
            color: #202124;
        }
        .badge {
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .badge.success { background: #34a853; color: white; }
        .badge.error { background: #ea4335; color: white; }
        .badge.testing { background: #fbbc04; color: #333; }
        .badge.pending { background: #9aa0a6; color: white; }
        .details {
            font-size: 14px;
            color: #5f6368;
            margin-top: 10px;
        }
        .code {
            background: #f8f9fa;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            padding: 12px;
            font-family: 'Courier New', monospace;
            font-size: 13px;
            overflow-x: auto;
            margin-top: 10px;
        }
        .response {
            max-height: 200px;
            overflow-y: auto;
            margin-top: 10px;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .summary {
            background: #e8f0fe;
            border: 2px solid #1a73e8;
            border-radius: 12px;
            padding: 20px;
            margin: 30px 0;
        }
        .summary h2 { 
            margin-top: 0; 
            color: #1a73e8;
        }
        .working-models {
            background: #e8f5e9;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .working-models h3 {
            margin-top: 0;
            color: #34a853;
        }
        .working-models code {
            background: white;
            padding: 2px 8px;
            border-radius: 4px;
            border: 1px solid #34a853;
            display: inline-block;
            margin: 5px;
        }
        .progress {
            width: 100%;
            height: 8px;
            background: #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin: 20px 0;
        }
        .progress-bar {
            height: 100%;
            background: #1a73e8;
            transition: width 0.3s;
        }
    </style>
</head>
<body>
    <h1>🧪 Test All Gemini Models</h1>
    <p>Testing <strong><?php echo count($modelsToTest); ?></strong> different model names to find which ones work with your API key...</p>
    
    <div class="progress">
        <div class="progress-bar" style="width: 0%;" id="progressBar"></div>
    </div>
    
    <div id="results">
        <?php
        $workingModels = [];
        $totalModels = count($modelsToTest);
        $currentIndex = 0;
        
        foreach ($modelsToTest as $modelName => $modelDesc) {
            $currentIndex++;
            $progressPercent = ($currentIndex / $totalModels) * 100;
            
            echo "<script>document.getElementById('progressBar').style.width = '{$progressPercent}%';</script>";
            
            echo '<div class="model-card testing" id="model-' . htmlspecialchars($modelName) . '">';
            echo '<div class="model-header">';
            echo '<div class="model-name">' . htmlspecialchars($modelDesc) . '</div>';
            echo '<span class="badge testing">Testing...</span>';
            echo '</div>';
            echo '<div class="code">' . htmlspecialchars($modelName) . '</div>';
            echo '</div>';
            
            flush();
            ob_flush();
            
            // Test this model
            $url = "https://generativelanguage.googleapis.com/v1beta/models/{$modelName}:generateContent?key=" . $apiKey;
            
            $payload = [
                'contents' => [[
                    'parts' => [[
                        'text' => 'Reply with just: OK'
                    ]]
                ]],
                'generationConfig' => [
                    'temperature' => 0.1,
                    'maxOutputTokens' => 50
                ]
            ];
            
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
                CURLOPT_TIMEOUT => 15,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            
            $startTime = microtime(true);
            $response = curl_exec($ch);
            $endTime = microtime(true);
            $duration = round(($endTime - $startTime) * 1000, 2);
            
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);
            
            $result = json_decode($response, true);
            
            // Update the card
            echo "<script>";
            echo "var card = document.getElementById('model-" . htmlspecialchars($modelName) . "');";
            
            if ($httpCode === 200) {
                // SUCCESS!
                $workingModels[] = [
                    'name' => $modelName,
                    'desc' => $modelDesc,
                    'duration' => $duration
                ];
                
                echo "card.className = 'model-card success';";
                echo "card.querySelector('.badge').className = 'badge success';";
                echo "card.querySelector('.badge').textContent = '✓ WORKS';";
                
                $responseText = $result['candidates'][0]['content']['parts'][0]['text'] ?? 'N/A';
                
                $details = "<div class='details'>";
                $details .= "<strong>✅ SUCCESS!</strong><br>";
                $details .= "HTTP Code: {$httpCode}<br>";
                $details .= "Response Time: {$duration}ms<br>";
                $details .= "AI Response: <code>" . htmlspecialchars($responseText) . "</code>";
                $details .= "</div>";
                
                echo "card.innerHTML += " . json_encode($details) . ";";
            } else {
                // FAILED
                echo "card.className = 'model-card error';";
                echo "card.querySelector('.badge').className = 'badge error';";
                echo "card.querySelector('.badge').textContent = '✗ FAILED';";
                
                $errorMsg = $result['error']['message'] ?? 'Unknown error';
                $errorCode = $result['error']['code'] ?? $httpCode;
                
                $details = "<div class='details'>";
                $details .= "<strong>❌ FAILED</strong><br>";
                $details .= "HTTP Code: {$httpCode}<br>";
                $details .= "Error Code: {$errorCode}<br>";
                $details .= "Error: " . htmlspecialchars(substr($errorMsg, 0, 200)) . "<br>";
                
                if ($curlError) {
                    $details .= "cURL Error: " . htmlspecialchars($curlError);
                }
                
                $details .= "</div>";
                
                if (strlen($errorMsg) > 200) {
                    $details .= "<div class='response code'>" . htmlspecialchars($errorMsg) . "</div>";
                }
                
                echo "card.innerHTML += " . json_encode($details) . ";";
            }
            
            echo "</script>";
            
            flush();
            ob_flush();
            
            // Small delay to avoid rate limiting
            usleep(500000); // 0.5 seconds
        }
        ?>
    </div>
    
    <div class="summary">
        <h2>📊 Test Results Summary</h2>
        
        <?php if (count($workingModels) > 0): ?>
            <div class="working-models">
                <h3>✅ Working Models (<?php echo count($workingModels); ?>)</h3>
                <p>These models work with your API key:</p>
                <?php foreach ($workingModels as $model): ?>
                    <div style="margin: 10px 0;">
                        <code><?php echo htmlspecialchars($model['name']); ?></code>
                        <span style="color: #5f6368; font-size: 13px;">
                            - <?php echo htmlspecialchars($model['desc']); ?>
                            (<?php echo $model['duration']; ?>ms)
                        </span>
                    </div>
                <?php endforeach; ?>
                
                <hr style="margin: 20px 0; border: none; border-top: 1px solid #34a853;">
                
                <h3>💡 Recommended Model to Use</h3>
                <p>Update your code to use:</p>
                <div class="code" style="background: white; border: 2px solid #34a853; font-size: 14px; font-weight: bold;">
                    $url = "https://generativelanguage.googleapis.com/v1beta/models/<?php echo htmlspecialchars($workingModels[0]['name']); ?>:generateContent?key=" . $apiKey;
                </div>
                
                <h3>📁 Files to Update</h3>
                <ul style="font-size: 14px;">
                    <li><code>api/upload-evidence.php</code></li>
                    <li><code>api/ai-verify-evidence.php</code></li>
                    <li><code>api/pre-upload-ai-check.php</code></li>
                    <li><code>test-gemini-upload.php</code></li>
                    <li><code>test-api-key.php</code></li>
                </ul>
            </div>
        <?php else: ?>
            <div style="background: #fce8e6; padding: 15px; border-radius: 8px;">
                <h3 style="color: #ea4335; margin-top: 0;">❌ No Working Models Found</h3>
                <p><strong>Possible issues:</strong></p>
                <ul>
                    <li>API key is invalid or expired</li>
                    <li>API key doesn't have permission for generateContent</li>
                    <li>Gemini API is not enabled in your Google Cloud project</li>
                    <li>You've hit rate limits / quota exceeded</li>
                    <li>Billing is not set up (if required)</li>
                </ul>
                
                <p><strong>Next steps:</strong></p>
                <ol>
                    <li>Go to <a href="https://aistudio.google.com/app/apikey" target="_blank">Google AI Studio</a></li>
                    <li>Verify your API key is valid</li>
                    <li>Check that "Gemini API" is enabled</li>
                    <li>Try generating a new API key</li>
                </ol>
            </div>
        <?php endif; ?>
        
        <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 8px;">
            <h3 style="margin-top: 0;">ℹ️ About Model Names</h3>
            <ul style="font-size: 14px; color: #5f6368;">
                <li><strong>gemini-1.5-flash</strong> - Fast, efficient, recommended for most tasks</li>
                <li><strong>gemini-1.5-pro</strong> - More powerful, better for complex tasks</li>
                <li><strong>*-latest</strong> variants - Deprecated, may not work</li>
            </ul>
        </div>
    </div>
    
    <script>
        console.log('Test completed!');
        console.log('Working models:', <?php echo json_encode($workingModels); ?>);
    </script>
</body>
</html>
