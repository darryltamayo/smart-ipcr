<?php
/**
 * Debug AI Upload Settings
 * Check why AI verification is not running during file upload
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();
requireRole('admin', 'dean');

$db = getDB();

// Get AI settings
$settings = $db->query("
    SELECT config_key, config_value, updated_at 
    FROM system_config 
    WHERE config_key IN ('ai_verification_mode', 'gemini_api_key')
    ORDER BY config_key
")->fetchAll();

$aiMode = null;
$apiKey = null;

foreach ($settings as $s) {
    if ($s['config_key'] === 'ai_verification_mode') {
        $aiMode = $s['config_value'];
    }
    if ($s['config_key'] === 'gemini_api_key') {
        $apiKey = $s['config_value'];
    }
}

// Check what upload-evidence.php would see
$aiModeResult = $db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")->fetchColumn();
$aiModeProcessed = $aiModeResult && trim($aiModeResult) !== '' ? trim($aiModeResult) : 'advisory';

$apiKeyResult = trim($db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn() ?: '');

// Determine if AI would run
$wouldRunAI = ($aiModeProcessed !== 'disabled' && !empty($apiKeyResult));

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Debug AI Upload Settings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-<?= $wouldRunAI ? 'success' : 'danger' ?> text-white">
            <h3>
                <i class="bi bi-bug me-2"></i>
                Debug: AI Upload Settings
                <?= $wouldRunAI ? '<span class="badge bg-light text-success ms-2">AI ENABLED</span>' : '<span class="badge bg-light text-danger ms-2">AI DISABLED</span>' ?>
            </h3>
        </div>
        <div class="card-body">
            
            <!-- Current Settings -->
            <h5 class="mb-3"><i class="bi bi-database me-2"></i>Database Settings</h5>
            
            <?php if (!$settings): ?>
            <div class="alert alert-danger">
                <i class="bi bi-exclamation-triangle me-2"></i>
                <strong>NO AI SETTINGS FOUND IN DATABASE!</strong><br>
                The system_config table is missing AI configuration entries.
            </div>
            <?php else: ?>
            
            <table class="table table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th>Config Key</th>
                        <th>Value</th>
                        <th>Last Updated</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($settings as $s): ?>
                    <tr>
                        <td><code><?= htmlspecialchars($s['config_key']) ?></code></td>
                        <td>
                            <?php if ($s['config_key'] === 'gemini_api_key'): ?>
                                <?php if (empty($s['config_value'])): ?>
                                    <span class="text-danger"><em>[EMPTY]</em></span>
                                <?php else: ?>
                                    <code><?= htmlspecialchars(substr($s['config_value'], 0, 15)) ?>...</code>
                                    <span class="badge bg-success">Set</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <strong><?= htmlspecialchars($s['config_value'] ?: '[EMPTY]') ?></strong>
                            <?php endif; ?>
                        </td>
                        <td><?= $s['updated_at'] ? date('M d, Y H:i:s', strtotime($s['updated_at'])) : '—' ?></td>
                        <td>
                            <?php
                            if ($s['config_key'] === 'ai_verification_mode') {
                                if (empty($s['config_value'])) {
                                    echo '<span class="badge bg-danger">❌ EMPTY</span>';
                                } elseif ($s['config_value'] === 'disabled') {
                                    echo '<span class="badge bg-secondary">🚫 Disabled</span>';
                                } elseif ($s['config_value'] === 'advisory') {
                                    echo '<span class="badge bg-success">✅ Advisory</span>';
                                } elseif ($s['config_value'] === 'strict') {
                                    echo '<span class="badge bg-warning text-dark">⚠️ Strict</span>';
                                } else {
                                    echo '<span class="badge bg-danger">❓ Invalid: ' . htmlspecialchars($s['config_value']) . '</span>';
                                }
                            } elseif ($s['config_key'] === 'gemini_api_key') {
                                echo empty($s['config_value']) 
                                    ? '<span class="badge bg-danger">❌ Missing</span>' 
                                    : '<span class="badge bg-success">✅ Present</span>';
                            }
                            ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            
            <?php endif; ?>
            
            <hr>
            
            <!-- Processing Logic -->
            <h5 class="mb-3"><i class="bi bi-gear me-2"></i>Processing Logic (What upload-evidence.php sees)</h5>
            
            <div class="card mb-3 border-<?= $wouldRunAI ? 'success' : 'danger' ?>">
                <div class="card-body">
                    <h6>Step 1: Fetch AI Mode</h6>
                    <pre class="bg-light p-2 rounded"><code>$db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")</code></pre>
                    <p class="mb-1"><strong>Raw Result:</strong> <code><?= var_export($aiModeResult, true) ?></code></p>
                    <p class="mb-1"><strong>After Processing:</strong> <code>"<?= htmlspecialchars($aiModeProcessed) ?>"</code></p>
                    <p class="mb-0">
                        <small class="text-muted">
                            Logic: <code>$aiModeResult && trim($aiModeResult) !== '' ? trim($aiModeResult) : 'advisory'</code>
                        </small>
                    </p>
                </div>
            </div>
            
            <div class="card mb-3 border-<?= !empty($apiKeyResult) ? 'success' : 'danger' ?>">
                <div class="card-body">
                    <h6>Step 2: Fetch API Key</h6>
                    <pre class="bg-light p-2 rounded"><code>trim($db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn())</code></pre>
                    <p class="mb-1"><strong>Result:</strong> 
                        <?= !empty($apiKeyResult) 
                            ? '<code>' . htmlspecialchars(substr($apiKeyResult, 0, 20)) . '...</code> <span class="badge bg-success">Present</span>' 
                            : '<span class="text-danger"><em>[EMPTY]</em></span> <span class="badge bg-danger">Missing</span>' 
                        ?>
                    </p>
                </div>
            </div>
            
            <div class="card mb-3 border-<?= $wouldRunAI ? 'success' : 'danger' ?>">
                <div class="card-body">
                    <h6>Step 3: Check If AI Should Run</h6>
                    <pre class="bg-light p-2 rounded"><code>if ($aiMode !== 'disabled' && $apiKey) {
    // Run AI verification
}</code></pre>
                    <table class="table table-sm mb-2">
                        <tr>
                            <td><strong>AI Mode:</strong></td>
                            <td><code>"<?= htmlspecialchars($aiModeProcessed) ?>"</code></td>
                            <td><?= $aiModeProcessed !== 'disabled' ? '<span class="badge bg-success">✅ Not disabled</span>' : '<span class="badge bg-danger">❌ Disabled</span>' ?></td>
                        </tr>
                        <tr>
                            <td><strong>API Key:</strong></td>
                            <td><?= !empty($apiKeyResult) ? '<code>Set</code>' : '<em>[EMPTY]</em>' ?></td>
                            <td><?= !empty($apiKeyResult) ? '<span class="badge bg-success">✅ Present</span>' : '<span class="badge bg-danger">❌ Missing</span>' ?></td>
                        </tr>
                    </table>
                    <p class="mb-0">
                        <strong>Result:</strong>
                        <?php if ($wouldRunAI): ?>
                            <span class="badge bg-success fs-6">✅ AI WILL RUN</span>
                        <?php else: ?>
                            <span class="badge bg-danger fs-6">❌ AI WILL NOT RUN</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>
            
            <hr>
            
            <!-- Diagnosis -->
            <h5 class="mb-3"><i class="bi bi-clipboard-pulse me-2"></i>Diagnosis & Fix</h5>
            
            <?php if ($wouldRunAI): ?>
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle-fill me-2"></i>✅ AI Verification is Properly Configured</h6>
                <p class="mb-2">Based on the current settings, AI verification SHOULD run when faculty upload files.</p>
                <p class="mb-0"><strong>Expected Behavior:</strong></p>
                <ul class="mb-0">
                    <li>Mode = "<?= htmlspecialchars($aiModeProcessed) ?>" → AI will check files but NOT block uploads</li>
                    <li>Faculty will see confidence scores (e.g., "85% confidence", "60% uncertain")</li>
                    <li>All uploads are allowed regardless of AI score</li>
                </ul>
            </div>
            
            <?php if ($aiModeProcessed === 'advisory'): ?>
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle me-2"></i>Advisory Mode Active</h6>
                <p class="mb-0">
                    <strong>Faculty will see:</strong> Toast notifications with AI confidence scores<br>
                    <strong>✅ 80%+:</strong> "AI Verified (85%): file.pdf uploaded! Valid Evidence"<br>
                    <strong>⚠️ <80%:</strong> "Uploaded (60% confidence): file.pdf - AI suggests manual review"<br>
                    <strong>All uploads are allowed.</strong> Deans can review manually later.
                </p>
            </div>
            <?php endif; ?>
            
            <?php else: ?>
            <div class="alert alert-danger">
                <h6><i class="bi bi-x-circle-fill me-2"></i>❌ AI Verification is NOT Running</h6>
                <p class="mb-2"><strong>Reason:</strong></p>
                <ul class="mb-2">
                    <?php if ($aiModeProcessed === 'disabled'): ?>
                    <li>AI Mode is set to <strong>"disabled"</strong></li>
                    <?php endif; ?>
                    <?php if (empty($apiKeyResult)): ?>
                    <li>Gemini API Key is <strong>missing or empty</strong></li>
                    <?php endif; ?>
                </ul>
                
                <p class="mb-0"><strong>Current Behavior:</strong></p>
                <ul class="mb-0">
                    <li>Faculty uploads go through without AI check</li>
                    <li>Confidence score = 0%</li>
                    <li>Toast message: "Upload Complete (AI verification disabled)"</li>
                </ul>
            </div>
            
            <div class="alert alert-warning">
                <h6><i class="bi bi-tools me-2"></i>How to Fix:</h6>
                <ol class="mb-0">
                    <?php if ($aiModeProcessed === 'disabled'): ?>
                    <li>Go to <strong>Admin → Settings → AI Verification</strong></li>
                    <li>Select <strong>"Advisory Only"</strong> (recommended)</li>
                    <li>Save settings</li>
                    <?php endif; ?>
                    <?php if (empty($apiKeyResult)): ?>
                    <li>Go to <strong>Admin → Settings → AI Verification</strong></li>
                    <li>Enter your <strong>Gemini API Key</strong></li>
                    <li>Save settings</li>
                    <?php endif; ?>
                    <li>Test by uploading a file as faculty</li>
                </ol>
            </div>
            <?php endif; ?>
            
        </div>
        <div class="card-footer">
            <a href="<?= BASE_URL ?>/admin/settings.php" class="btn btn-primary">
                <i class="bi bi-gear me-2"></i>Go to Settings
            </a>
            <a href="<?= BASE_URL ?>/test-ai-settings.php" class="btn btn-info">
                <i class="bi bi-clipboard-check me-2"></i>Test AI Settings
            </a>
            <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">
                <i class="bi bi-house-door me-2"></i>Dashboard
            </a>
        </div>
    </div>
</div>
</body>
</html>
