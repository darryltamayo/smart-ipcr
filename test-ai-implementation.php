<?php
/**
 * AI Implementation Verification Test
 * Run this to verify all AI components are properly configured
 */

require_once __DIR__ . '/config/database.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>AI Implementation Test</title>
    <style>
        body { font-family: system-ui; max-width: 900px; margin: 40px auto; padding: 20px; }
        .test-section { background: #f8f9fa; border-left: 4px solid #0d6efd; padding: 15px; margin: 15px 0; border-radius: 4px; }
        .test-section h3 { margin-top: 0; color: #0d6efd; }
        .pass { color: #198754; font-weight: bold; }
        .fail { color: #dc3545; font-weight: bold; }
        .warn { color: #ffc107; font-weight: bold; }
        .code { background: #282c34; color: #abb2bf; padding: 15px; border-radius: 4px; overflow-x: auto; font-family: 'Courier New', monospace; font-size: 13px; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        table td, table th { padding: 8px; text-align: left; border: 1px solid #dee2e6; }
        table th { background: #e9ecef; font-weight: 600; }
        .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: 600; }
        .badge-success { background: #d1e7dd; color: #0f5132; }
        .badge-danger { background: #f8d7da; color: #842029; }
        .badge-warning { background: #fff3cd; color: #856404; }
    </style>
</head>
<body>
    <h1>🤖 AI Implementation Verification Test</h1>
    <p><strong>Purpose:</strong> Verify that all AI evidence verification components are properly configured and connected.</p>
    
    <?php
    try {
        $db = getDB();
        echo '<div class="test-section"><h3>✅ Database Connection</h3><p class="pass">SUCCESS - Database connected</p></div>';
    } catch (Exception $e) {
        echo '<div class="test-section"><h3>❌ Database Connection</h3><p class="fail">FAILED: ' . htmlspecialchars($e->getMessage()) . '</p></div>';
        exit;
    }
    
    // Test 1: Check system_config table
    echo '<div class="test-section">';
    echo '<h3>Test 1: System Configuration</h3>';
    
    $configs = $db->query("SELECT config_key, config_value FROM system_config WHERE config_key IN ('ai_verification_mode', 'gemini_api_key')")->fetchAll(PDO::FETCH_KEY_PAIR);
    
    $aiMode = $configs['ai_verification_mode'] ?? null;
    $apiKey = $configs['gemini_api_key'] ?? null;
    
    echo '<table>';
    echo '<tr><th>Configuration</th><th>Value</th><th>Status</th></tr>';
    
    // AI Mode
    echo '<tr><td><strong>ai_verification_mode</strong></td>';
    if ($aiMode) {
        echo '<td><span class="badge badge-success">' . htmlspecialchars($aiMode) . '</span></td>';
        echo '<td class="pass">✅ Configured</td>';
    } else {
        echo '<td><span class="badge badge-danger">NOT SET</span></td>';
        echo '<td class="fail">❌ Missing</td>';
    }
    echo '</tr>';
    
    // API Key
    echo '<tr><td><strong>gemini_api_key</strong></td>';
    if ($apiKey && strlen($apiKey) > 0) {
        $keyType = strpos($apiKey, 'sk-or-') === 0 ? 'OpenRouter' : 'Gemini';
        echo '<td><span class="badge badge-success">' . $keyType . '</span> (' . strlen($apiKey) . ' chars)<br>';
        echo '<code>' . htmlspecialchars(substr($apiKey, 0, 20)) . '...</code></td>';
        echo '<td class="pass">✅ Configured</td>';
    } else {
        echo '<td><span class="badge badge-danger">NOT SET</span></td>';
        echo '<td class="fail">❌ Missing</td>';
    }
    echo '</tr>';
    echo '</table>';
    
    // Overall assessment
    if ($aiMode && $apiKey) {
        echo '<p class="pass">✅ <strong>Configuration is complete!</strong> AI verification is ready to use.</p>';
    } else {
        echo '<p class="fail">❌ <strong>Configuration incomplete.</strong> Please set both AI mode and API key in Admin → Settings.</p>';
    }
    echo '</div>';
    
    // Test 2: Check file locations
    echo '<div class="test-section">';
    echo '<h3>Test 2: File Structure</h3>';
    
    $files = [
        'api/upload-evidence.php' => 'Main upload handler with AI integration',
        'faculty/ipcr-form.php' => 'Faculty upload form with toast notifications',
        'admin/settings.php' => 'AI configuration interface',
    ];
    
    echo '<table>';
    echo '<tr><th>File</th><th>Description</th><th>Status</th></tr>';
    
    foreach ($files as $file => $desc) {
        $fullPath = __DIR__ . '/' . $file;
        $exists = file_exists($fullPath);
        
        echo '<tr>';
        echo '<td><code>' . htmlspecialchars($file) . '</code></td>';
        echo '<td>' . htmlspecialchars($desc) . '</td>';
        
        if ($exists) {
            $size = filesize($fullPath);
            echo '<td class="pass">✅ Found (' . number_format($size) . ' bytes)</td>';
        } else {
            echo '<td class="fail">❌ Not found</td>';
        }
        echo '</tr>';
    }
    echo '</table>';
    echo '</div>';
    
    // Test 3: Check Gemini API implementation
    echo '<div class="test-section">';
    echo '<h3>Test 3: Gemini API Implementation</h3>';
    
    $uploadFile = file_get_contents(__DIR__ . '/api/upload-evidence.php');
    
    $checks = [
        'Gemini API URL' => strpos($uploadFile, 'generativelanguage.googleapis.com') !== false,
        'Gemini response parsing' => strpos($uploadFile, "['candidates'][0]['content']['parts'][0]['text']") !== false,
        'Quota error handling (HTTP 429)' => strpos($uploadFile, '429') !== false,
        'Advisory mode logic' => strpos($uploadFile, "aiMode === 'advisory'") !== false,
        'Strict mode logic' => strpos($uploadFile, "aiMode === 'strict'") !== false,
        'Debug logging' => strpos($uploadFile, 'error_log') !== false,
    ];
    
    echo '<table>';
    echo '<tr><th>Component</th><th>Status</th></tr>';
    
    $allPassed = true;
    foreach ($checks as $name => $passed) {
        echo '<tr>';
        echo '<td>' . htmlspecialchars($name) . '</td>';
        echo '<td>' . ($passed ? '<span class="pass">✅ Implemented</span>' : '<span class="fail">❌ Missing</span>') . '</td>';
        echo '</tr>';
        
        if (!$passed) $allPassed = false;
    }
    echo '</table>';
    
    if ($allPassed) {
        echo '<p class="pass">✅ <strong>All components are implemented!</strong></p>';
    } else {
        echo '<p class="fail">❌ <strong>Some components are missing.</strong> Implementation may be incomplete.</p>';
    }
    echo '</div>';
    
    // Test 4: Check upload directory
    echo '<div class="test-section">';
    echo '<h3>Test 4: Upload Directory</h3>';
    
    $uploadDir = __DIR__ . '/uploads/';
    $uploadExists = is_dir($uploadDir);
    $uploadWritable = $uploadExists && is_writable($uploadDir);
    
    echo '<table>';
    echo '<tr><th>Check</th><th>Status</th></tr>';
    echo '<tr><td>Directory exists</td><td>' . ($uploadExists ? '<span class="pass">✅ Yes</span>' : '<span class="fail">❌ No</span>') . '</td></tr>';
    echo '<tr><td>Directory writable</td><td>' . ($uploadWritable ? '<span class="pass">✅ Yes</span>' : '<span class="fail">❌ No</span>') . '</td></tr>';
    echo '<tr><td>Path</td><td><code>' . htmlspecialchars($uploadDir) . '</code></td></tr>';
    echo '</table>';
    
    if (!$uploadExists) {
        echo '<p class="warn">⚠️ <strong>Upload directory missing.</strong> It will be created automatically on first upload.</p>';
    } elseif (!$uploadWritable) {
        echo '<p class="fail">❌ <strong>Upload directory not writable.</strong> Please set permissions to 755 or 777.</p>';
    } else {
        echo '<p class="pass">✅ <strong>Upload directory is ready!</strong></p>';
    }
    echo '</div>';
    
    // Test 5: Simulated AI Check (dry run)
    echo '<div class="test-section">';
    echo '<h3>Test 5: AI Configuration Summary</h3>';
    
    if ($aiMode && $apiKey) {
        echo '<div style="background: #e7f3ff; padding: 15px; border-radius: 4px; border: 1px solid #b3d7ff;">';
        echo '<h4 style="margin-top: 0;">Current Configuration:</h4>';
        echo '<ul>';
        echo '<li><strong>AI Mode:</strong> <span class="badge badge-success">' . htmlspecialchars($aiMode) . '</span></li>';
        echo '<li><strong>API Type:</strong> ' . (strpos($apiKey, 'sk-or-') === 0 ? 'OpenRouter' : '<strong>Google Gemini</strong>') . '</li>';
        echo '<li><strong>API Key Length:</strong> ' . strlen($apiKey) . ' characters</li>';
        echo '</ul>';
        
        if ($aiMode === 'advisory') {
            echo '<p class="pass">✅ <strong>Advisory Mode Active</strong> - All uploads will be allowed. AI provides confidence scores and warnings only.</p>';
        } elseif ($aiMode === 'strict') {
            echo '<p class="warn">⚠️ <strong>Strict Mode Active</strong> - Uploads with confidence < 50% will be blocked. Consider using Advisory mode for better user experience.</p>';
        } else {
            echo '<p class="fail">❌ <strong>AI Disabled</strong> - No AI verification will be performed.</p>';
        }
        
        echo '<h4>What happens on upload:</h4>';
        echo '<ol style="line-height: 1.8;">';
        echo '<li>File is uploaded to <code>uploads/[form_id]/[si_id]/</code></li>';
        echo '<li>System sends file info to Gemini API for analysis</li>';
        echo '<li>Gemini returns verdict (valid/uncertain/invalid) + confidence score</li>';
        
        if ($aiMode === 'advisory') {
            echo '<li><strong>Advisory Mode:</strong> Upload succeeds regardless of verdict</li>';
            echo '<li>User sees confidence score in toast notification</li>';
            echo '<li>Dean can review AI verdict in review queue</li>';
        } elseif ($aiMode === 'strict') {
            echo '<li><strong>Strict Mode:</strong> Upload blocked if confidence < 50%</li>';
            echo '<li>User sees detailed error message with suggestions</li>';
            echo '<li>Dean only sees approved uploads in review queue</li>';
        }
        
        echo '</ol>';
        
        echo '<h4>Quota Handling:</h4>';
        echo '<p>If Gemini API quota is exceeded (HTTP 429 error):</p>';
        echo '<ul>';
        echo '<li>User sees: <em>"⚠️ AI verification temporarily unavailable"</em></li>';
        echo '<li>Upload <strong>continues successfully</strong> (AI error won\'t block users)</li>';
        echo '<li>File will be reviewed manually by Dean</li>';
        echo '<li>Error is logged for admin monitoring</li>';
        echo '</ul>';
        
        echo '</div>';
    } else {
        echo '<p class="fail">❌ <strong>Cannot test - configuration incomplete.</strong></p>';
    }
    
    echo '</div>';
    
    // Final Summary
    echo '<div class="test-section" style="border-left-color: #198754; background: #d1e7dd;">';
    echo '<h3 style="color: #0f5132;">📋 Final Summary</h3>';
    
    $ready = $aiMode && $apiKey && $allPassed && $uploadWritable;
    
    if ($ready) {
        echo '<p class="pass" style="font-size: 18px;">✅ <strong>SYSTEM IS READY!</strong> AI Evidence Verification is fully configured and operational.</p>';
        echo '<h4>Next Steps:</h4>';
        echo '<ol>';
        echo '<li>Have faculty test uploading evidence files</li>';
        echo '<li>Monitor AI confidence scores in toast notifications</li>';
        echo '<li>Check Dean review queue for AI verdicts</li>';
        echo '<li>Review error logs if quota issues occur</li>';
        echo '</ol>';
    } else {
        echo '<p class="fail" style="font-size: 18px;">❌ <strong>SYSTEM NOT READY.</strong> Please fix the issues above.</p>';
        echo '<h4>Required Actions:</h4>';
        echo '<ul>';
        if (!$aiMode) echo '<li>Set AI verification mode in Admin → Settings</li>';
        if (!$apiKey) echo '<li>Configure Gemini API key in Admin → Settings</li>';
        if (!$allPassed) echo '<li>Verify code implementation is complete</li>';
        if (!$uploadWritable) echo '<li>Fix upload directory permissions</li>';
        echo '</ul>';
    }
    
    echo '</div>';
    
    ?>
    
    <div style="margin-top: 40px; padding: 20px; background: #f8f9fa; border-radius: 4px; font-size: 13px; color: #6c757d;">
        <strong>Test completed at:</strong> <?= date('Y-m-d H:i:s') ?><br>
        <strong>Server:</strong> <?= php_uname('n') ?><br>
        <strong>PHP Version:</strong> <?= phpversion() ?><br>
        <strong>Database:</strong> <?= DB_NAME ?> @ <?= DB_HOST ?>
    </div>
</body>
</html>
