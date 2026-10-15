<?php
/**
 * Live Upload Test - See Real Response
 * This simulates faculty upload and shows the actual API response
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();

$db = getDB();
$currentUserId = $_SESSION['user_id'];

// Get a DRAFT or RETURNED form to test with (editable forms only)
$testForm = $db->query("
    SELECT f.id, f.period_id, f.template_id, f.status
    FROM ipcr_forms f 
    WHERE f.user_id = {$currentUserId} 
    AND f.status IN ('draft', 'returned')
    ORDER BY f.id DESC
    LIMIT 1
")->fetch();

if (!$testForm) {
    echo '<div class="container mt-5"><div class="alert alert-danger">';
    echo '<h5>❌ No Editable Forms Available</h5>';
    echo '<p>You don\'t have any DRAFT or RETURNED forms to test with.</p>';
    echo '<p><strong>Solution:</strong> Go to <a href="' . BASE_URL . '/faculty/ipcr-form.php">My IPCR Form</a> and create a draft form, or ask admin to return your form for editing.</p>';
    echo '</div></div>';
    require_once __DIR__ . '/includes/footer.php';
    exit;
}

// Get a success indicator
$testSI = $db->query("
    SELECT s.id, s.code, s.title 
    FROM success_indicators s
    JOIN kras k ON s.kra_id = k.id
    JOIN mfos m ON k.mfo_id = m.id
    WHERE m.template_id = {$testForm['template_id']}
    LIMIT 1
")->fetch();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Live Upload Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        .response-box {
            background: #1e1e1e;
            color: #d4d4d4;
            padding: 20px;
            border-radius: 8px;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            max-height: 500px;
            overflow-y: auto;
            white-space: pre-wrap;
            word-wrap: break-word;
        }
        .highlight-success { color: #4caf50; font-weight: bold; }
        .highlight-error { color: #f44336; font-weight: bold; }
        .highlight-warning { color: #ff9800; font-weight: bold; }
        .highlight-info { color: #2196f3; font-weight: bold; }
    </style>
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h3><i class="bi bi-cloud-upload me-2"></i>Live Upload Test - Real API Response</h3>
        </div>
        <div class="card-body">
            
            <div class="alert alert-info">
                <h6><i class="bi bi-info-circle me-2"></i>Test Upload to See Real Response</h6>
                <p class="mb-0">
                    Upload a test file to see EXACTLY what <code>upload-evidence.php</code> returns. 
                    This will show AI confidence, verdict, and any errors.
                </p>
            </div>
            
            <form id="uploadForm">
                <div class="mb-3">
                    <label class="form-label"><strong>Select Test File</strong></label>
                    <input type="file" class="form-control" id="testFile" required>
                    <small class="text-muted">Any file type, max 10MB</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label"><strong>Test Context</strong></label>
                    <div class="bg-light p-3 rounded">
                        <div><strong>Form ID:</strong> <?= $testForm['id'] ?></div>
                        <div><strong>Form Status:</strong> <span class="badge bg-<?= $testForm['status'] === 'draft' ? 'secondary' : 'warning' ?>"><?= ucfirst($testForm['status']) ?></span></div>
                        <div><strong>Period ID:</strong> <?= $testForm['period_id'] ?></div>
                        <div><strong>SI ID:</strong> <?= $testSI['id'] ?></div>
                        <div><strong>SI:</strong> <?= h($testSI['code']) ?> - <?= h($testSI['title']) ?></div>
                    </div>
                    <small class="text-muted">
                        <i class="bi bi-info-circle me-1"></i>
                        Using a <?= $testForm['status'] ?> form (editable)
                    </small>
                </div>
                
                <button type="submit" class="btn btn-primary btn-lg w-100" id="uploadBtn">
                    <i class="bi bi-upload me-2"></i>Upload and Test AI Verification
                </button>
            </form>
            
            <hr>
            
            <h5 class="mb-3"><i class="bi bi-terminal me-2"></i>API Response (Live)</h5>
            
            <div id="responseBox" class="response-box">
                Waiting for upload...
            </div>
            
            <div id="analysisBox" class="mt-3" style="display:none;">
                <h6><i class="bi bi-clipboard-check me-2"></i>Response Analysis</h6>
                <div id="analysisContent"></div>
            </div>
            
        </div>
        <div class="card-footer">
            <a href="<?= BASE_URL ?>/test-upload-ai-check.php" class="btn btn-info">
                <i class="bi bi-clipboard-check me-2"></i>Check Upload Settings
            </a>
            <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">
                <i class="bi bi-house-door me-2"></i>Dashboard
            </a>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?= BASE_URL ?>';
const FORM_ID = <?= $testForm['id'] ?>;
const SI_ID = <?= $testSI['id'] ?>;

document.getElementById('uploadForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const fileInput = document.getElementById('testFile');
    const file = fileInput.files[0];
    
    if (!file) {
        alert('Please select a file');
        return;
    }
    
    const uploadBtn = document.getElementById('uploadBtn');
    const responseBox = document.getElementById('responseBox');
    const analysisBox = document.getElementById('analysisBox');
    
    uploadBtn.disabled = true;
    uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Uploading...';
    responseBox.textContent = 'Uploading to server...\n\nFile: ' + file.name + '\nSize: ' + file.size + ' bytes';
    
    const formData = new FormData();
    formData.append('evidence', file);
    formData.append('form_id', FORM_ID);
    formData.append('si_id', SI_ID);
    formData.append('description', 'Test upload from live test tool');
    
    try {
        const startTime = Date.now();
        
        const res = await fetch(BASE_URL + '/api/upload-evidence.php', {
            method: 'POST',
            body: formData
        });
        
        const endTime = Date.now();
        const duration = ((endTime - startTime) / 1000).toFixed(2);
        
        const contentType = res.headers.get('content-type');
        
        responseBox.textContent = '=== HTTP RESPONSE ===\n\n';
        responseBox.textContent += 'Status: ' + res.status + ' ' + res.statusText + '\n';
        responseBox.textContent += 'Content-Type: ' + contentType + '\n';
        responseBox.textContent += 'Response Time: ' + duration + 's\n\n';
        responseBox.textContent += '=== RESPONSE BODY ===\n\n';
        
        if (contentType && contentType.includes('application/json')) {
            const data = await res.json();
            responseBox.textContent += JSON.stringify(data, null, 2);
            
            // Analysis
            analysisBox.style.display = 'block';
            let analysis = '';
            
            if (data.success) {
                analysis += '<div class="alert alert-success">';
                analysis += '<h6>✅ Upload Successful</h6>';
                analysis += '<p class="mb-0">File was uploaded to the server.</p>';
                analysis += '</div>';
                
                // AI Analysis
                const confidence = data.ai_confidence || 0;
                const verdict = data.ai_verdict || 'unknown';
                
                if (confidence === 0) {
                    analysis += '<div class="alert alert-danger">';
                    analysis += '<h6>❌ AI Verification: DISABLED</h6>';
                    analysis += '<p class="mb-0"><strong>Problem:</strong> AI did not run. Confidence = 0.</p>';
                    analysis += '<p class="mb-0"><strong>Reason:</strong> Either AI mode is "disabled" or API key is missing.</p>';
                    analysis += '</div>';
                } else {
                    if (confidence >= 80) {
                        analysis += '<div class="alert alert-success">';
                        analysis += '<h6>✅ AI Verification: HIGH CONFIDENCE</h6>';
                    } else if (confidence >= 50) {
                        analysis += '<div class="alert alert-warning">';
                        analysis += '<h6>⚠️ AI Verification: UNCERTAIN</h6>';
                    } else {
                        analysis += '<div class="alert alert-danger">';
                        analysis += '<h6>❌ AI Verification: LOW CONFIDENCE</h6>';
                    }
                    analysis += '<p class="mb-1"><strong>Verdict:</strong> ' + verdict + '</p>';
                    analysis += '<p class="mb-1"><strong>Confidence:</strong> ' + confidence + '%</p>';
                    if (data.ai_reason) {
                        analysis += '<p class="mb-1"><strong>Reason:</strong> ' + data.ai_reason + '</p>';
                    }
                    if (data.document_type) {
                        analysis += '<p class="mb-0"><strong>Document Type:</strong> ' + data.document_type + '</p>';
                    }
                    analysis += '</div>';
                }
            } else {
                analysis += '<div class="alert alert-danger">';
                analysis += '<h6>❌ Upload Failed</h6>';
                analysis += '<p class="mb-0">' + (data.message || 'Unknown error') + '</p>';
                analysis += '</div>';
            }
            
            document.getElementById('analysisContent').innerHTML = analysis;
            
        } else {
            const text = await res.text();
            responseBox.textContent += text;
            
            analysisBox.style.display = 'block';
            document.getElementById('analysisContent').innerHTML = '<div class="alert alert-danger"><h6>❌ Server Error</h6><p class="mb-0">Server returned HTML instead of JSON. This is a PHP error. Check server error logs.</p></div>';
        }
        
    } catch (error) {
        responseBox.textContent += '\n\n=== ERROR ===\n\n';
        responseBox.textContent += error.toString();
        
        analysisBox.style.display = 'block';
        document.getElementById('analysisContent').innerHTML = '<div class="alert alert-danger"><h6>❌ Network Error</h6><p class="mb-0">' + error.message + '</p></div>';
    }
    
    uploadBtn.disabled = false;
    uploadBtn.innerHTML = '<i class="bi bi-upload me-2"></i>Upload and Test AI Verification';
});
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
