<?php
/**
 * Sync AI Settings to Online Database
 * Use this to ensure API key is properly saved online
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();
requireRole('admin');

$db = getDB();
$action = $_POST['action'] ?? 'view';
$message = '';
$messageType = 'info';

if ($action === 'force_save') {
    $apiKey = trim($_POST['api_key'] ?? '');
    $aiMode = trim($_POST['ai_mode'] ?? 'advisory');
    
    if (empty($apiKey)) {
        $message = '❌ API Key cannot be empty!';
        $messageType = 'danger';
    } else {
        try {
            // Force insert/update both settings
            $stmt = $db->prepare("
                INSERT INTO system_config (config_key, config_value, updated_at) 
                VALUES (?, ?, NOW())
                ON DUPLICATE KEY UPDATE config_value = VALUES(config_value), updated_at = NOW()
            ");
            
            // Save AI mode
            $stmt->execute(['ai_verification_mode', $aiMode]);
            
            // Save API key
            $stmt->execute(['gemini_api_key', $apiKey]);
            
            // Verify both were saved
            $verifyMode = $db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")->fetchColumn();
            $verifyKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();
            
            if ($verifyMode === $aiMode && $verifyKey === $apiKey) {
                $message = "✅ SUCCESS! AI settings saved and verified in database.";
                $messageType = 'success';
                
                auditLog('updated', 'config', 'ai_settings', null, [
                    'mode' => $aiMode,
                    'api_key' => 'set'
                ], 'AI settings force synced to database');
            } else {
                $message = "⚠️ Settings saved but verification failed. Check database permissions.";
                $messageType = 'warning';
            }
            
        } catch (Exception $e) {
            $message = "❌ Database Error: " . $e->getMessage();
            $messageType = 'danger';
        }
    }
}

// Get current settings
$currentMode = $db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")->fetchColumn();
$currentKey = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sync AI Settings Online</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3>
                <i class="bi bi-cloud-arrow-up-fill me-2"></i>
                Sync AI Settings to Online Database
            </h3>
        </div>
        <div class="card-body">
            
            <?php if ($message): ?>
            <div class="alert alert-<?= $messageType ?> alert-dismissible">
                <?= $message ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle me-2"></i>Why use this tool?</h6>
                <p class="mb-0">
                    If you're testing on <strong>https://equipment-borrowing.online</strong> and AI verification 
                    shows as "disabled" even after enabling in settings, this tool forces the settings to be 
                    saved directly to the database.
                </p>
            </div>
            
            <hr>
            
            <h5 class="mb-3"><i class="bi bi-database me-2"></i>Current Database Settings</h5>
            
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
                        <td>
                            <?php if (empty($currentMode)): ?>
                                <em class="text-danger">[NOT SET]</em>
                            <?php else: ?>
                                <code><?= htmlspecialchars($currentMode) ?></code>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($currentMode === 'advisory'): ?>
                                <span class="badge bg-success">✅ Correct</span>
                            <?php elseif ($currentMode === 'disabled'): ?>
                                <span class="badge bg-secondary">🚫 Disabled</span>
                            <?php elseif ($currentMode === 'strict'): ?>
                                <span class="badge bg-warning">⚠️ Strict</span>
                            <?php else: ?>
                                <span class="badge bg-danger">❌ Invalid/Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>Gemini API Key</strong></td>
                        <td>
                            <?php if (empty($currentKey)): ?>
                                <em class="text-danger">[NOT SET]</em>
                            <?php else: ?>
                                <code><?= htmlspecialchars(substr($currentKey, 0, 20)) ?>...</code>
                                <small class="text-muted">(<?= strlen($currentKey) ?> chars)</small>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if (!empty($currentKey)): ?>
                                <span class="badge bg-success">✅ Present</span>
                            <?php else: ?>
                                <span class="badge bg-danger">❌ Missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            
            <hr>
            
            <h5 class="mb-3"><i class="bi bi-pencil-square me-2"></i>Force Save Settings</h5>
            
            <form method="POST">
                <input type="hidden" name="action" value="force_save">
                
                <div class="mb-3">
                    <label class="form-label"><strong>AI Verification Mode</strong></label>
                    <select name="ai_mode" class="form-select" required>
                        <option value="advisory" <?= $currentMode === 'advisory' ? 'selected' : '' ?>>Advisory Only (Recommended)</option>
                        <option value="strict" <?= $currentMode === 'strict' ? 'selected' : '' ?>>Strict Mode</option>
                        <option value="disabled" <?= $currentMode === 'disabled' ? 'selected' : '' ?>>Disabled</option>
                    </select>
                    <small class="text-muted">
                        <strong>Advisory:</strong> AI checks files but never blocks uploads (recommended)<br>
                        <strong>Strict:</strong> AI blocks uploads below 50% confidence<br>
                        <strong>Disabled:</strong> No AI verification
                    </small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Google Gemini API Key</strong></label>
                    <input type="text" 
                           name="api_key" 
                           class="form-control font-monospace" 
                           value="<?= htmlspecialchars($currentKey ?: '') ?>" 
                           placeholder="AIzaSy..." 
                           required>
                    <small class="text-muted">
                        Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a>
                    </small>
                </div>
                
                <div class="alert alert-warning">
                    <h6><i class="bi bi-exclamation-triangle me-2"></i>Important Notes:</h6>
                    <ul class="mb-0">
                        <li>This will <strong>directly update</strong> the database</li>
                        <li>Make sure the API key is correct (test it first)</li>
                        <li>Changes take effect immediately</li>
                        <li>Faculty may need to refresh their browser</li>
                    </ul>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100">
                    <i class="bi bi-cloud-check me-2"></i>Force Save to Database
                </button>
            </form>
            
        </div>
        <div class="card-footer">
            <div class="d-flex gap-2 justify-content-between">
                <div>
                    <a href="<?= BASE_URL ?>/test-upload-ai-check.php" class="btn btn-info">
                        <i class="bi bi-clipboard-check me-2"></i>Test Upload Settings
                    </a>
                    <a href="<?= BASE_URL ?>/test-gemini-key.php" class="btn btn-secondary">
                        <i class="bi bi-key me-2"></i>Test API Key
                    </a>
                </div>
                <a href="<?= BASE_URL ?>/admin/settings.php" class="btn btn-primary">
                    <i class="bi bi-gear me-2"></i>Admin Settings
                </a>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6><i class="bi bi-lightbulb me-2"></i>Troubleshooting Steps</h6>
        </div>
        <div class="card-body">
            <ol>
                <li><strong>Force save settings above</strong> - This ensures settings are in database</li>
                <li><strong>Test API key:</strong> <a href="<?= BASE_URL ?>/test-gemini-key.php">test-gemini-key.php</a></li>
                <li><strong>Test upload check:</strong> <a href="<?= BASE_URL ?>/test-upload-ai-check.php">test-upload-ai-check.php</a></li>
                <li><strong>Clear browser cache</strong> - Faculty should clear cache and reload</li>
                <li><strong>Try incognito mode</strong> - Test as faculty in private browsing</li>
                <li><strong>Check server error logs</strong> - Contact hosting provider for PHP error logs</li>
            </ol>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
