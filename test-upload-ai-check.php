<?php
/**
 * Real-Time Upload AI Check Diagnostic
 * Run this to see EXACTLY what upload-evidence.php sees
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();

$db = getDB();

// EXACT SAME LOGIC as upload-evidence.php
$aiModeResult = $db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")->fetchColumn();
$aiMode = $aiModeResult && trim($aiModeResult) !== '' ? trim($aiModeResult) : 'advisory';

// Get API Key - EXACT SAME as upload-evidence.php
$apiKeyResult = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();
$apiKey = trim($apiKeyResult);

// The actual check
$willRunAI = ($aiMode !== 'disabled' && $apiKey);

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload AI Check Diagnostic</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .debug-box {
            background: #f8f9fa;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin: 10px 0;
            font-family: monospace;
        }
        .pass { background: #d4edda; border-color: #c3e6cb; }
        .fail { background: #f8d7da; border-color: #f5c6cb; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-<?= $willRunAI ? 'success' : 'danger' ?> text-white">
            <h3>
                <i class="bi bi-bug-fill me-2"></i>
                Real-Time Upload AI Check
                <?= $willRunAI ? '<span class="badge bg-light text-success">✅ WILL RUN</span>' : '<span class="badge bg-light text-danger">❌ WON\'T RUN</span>' ?>
            </h3>
        </div>
        <div class="card-body">
            
            <h5 class="mb-3">🔍 EXACT Upload Logic Simulation</h5>
            <p class="text-muted">This shows EXACTLY what <code>upload-evidence.php</code> sees when faculty uploads a file.</p>
            
            <hr>
            
            <!-- Step 1: AI Mode Check -->
            <div class="debug-box <?= ($aiMode !== 'disabled') ? 'pass' : 'fail' ?>">
                <h6><strong>Step 1: Get AI Mode</strong></h6>
                <pre>$aiModeResult = $db->query("SELECT config_value FROM system_config WHERE config_key='ai_verification_mode'")->fetchColumn();</pre>
                
                <table class="table table-sm table-bordered bg-white mt-2">
                    <tr>
                        <td><strong>Raw DB Result:</strong></td>
                        <td><code><?= var_export($aiModeResult, true) ?></code></td>
                    </tr>
                    <tr>
                        <td><strong>After trim():</strong></td>
                        <td><code>"<?= htmlspecialchars($aiMode) ?>"</code></td>
                    </tr>
                    <tr>
                        <td><strong>Check:</strong></td>
                        <td><code>$aiMode !== 'disabled'</code></td>
                    </tr>
                    <tr>
                        <td><strong>Result:</strong></td>
                        <td>
                            <?php if ($aiMode !== 'disabled'): ?>
                                <span class="badge bg-success">✅ PASS - Not disabled</span>
                            <?php else: ?>
                                <span class="badge bg-danger">❌ FAIL - Mode is 'disabled'</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Step 2: API Key Check -->
            <div class="debug-box <?= !empty($apiKey) ? 'pass' : 'fail' ?>">
                <h6><strong>Step 2: Get API Key</strong></h6>
                <pre>$apiKey = trim($db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn());</pre>
                
                <table class="table table-sm table-bordered bg-white mt-2">
                    <tr>
                        <td><strong>Raw DB Result:</strong></td>
                        <td>
                            <?php if ($apiKeyResult === false): ?>
                                <code class="text-danger">FALSE (not found in DB)</code>
                            <?php elseif ($apiKeyResult === null): ?>
                                <code class="text-danger">NULL</code>
                            <?php elseif (empty($apiKeyResult)): ?>
                                <code class="text-danger">'' (empty string)</code>
                            <?php else: ?>
                                <code><?= htmlspecialchars(substr($apiKeyResult, 0, 20)) ?>...</code>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>After trim():</strong></td>
                        <td>
                            <?php if (empty($apiKey)): ?>
                                <code class="text-danger">'' (empty)</code>
                            <?php else: ?>
                                <code><?= htmlspecialchars(substr($apiKey, 0, 20)) ?>...</code>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <tr>
                        <td><strong>String Length:</strong></td>
                        <td><code><?= strlen($apiKey) ?> characters</code></td>
                    </tr>
                    <tr>
                        <td><strong>Check:</strong></td>
                        <td><code>!empty($apiKey)</code> or <code>$apiKey evaluates to truthy</code></td>
                    </tr>
                    <tr>
                        <td><strong>Result:</strong></td>
                        <td>
                            <?php if (!empty($apiKey)): ?>
                                <span class="badge bg-success">✅ PASS - API Key present</span>
                            <?php else: ?>
                                <span class="badge bg-danger">❌ FAIL - API Key is empty/missing</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <!-- Step 3: Final Check -->
            <div class="debug-box <?= $willRunAI ? 'pass' : 'fail' ?>">
                <h6><strong>Step 3: Final Decision</strong></h6>
                <pre>if ($aiMode !== 'disabled' && $apiKey) {
    // Run AI verification
}</pre>
                
                <table class="table table-sm table-bordered bg-white mt-2">
                    <tr>
                        <td><strong>Condition 1:</strong></td>
                        <td><code>$aiMode !== 'disabled'</code></td>
                        <td><?= ($aiMode !== 'disabled') ? '<span class="badge bg-success">✅ TRUE</span>' : '<span class="badge bg-danger">❌ FALSE</span>' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Condition 2:</strong></td>
                        <td><code>$apiKey (truthy check)</code></td>
                        <td><?= $apiKey ? '<span class="badge bg-success">✅ TRUE</span>' : '<span class="badge bg-danger">❌ FALSE</span>' ?></td>
                    </tr>
                    <tr>
                        <td><strong>Both Conditions:</strong></td>
                        <td><code>TRUE && TRUE</code></td>
                        <td>
                            <?php if ($willRunAI): ?>
                                <span class="badge bg-success fs-5">✅ AI WILL RUN</span>
                            <?php else: ?>
                                <span class="badge bg-danger fs-5">❌ AI WILL NOT RUN</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
            
            <hr>
            
            <!-- Diagnosis -->
            <h5 class="mb-3">🩺 Diagnosis & Solution</h5>
            
            <?php if ($willRunAI): ?>
            <div class="alert alert-success">
                <h6><i class="bi bi-check-circle-fill me-2"></i>✅ Configuration is Correct</h6>
                <p class="mb-0">Based on the settings, AI verification SHOULD run when faculty uploads files.</p>
                <p class="mt-2 mb-0"><strong>If faculty still sees "disabled" message:</strong></p>
                <ul class="mb-0">
                    <li>Clear browser cache and try again</li>
                    <li>Check if the API key is actually valid (test with <code>test-gemini-key.php</code>)</li>
                    <li>Check Apache error logs for PHP errors</li>
                </ul>
            </div>
            <?php else: ?>
            <div class="alert alert-danger">
                <h6><i class="bi bi-x-circle-fill me-2"></i>❌ Problem Found!</h6>
                
                <?php if ($aiMode === 'disabled'): ?>
                <div class="mb-3">
                    <strong>Problem: AI Mode is 'disabled'</strong>
                    <p class="mb-0">Even though you set it to 'advisory' in settings, the database still has 'disabled'.</p>
                </div>
                <div class="alert alert-warning">
                    <strong>🔧 Fix:</strong>
                    <ol class="mb-0">
                        <li>Go to <a href="<?= BASE_URL ?>/admin/settings.php" class="alert-link">Admin → Settings</a></li>
                        <li>Select <strong>"Advisory Only"</strong> radio button</li>
                        <li>Click <strong>"Save AI Settings"</strong></li>
                        <li>Come back to this page and refresh to verify</li>
                    </ol>
                </div>
                <?php endif; ?>
                
                <?php if (empty($apiKey)): ?>
                <div class="mb-3">
                    <strong>Problem: API Key is empty/missing</strong>
                    <p class="mb-0">The Gemini API key is not saved in the database.</p>
                </div>
                <div class="alert alert-warning">
                    <strong>🔧 Fix:</strong>
                    <ol class="mb-0">
                        <li>Get your API key from <a href="https://makersuite.google.com/app/apikey" target="_blank" class="alert-link">Google AI Studio</a></li>
                        <li>Go to <a href="<?= BASE_URL ?>/admin/settings.php" class="alert-link">Admin → Settings</a></li>
                        <li>Paste the API key in <strong>"Google Gemini API Key"</strong> field</li>
                        <li>Click <strong>"Save AI Settings"</strong></li>
                        <li>Test the key with <a href="<?= BASE_URL ?>/test-gemini-key.php" class="alert-link">test-gemini-key.php</a></li>
                    </ol>
                </div>
                <?php endif; ?>
            </div>
            <?php endif; ?>
            
        </div>
        <div class="card-footer">
            <div class="d-flex gap-2">
                <a href="<?= BASE_URL ?>/admin/settings.php" class="btn btn-primary">
                    <i class="bi bi-gear me-2"></i>Admin Settings
                </a>
                <a href="<?= BASE_URL ?>/test-gemini-key.php" class="btn btn-info">
                    <i class="bi bi-key me-2"></i>Test API Key
                </a>
                <button onclick="location.reload()" class="btn btn-success">
                    <i class="bi bi-arrow-clockwise me-2"></i>Refresh Test
                </button>
                <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">
                    <i class="bi bi-house-door me-2"></i>Dashboard
                </a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
