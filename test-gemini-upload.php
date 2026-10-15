<?php
/**
 * Test Gemini API - Actual Content Generation (same as upload)
 */
require_once __DIR__ . '/config/database.php';

$db = getDB();
$apiKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

if (!$apiKey) {
    die("No API key configured");
}

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Gemini Upload Test</title>
    <style>
        body { font-family: system-ui; max-width: 900px; margin: 40px auto; padding: 20px; }
        .box { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 20px; margin: 20px 0; }
        .success { background: #d1e7dd; border-color: #badbcc; color: #0f5132; }
        .error { background: #f8d7da; border-color: #f5c2c7; color: #842029; }
        .code { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: monospace; font-size: 13px; }
        pre { margin: 0; white-space: pre-wrap; word-wrap: break-word; }
    </style>
</head>
<body>
    <h1>🧪 Gemini Upload Test</h1>
    <p>Testing the EXACT same API call used during file upload...</p>

    <?php
    // Use v1beta with correct model name (gemini-1.5-flash, not gemini-1.5-flash-latest)
    $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $apiKey;
    
    $testPrompt = "Verify evidence file for IPCR:\n\n";
    $testPrompt .= "File: test-document.pdf\n";
    $testPrompt .= "Period: January-June 2026\n";
    $testPrompt .= "For: KRA 1.1 - Prepare course syllabus\n";
    $testPrompt .= "Expected Evidence: Course Syllabus\n\n";
    $testPrompt .= "VERIFICATION RULES:\n";
    $testPrompt .= "- If filename matches = 85% confidence (VALID)\n";
    $testPrompt .= "- If related but not exact = 60-75% (UNCERTAIN)\n";
    $testPrompt .= "- If wrong = <40% (INVALID)\n\n";
    $testPrompt .= 'Respond in JSON: {"verdict":"valid|uncertain|invalid","confidence":0-100,"document_type":"type","content_summary":"summary","reason":"explanation","suggestions":"recommendations"}';
    
    $payload = [
        'contents' => [[
            'parts' => [[
                'text' => $testPrompt
            ]]
        ]],
        'generationConfig' => [
            'temperature' => 0.1,
            'maxOutputTokens' => 512
        ]
    ];
    
    echo '<div class="box">';
    echo '<h3>📤 Request Details</h3>';
    echo '<p><strong>Endpoint:</strong> ' . htmlspecialchars($url) . '</p>';
    echo '<p><strong>Method:</strong> POST</p>';
    echo '<p><strong>Payload:</strong></p>';
    echo '<div class="code"><pre>' . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . '</pre></div>';
    echo '</div>';
    
    // Make the request
    $ch = curl_init($url);
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($payload),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_TIMEOUT => 25,
        CURLOPT_SSL_VERIFYPEER => false,
    ]);
    
    $startTime = microtime(true);
    $response = curl_exec($ch);
    $endTime = microtime(true);
    $duration = round(($endTime - $startTime) * 1000, 2);
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);
    
    // Display results
    echo '<div class="box">';
    echo '<h3>📥 Response Details</h3>';
    echo '<p><strong>HTTP Code:</strong> ' . $httpCode . '</p>';
    echo '<p><strong>Duration:</strong> ' . $duration . ' ms</p>';
    
    if ($curlError) {
        echo '<p><strong>cURL Error:</strong> <span style="color: #dc2626;">' . htmlspecialchars($curlError) . '</span></p>';
    }
    echo '</div>';
    
    if ($httpCode === 200) {
        echo '<div class="box success">';
        echo '<h3>✅ SUCCESS - API Call Worked!</h3>';
        
        $result = json_decode($response, true);
        
        echo '<p><strong>Raw Response:</strong></p>';
        echo '<div class="code"><pre>' . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . '</pre></div>';
        
        // Try to extract the text
        $text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        
        if ($text) {
            echo '<p><strong>Generated Text:</strong></p>';
            echo '<div class="code"><pre>' . htmlspecialchars($text) . '</pre></div>';
            
            // Try to parse as JSON
            $text = preg_replace('/```json\s*/i', '', $text);
            $text = preg_replace('/```\s*/i', '', trim($text));
            $aiResult = json_decode($text, true);
            
            if ($aiResult && isset($aiResult['verdict'])) {
                echo '<p><strong>✅ Parsed AI Result:</strong></p>';
                echo '<div class="code"><pre>' . htmlspecialchars(json_encode($aiResult, JSON_PRETTY_PRINT)) . '</pre></div>';
                
                echo '<h4>📊 AI Verdict:</h4>';
                echo '<ul>';
                echo '<li><strong>Verdict:</strong> ' . htmlspecialchars($aiResult['verdict']) . '</li>';
                echo '<li><strong>Confidence:</strong> ' . ($aiResult['confidence'] ?? 'N/A') . '%</li>';
                echo '<li><strong>Document Type:</strong> ' . htmlspecialchars($aiResult['document_type'] ?? 'N/A') . '</li>';
                echo '<li><strong>Reason:</strong> ' . htmlspecialchars($aiResult['reason'] ?? 'N/A') . '</li>';
                echo '</ul>';
            } else {
                echo '<p style="color: #dc2626;"><strong>⚠️ Could not parse AI result as JSON</strong></p>';
            }
        }
        
        echo '</div>';
    } else if ($httpCode === 429) {
        echo '<div class="box error">';
        echo '<h3>❌ HTTP 429 - Rate Limit / Quota Exceeded</h3>';
        
        $errorResult = json_decode($response, true);
        
        echo '<p><strong>Error Response:</strong></p>';
        echo '<div class="code"><pre>' . htmlspecialchars(json_encode($errorResult, JSON_PRETTY_PRINT)) . '</pre></div>';
        
        $errorMessage = $errorResult['error']['message'] ?? 'Unknown error';
        echo '<p><strong>Error Message:</strong> ' . htmlspecialchars($errorMessage) . '</p>';
        
        echo '<h4>💡 Possible Solutions:</h4>';
        echo '<ul>';
        echo '<li>Wait a few minutes and try again (rate limiting resets)</li>';
        echo '<li>Check your Google Cloud Console quota dashboard</li>';
        echo '<li>Upgrade to a paid tier if using free tier</li>';
        echo '<li>The API key test works because it uses a different endpoint (LIST vs GENERATE)</li>';
        echo '</ul>';
        echo '</div>';
    } else if ($httpCode === 400) {
        echo '<div class="box error">';
        echo '<h3>❌ HTTP 400 - Bad Request</h3>';
        
        $errorResult = json_decode($response, true);
        
        echo '<p><strong>Error Response:</strong></p>';
        echo '<div class="code"><pre>' . htmlspecialchars(json_encode($errorResult, JSON_PRETTY_PRINT)) . '</pre></div>';
        
        $errorMessage = $errorResult['error']['message'] ?? 'Unknown error';
        echo '<p><strong>Error Message:</strong> ' . htmlspecialchars($errorMessage) . '</p>';
        
        echo '<h4>💡 Possible Causes:</h4>';
        echo '<ul>';
        echo '<li>Invalid request format</li>';
        echo '<li>API key doesn\'t have permission for this endpoint</li>';
        echo '<li>Model name is incorrect</li>';
        echo '<li>Payload structure is wrong</li>';
        echo '</ul>';
        echo '</div>';
    } else if ($httpCode === 403) {
        echo '<div class="box error">';
        echo '<h3>❌ HTTP 403 - Forbidden</h3>';
        
        $errorResult = json_decode($response, true);
        
        echo '<p><strong>Error Response:</strong></p>';
        echo '<div class="code"><pre>' . htmlspecialchars(json_encode($errorResult, JSON_PRETTY_PRINT)) . '</pre></div>';
        
        echo '<h4>💡 Possible Causes:</h4>';
        echo '<ul>';
        echo '<li>API key doesn\'t have permission for generateContent</li>';
        echo '<li>API not enabled in Google Cloud Console</li>';
        echo '<li>Billing not set up (if required)</li>';
        echo '</ul>';
        echo '</div>';
    } else {
        echo '<div class="box error">';
        echo '<h3>❌ HTTP ' . $httpCode . ' - Error</h3>';
        echo '<p><strong>Raw Response:</strong></p>';
        echo '<div class="code"><pre>' . htmlspecialchars(substr($response, 0, 1000)) . '</pre></div>';
        echo '</div>';
    }
    ?>
    
    <div class="box" style="background: #e7f3ff; border-color: #93c5fd;">
        <h3>ℹ️ Next Steps</h3>
        <ul>
            <li>If this test <strong>succeeds</strong> but upload fails, there may be a timing or payload issue</li>
            <li>If this test <strong>fails with 429</strong>, you've hit rate limits - wait and try again</li>
            <li>If this test <strong>fails with 400/403</strong>, check API key permissions in Google Cloud Console</li>
            <li>Check the error logs in hosting cPanel for more details</li>
        </ul>
    </div>
</body>
</html>
