<?php
/**
 * Test AI Settings Switch
 * Verifies that the AI mode switch works without errors
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/constants.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test AI Settings</title>
    <style>
        body { font-family: system-ui; max-width: 900px; margin: 40px auto; padding: 20px; background: #f5f5f5; }
        .card { background: white; border-radius: 8px; padding: 25px; margin-bottom: 20px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
        h1 { color: #2c5f2d; margin-top: 0; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #dc3545; }
        .info { background: #d1ecf1; color: #0c5460; padding: 15px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #17a2b8; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 6px; margin: 10px 0; border-left: 4px solid #ffc107; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        td { padding: 10px; border-bottom: 1px solid #eee; }
        td:first-child { font-weight: bold; width: 250px; color: #666; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; font-family: monospace; }
        .btn { background: #2c5f2d; color: white; border: none; padding: 10px 20px; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
        .btn:hover { background: #1e4620; }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #545b62; }
    </style>
</head>
<body>
    <div class="card">
        <h1>🧪 AI Settings Test</h1>
        <p>Testing AI verification mode configuration...</p>
    </div>

    <?php
    try {
        $db = getDB();
        echo '<div class="success">✅ Database connection successful</div>';
    } catch (Exception $e) {
        echo '<div class="error">❌ Database error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        exit;
    }
    ?>

    <div class="card">
        <h2>📊 Current Configuration</h2>
        
        <?php
        // Check if config exists
        $stmt = $db->query("SELECT config_key, config_value FROM system_config WHERE config_key IN ('ai_verification_mode', 'gemini_api_key')");
        $configs = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        
        $aiMode = $configs['ai_verification_mode'] ?? null;
        $apiKey = $configs['gemini_api_key'] ?? null;
        ?>
        
        <table>
            <tr>
                <td>AI Verification Mode:</td>
                <td>
                    <?php if ($aiMode === null): ?>
                        <span style="color: #dc3545;">❌ Not configured</span>
                    <?php else: ?>
                        <code><?= htmlspecialchars($aiMode) ?></code>
                        <?php if (in_array($aiMode, ['disabled', 'advisory', 'strict'])): ?>
                            <span style="color: #28a745;"> ✅ Valid</span>
                        <?php else: ?>
                            <span style="color: #dc3545;"> ❌ Invalid value</span>
                        <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td>API Key Status:</td>
                <td>
                    <?php if ($apiKey === null || $apiKey === ''): ?>
                        <span style="color: #856404;">⚠️ Not configured</span>
                    <?php else: ?>
                        <span style="color: #28a745;">✅ Configured</span>
                        <code><?= htmlspecialchars(substr($apiKey, 0, 15)) ?>...</code>
                    <?php endif; ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>🔧 Test Mode Switching</h2>
        <p>Click buttons below to test switching between modes:</p>
        
        <form method="POST" action="" style="display: inline;">
            <input type="hidden" name="test_action" value="set_disabled">
            <button type="submit" class="btn">Set to: Disabled</button>
        </form>
        
        <form method="POST" action="" style="display: inline;">
            <input type="hidden" name="test_action" value="set_advisory">
            <button type="submit" class="btn">Set to: Advisory</button>
        </form>
        
        <form method="POST" action="" style="display: inline;">
            <input type="hidden" name="test_action" value="set_strict">
            <button type="submit" class="btn">Set to: Strict</button>
        </form>
        
        <?php
        if (isset($_POST['test_action'])) {
            $action = $_POST['test_action'];
            
            try {
                $newMode = '';
                switch ($action) {
                    case 'set_disabled':
                        $newMode = 'disabled';
                        break;
                    case 'set_advisory':
                        $newMode = 'advisory';
                        break;
                    case 'set_strict':
                        $newMode = 'strict';
                        break;
                }
                
                if ($newMode) {
                    $stmt = $db->prepare("INSERT INTO system_config (config_key, config_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE config_value = VALUES(config_value)");
                    $stmt->execute(['ai_verification_mode', $newMode]);
                    
                    // Verify it was saved
                    $check = $db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")->fetchColumn();
                    
                    if ($check === $newMode) {
                        echo '<div class="success">';
                        echo '<strong>✅ Success!</strong><br>';
                        echo 'AI mode changed to: <code>' . htmlspecialchars($newMode) . '</code><br>';
                        echo 'Database value confirmed: <code>' . htmlspecialchars($check) . '</code>';
                        echo '</div>';
                        
                        // Refresh to show new value
                        echo '<script>setTimeout(function(){ window.location.reload(); }, 2000);</script>';
                    } else {
                        echo '<div class="error">';
                        echo '<strong>❌ Verification Failed</strong><br>';
                        echo 'Expected: <code>' . htmlspecialchars($newMode) . '</code><br>';
                        echo 'Got: <code>' . htmlspecialchars($check) . '</code>';
                        echo '</div>';
                    }
                }
            } catch (Exception $e) {
                echo '<div class="error">';
                echo '<strong>❌ Error:</strong> ' . htmlspecialchars($e->getMessage());
                echo '</div>';
            }
        }
        ?>
    </div>

    <div class="card">
        <h2>✅ Validation Results</h2>
        
        <?php
        $errors = [];
        $warnings = [];
        $success = [];
        
        // Check 1: Database table exists
        try {
            $db->query("SELECT 1 FROM system_config LIMIT 1");
            $success[] = "system_config table exists";
        } catch (Exception $e) {
            $errors[] = "system_config table missing or inaccessible";
        }
        
        // Check 2: AI mode is valid
        if ($aiMode === null) {
            $warnings[] = "AI mode not configured (will default to 'advisory')";
        } elseif (!in_array($aiMode, ['disabled', 'advisory', 'strict'])) {
            $errors[] = "AI mode has invalid value: '{$aiMode}'";
        } else {
            $success[] = "AI mode is valid: '{$aiMode}'";
        }
        
        // Check 3: API key format
        if ($apiKey && strlen($apiKey) > 0) {
            if (strpos($apiKey, 'AIza') === 0) {
                $success[] = "API key format: Legacy Gemini (AIza...)";
            } elseif (strpos($apiKey, 'AQ.') === 0 || strpos($apiKey, 'AQ.A') === 0) {
                $success[] = "API key format: New OAuth Gemini (AQ....)";
            } elseif (strpos($apiKey, 'sk-or-') === 0) {
                $success[] = "API key format: OpenRouter (sk-or-...)";
            } else {
                $warnings[] = "API key format unknown (may not work)";
            }
        }
        
        // Check 4: Upload handler exists
        if (file_exists(__DIR__ . '/api/upload-evidence.php')) {
            $success[] = "Upload handler exists";
        } else {
            $errors[] = "Upload handler missing: api/upload-evidence.php";
        }
        
        // Display results
        if (count($success) > 0) {
            echo '<div class="success">';
            echo '<strong>✅ Passed Checks:</strong><ul style="margin: 5px 0; padding-left: 20px;">';
            foreach ($success as $s) echo '<li>' . htmlspecialchars($s) . '</li>';
            echo '</ul></div>';
        }
        
        if (count($warnings) > 0) {
            echo '<div class="warning">';
            echo '<strong>⚠️ Warnings:</strong><ul style="margin: 5px 0; padding-left: 20px;">';
            foreach ($warnings as $w) echo '<li>' . htmlspecialchars($w) . '</li>';
            echo '</ul></div>';
        }
        
        if (count($errors) > 0) {
            echo '<div class="error">';
            echo '<strong>❌ Errors Found:</strong><ul style="margin: 5px 0; padding-left: 20px;">';
            foreach ($errors as $e) echo '<li>' . htmlspecialchars($e) . '</li>';
            echo '</ul></div>';
        }
        
        if (count($errors) === 0) {
            echo '<div class="info">';
            echo '<strong>🎉 All Systems Operational!</strong><br>';
            echo 'The AI settings switch is working correctly. No errors detected.';
            echo '</div>';
        }
        ?>
    </div>

    <div class="card">
        <h2>📋 Next Steps</h2>
        <ol style="line-height: 2;">
            <li>Go to <a href="admin/settings.php"><strong>Admin → Settings</strong></a></li>
            <li>Scroll to "AI Evidence Verification" section</li>
            <li>Select your preferred mode (Disabled / Advisory / Strict)</li>
            <li>Paste your Google Gemini API key</li>
            <li>Click "Save AI Settings"</li>
        </ol>
        
        <div style="margin-top: 20px;">
            <a href="admin/settings.php" class="btn">Go to Settings Page</a>
            <a href="test-gemini-key.php" class="btn btn-secondary">Test API Key</a>
        </div>
    </div>

    <div class="card">
        <p style="text-align: center; color: #666; margin: 0;">
            <strong>🔒 Security:</strong> Delete this test file after use: <code>test-ai-settings.php</code>
        </p>
    </div>
</body>
</html>
