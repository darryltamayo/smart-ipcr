<?php
/**
 * Force Enable AI Advisory Mode
 * This script ensures AI verification is properly enabled in advisory mode
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();
requireRole('admin');

$db = getDB();
$messages = [];

// Step 1: Check current settings
$currentSettings = $db->query("
    SELECT config_key, config_value 
    FROM system_config 
    WHERE config_key IN ('ai_verification_mode', 'gemini_api_key')
")->fetchAll(PDO::FETCH_KEY_PAIR);

$currentMode = $currentSettings['ai_verification_mode'] ?? null;
$currentKey = $currentSettings['gemini_api_key'] ?? null;

// Step 2: Force set AI mode to 'advisory' if needed
if ($currentMode !== 'advisory') {
    $stmt = $db->prepare("
        INSERT INTO system_config (config_key, config_value, updated_at) 
        VALUES ('ai_verification_mode', 'advisory', NOW())
        ON DUPLICATE KEY UPDATE config_value = 'advisory', updated_at = NOW()
    ");
    $stmt->execute();
    
    $messages[] = [
        'type' => 'success',
        'title' => '✅ AI Mode Updated',
        'message' => "Changed from '{$currentMode}' to 'advisory'"
    ];
    
    auditLog('updated', 'config', 'ai_verification_mode', $currentMode, 'advisory', 'Forced to advisory mode via fix script');
} else {
    $messages[] = [
        'type' => 'info',
        'title' => 'ℹ️ AI Mode Already Correct',
        'message' => "Mode is already set to 'advisory'"
    ];
}

// Step 3: Check API key
if (empty($currentKey)) {
    $messages[] = [
        'type' => 'warning',
        'title' => '⚠️ API Key Missing',
        'message' => "You need to add a Gemini API Key in Admin → Settings for AI to work"
    ];
} else {
    $messages[] = [
        'type' => 'success',
        'title' => '✅ API Key Present',
        'message' => "API Key is configured (starts with: " . substr($currentKey, 0, 15) . "...)"
    ];
}

// Step 4: Verify the fix
$verifyMode = $db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")->fetchColumn();
$verifyKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

$aiWillWork = ($verifyMode === 'advisory' && !empty($verifyKey));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Force Enable AI Advisory Mode</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-<?= $aiWillWork ? 'success' : 'warning' ?> text-white">
            <h3>
                <i class="bi bi-lightning-charge-fill me-2"></i>
                Force Enable AI Advisory Mode
            </h3>
        </div>
        <div class="card-body">
            
            <h5 class="mb-3"><i class="bi bi-list-check me-2"></i>Actions Performed:</h5>
            
            <?php foreach ($messages as $msg): ?>
            <div class="alert alert-<?= $msg['type'] ?>">
                <h6><?= $msg['title'] ?></h6>
                <p class="mb-0"><?= $msg['message'] ?></p>
            </div>
            <?php endforeach; ?>
            
            <hr>
            
            <h5 class="mb-3"><i class="bi bi-clipboard-check me-2"></i>Verification Results:</h5>
            
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Setting</th>
                        <th>Current Value</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong>AI Verification Mode</strong></td>
                        <td><code><?= htmlspecialchars($verifyMode ?: '[NOT SET]') ?></code></td>
                        <td>
                            <?php if ($verifyMode === 'advisory'): ?>
                                <span class="badge bg-success">✅ Correct</span>
                            <?php else: ?>
                                <span class="badge bg-danger">❌ Wrong Value</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Gemini API Key</strong></td>
                        <td>
                            <?php if (empty($verifyKey)): ?>
                                <em class="text-danger">[NOT SET]</em>
                            <?php else: ?>
                                <code><?= htmlspecialchars(substr($verifyKey, 0, 20)) ?>...</code>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($verifyKey)): ?>
                                <span class="badge bg-success">✅ Present</span>
                            <?php else: ?>
                                <span class="badge bg-danger">❌ Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <hr>
            
            <?php if ($aiWillWork): ?>
            <div class="alert alert-success">
                <h5><i class="bi bi-check-circle-fill me-2"></i>✅ AI Verification is Now Enabled!</h5>
                <p class="mb-2"><strong>What this means:</strong></p>
                <ul class="mb-0">
                    <li>✅ Faculty uploads will be checked by AI</li>
                    <li>✅ Confidence scores will be shown (e.g., "85% confidence")</li>
                    <li>✅ All uploads are allowed (advisory mode never blocks)</li>
                    <li>✅ Dean can review AI scores manually</li>
                </ul>
            </div>
            
            <div class="alert alert-info">
                <h6><i class="bi bi-lightbulb me-2"></i>What Faculty Will See:</h6>
                <p class="mb-1"><strong>High Confidence (80%+):</strong></p>
                <p class="text-success mb-2">✅ AI Verified (85%): file.pdf uploaded! <em>Valid Evidence</em></p>
                
                <p class="mb-1"><strong>Uncertain (<80%):</strong></p>
                <p class="text-warning mb-0">⚠️ Uploaded (60% confidence): file.pdf - AI suggests manual review <em>Uncertain Evidence</em></p>
            </div>
            
            <?php else: ?>
            <div class="alert alert-warning">
                <h5><i class="bi bi-exclamation-triangle-fill me-2"></i>⚠️ AI Still Not Fully Configured</h5>
                
                <?php if (empty($verifyKey)): ?>
                <p class="mb-2"><strong>Missing: Gemini API Key</strong></p>
                <p class="mb-0">You need to add your Gemini API Key to make AI work:</p>
                <ol class="mt-2 mb-0">
                    <li>Go to <strong>Admin → Settings → AI Verification</strong></li>
                    <li>Scroll to <strong>"Google Gemini API Key"</strong></li>
                    <li>Paste your API key</li>
                    <li>Click <strong>"Save AI Settings"</strong></li>
                </ol>
                <?php else: ?>
                <p class="mb-0">AI Mode is not set correctly. Please check Admin → Settings.</p>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
        <div class="card-footer">
            <a href="<?= BASE_URL ?>/admin/settings.php" class="btn btn-primary">
                <i class="bi bi-gear me-2"></i>Admin Settings
            </a>
            <a href="<?= BASE_URL ?>/debug-ai-upload.php" class="btn btn-info">
                <i class="bi bi-bug me-2"></i>Debug AI Upload
            </a>
            <a href="<?= BASE_URL ?>/test-ai-settings.php" class="btn btn-secondary">
                <i class="bi bi-clipboard-check me-2"></i>Test AI Settings
            </a>
            <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-outline-secondary">
                <i class="bi bi-house-door me-2"></i>Dashboard
            </a>
        </div>
    </div>
    
    <?php if ($aiWillWork): ?>
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6><i class="bi bi-info-circle me-2"></i>Next Steps: Test the AI Verification</h6>
        </div>
        <div class="card-body">
            <ol>
                <li>Login as a <strong>faculty member</strong></li>
                <li>Go to <strong>"My IPCR Form"</strong></li>
                <li>Try uploading a file for any Success Indicator</li>
                <li>You should see AI confidence scores in the toast notification</li>
                <li>If you see "0% confidence" or "AI verification disabled" = AI is still not working</li>
            </ol>
            
            <p class="mb-0 mt-3"><strong>Troubleshooting:</strong> If AI still doesn't work after this fix, run the debug script above to see detailed diagnostics.</p>
        </div>
    </div>
    <?php endif; ?>
</div>
</body>
</html>
