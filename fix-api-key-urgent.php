<?php
/**
 * URGENT FIX: Check and Fix API Key Issue
 * This will show EXACTLY why API key is not being retrieved
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();
requireRole('admin');

$db = getDB();
$fixed = false;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fix_now'])) {
    $apiKey = trim($_POST['api_key'] ?? '');
    
    if (empty($apiKey)) {
        $error = "API Key cannot be empty!";
    } else {
        try {
            // Method 1: Delete and insert fresh
            $db->exec("DELETE FROM system_config WHERE config_key = 'gemini_api_key'");
            $db->exec("DELETE FROM system_config WHERE config_key = 'ai_verification_mode'");
            
            // Insert fresh with exact values
            $stmt = $db->prepare("INSERT INTO system_config (config_key, config_value, updated_at) VALUES (?, ?, NOW())");
            $stmt->execute(['ai_verification_mode', 'advisory']);
            $stmt->execute(['gemini_api_key', $apiKey]);
            
            $fixed = true;
            
        } catch (Exception $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}

// Test 1: Direct SELECT
$test1 = $db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn();

// Test 2: Fetch as array
$test2Result = $db->query("SELECT config_key, config_value FROM system_config WHERE config_key IN ('ai_verification_mode', 'gemini_api_key')");
$test2 = $test2Result->fetchAll(PDO::FETCH_KEY_PAIR);

// Test 3: Raw query
$test3 = $db->query("SELECT * FROM system_config WHERE config_key IN ('ai_verification_mode', 'gemini_api_key')")->fetchAll();

// Test 4: Count rows
$test4 = $db->query("SELECT COUNT(*) FROM system_config WHERE config_key IN ('ai_verification_mode', 'gemini_api_key')")->fetchColumn();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>URGENT: Fix API Key</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card border-danger">
        <div class="card-header bg-danger text-white">
            <h3>🚨 URGENT: API Key Diagnostic & Fix</h3>
        </div>
        <div class="card-body">
            
            <?php if ($fixed): ?>
            <div class="alert alert-success">
                <h5>✅ API Key Re-inserted Successfully!</h5>
                <p class="mb-0">The API key has been deleted and re-inserted fresh. Test upload now.</p>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger">
                <h5>❌ Error</h5>
                <p class="mb-0"><?= htmlspecialchars($error) ?></p>
            </div>
            <?php endif; ?>
            
            <h5 class="mb-3">🔍 Database Query Tests</h5>
            
            <!-- Test 1 -->
            <div class="card mb-3 border-<?= $test1 ? 'success' : 'danger' ?>">
                <div class="card-header">
                    <strong>Test 1: fetchColumn() - What upload-evidence.php uses</strong>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-2"><code>$db->query("SELECT config_value FROM system_config WHERE config_key='gemini_api_key'")->fetchColumn()</code></pre>
                    <div class="mt-2">
                        <strong>Result:</strong>
                        <?php if ($test1 === false): ?>
                            <code class="text-danger">FALSE (not found)</code>
                        <?php elseif ($test1 === null): ?>
                            <code class="text-danger">NULL</code>
                        <?php elseif (empty($test1)): ?>
                            <code class="text-danger">'' (empty string)</code>
                        <?php else: ?>
                            <code class="text-success"><?= htmlspecialchars(substr($test1, 0, 30)) ?>...</code>
                            <span class="badge bg-success ms-2"><?= strlen($test1) ?> characters</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Test 2 -->
            <div class="card mb-3 border-<?= isset($test2['gemini_api_key']) ? 'success' : 'danger' ?>">
                <div class="card-header">
                    <strong>Test 2: fetchAll(FETCH_KEY_PAIR)</strong>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-2"><code>$db->query("...")->fetchAll(PDO::FETCH_KEY_PAIR)</code></pre>
                    <div class="mt-2">
                        <strong>Result:</strong>
                        <pre><?= htmlspecialchars(print_r($test2, true)) ?></pre>
                    </div>
                </div>
            </div>
            
            <!-- Test 3 -->
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Test 3: Raw fetchAll() - All Columns</strong>
                </div>
                <div class="card-body">
                    <pre class="bg-light p-2"><code>SELECT * FROM system_config WHERE config_key IN (...)</code></pre>
                    <div class="mt-2">
                        <strong>Rows found:</strong> <?= count($test3) ?>
                        <pre><?= htmlspecialchars(print_r($test3, true)) ?></pre>
                    </div>
                </div>
            </div>
            
            <!-- Test 4 -->
            <div class="card mb-3">
                <div class="card-header">
                    <strong>Test 4: Count Check</strong>
                </div>
                <div class="card-body">
                    <p class="mb-0"><strong>Rows in database:</strong> <?= $test4 ?></p>
                    <?php if ($test4 == 0): ?>
                        <div class="alert alert-danger mt-2 mb-0">
                            ❌ NO ROWS FOUND! The settings are missing from the database!
                        </div>
                    <?php elseif ($test4 == 1): ?>
                        <div class="alert alert-warning mt-2 mb-0">
                            ⚠️ Only 1 row found. Both ai_verification_mode and gemini_api_key should exist.
                        </div>
                    <?php else: ?>
                        <div class="alert alert-success mt-2 mb-0">
                            ✅ Both rows exist in database.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <hr>
            
            <h5 class="mb-3">🔧 FORCE FIX</h5>
            
            <div class="alert alert-warning">
                <h6><i class="bi bi-exclamation-triangle me-2"></i>What this does:</h6>
                <ol class="mb-0">
                    <li><strong>DELETE</strong> existing gemini_api_key and ai_verification_mode rows</li>
                    <li><strong>INSERT</strong> fresh rows with clean values</li>
                    <li>This fixes any corruption or encoding issues</li>
                </ol>
            </div>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label"><strong>Paste Your Gemini API Key</strong></label>
                    <input type="text" 
                           name="api_key" 
                           class="form-control font-monospace" 
                           value="<?= htmlspecialchars($test1 ?: '') ?>" 
                           placeholder="AIzaSy..." 
                           required>
                    <small class="text-muted">Get from: <a href="https://makersuite.google.com/app/apikey" target="_blank">Google AI Studio</a></small>
                </div>
                
                <button type="submit" name="fix_now" class="btn btn-danger btn-lg w-100">
                    <i class="bi bi-arrow-repeat me-2"></i>DELETE & RE-INSERT API KEY (Force Fix)
                </button>
            </form>
            
        </div>
        <div class="card-footer">
            <a href="<?= BASE_URL ?>/test-upload-live.php" class="btn btn-primary">
                <i class="bi bi-upload me-2"></i>Test Upload Again
            </a>
            <a href="<?= BASE_URL ?>/test-upload-ai-check.php" class="btn btn-info">
                <i class="bi bi-clipboard-check me-2"></i>Check Settings
            </a>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
