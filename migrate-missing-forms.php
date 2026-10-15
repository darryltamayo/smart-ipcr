<?php
/**
 * Migration Script: Create Missing IPCR Forms for Open Periods
 * 
 * This script creates IPCR forms for faculty who should have forms
 * but don't (e.g., periods opened before the auto-creation fix).
 * 
 * USE CASE:
 * - Existing open periods that were opened before the fix
 * - Faculty added after period was opened
 * - Any edge case where auto-creation failed
 * 
 * SAFE TO RUN MULTIPLE TIMES - Will not create duplicates
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();
requireRole('admin');

$db = getDB();
$dryRun = isset($_GET['dry_run']) && $_GET['dry_run'] === '1';

// Get all open or rating periods
$periods = $db->query("
    SELECT id, name, template_id, status 
    FROM rating_periods 
    WHERE status IN ('open', 'rating')
    ORDER BY created_at DESC
")->fetchAll();

$results = [];
$totalCreated = 0;

foreach ($periods as $period) {
    if (!$period['template_id']) {
        $results[] = [
            'period' => $period['name'],
            'status' => 'skipped',
            'reason' => 'No template assigned',
            'created' => 0
        ];
        continue;
    }
    
    // Get all eligible faculty
    $faculty = $db->query("
        SELECT id, employee_id, first_name, last_name, email, personal_email
        FROM users
        WHERE role = 'faculty' AND is_active = 1 AND assigned_dean_id IS NOT NULL
    ")->fetchAll();
    
    $createdForPeriod = 0;
    $skippedCount = 0;
    
    foreach ($faculty as $fac) {
        // Check if form already exists
        $existingForm = $db->prepare("SELECT id FROM ipcr_forms WHERE user_id=? AND period_id=?");
        $existingForm->execute([$fac['id'], $period['id']]);
        
        if ($existingForm->fetch()) {
            $skippedCount++;
            continue; // Already has form, skip
        }
        
        if (!$dryRun) {
            // Create the form
            $db->prepare("INSERT INTO ipcr_forms (period_id, user_id, template_id, status, created_at) 
                         VALUES (?,?,?,'draft',NOW())")
               ->execute([$period['id'], $fac['id'], $period['template_id']]);
            
            $newFormId = $db->lastInsertId();
            
            // Log form creation
            $db->prepare("INSERT INTO form_history (form_id, action, done_by, remarks) 
                         VALUES (?,?,?,?)")
               ->execute([$newFormId, 'created', $_SESSION['user_id'], 'IPCR form created via migration script.']);
            
            auditLog('created', 'ipcr_form', $newFormId, null, 'draft', 
                    "IPCR form created via migration for faculty {$fac['employee_id']}");
        }
        
        $createdForPeriod++;
    }
    
    $results[] = [
        'period' => $period['name'],
        'status' => 'processed',
        'created' => $createdForPeriod,
        'skipped' => $skippedCount,
        'total_faculty' => count($faculty)
    ];
    
    $totalCreated += $createdForPeriod;
}

// HTML Output
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Migration: Create Missing Forms</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
<div class="container mt-5">
    <div class="card">
        <div class="card-header <?= $dryRun ? 'bg-warning text-dark' : 'bg-success text-white' ?>">
            <h3>
                <i class="bi bi-database-fill-add me-2"></i>
                Migration: Create Missing IPCR Forms
                <?php if ($dryRun): ?>
                    <span class="badge bg-dark ms-2">DRY RUN MODE</span>
                <?php endif; ?>
            </h3>
        </div>
        <div class="card-body">
            
            <?php if ($dryRun): ?>
            <div class="alert alert-warning">
                <h5><i class="bi bi-info-circle me-2"></i>Dry Run Mode</h5>
                <p class="mb-2">This is a preview. No database changes were made.</p>
                <p class="mb-0">
                    <strong>To actually create the forms, run without dry_run:</strong><br>
                    <code><?= $_SERVER['REQUEST_SCHEME'] ?>://<?= $_SERVER['HTTP_HOST'] ?><?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?></code>
                </p>
            </div>
            <?php endif; ?>
            
            <?php if (!$periods): ?>
            <div class="alert alert-info">
                <i class="bi bi-info-circle me-2"></i>
                No open or rating periods found. Nothing to migrate.
            </div>
            <?php else: ?>
            
            <div class="alert alert-<?= $totalCreated > 0 ? 'success' : 'info' ?> mb-4">
                <h5>
                    <?= $totalCreated > 0 ? '✅' : 'ℹ️' ?>
                    Summary: <?= $totalCreated ?> form(s) <?= $dryRun ? 'would be' : 'were' ?> created
                </h5>
                <p class="mb-0">
                    Processed <?= count($results) ?> open/rating period(s)
                </p>
            </div>
            
            <h5>Detailed Results by Period:</h5>
            
            <?php foreach ($results as $result): ?>
            <div class="card mb-3 border-<?= $result['status'] === 'skipped' ? 'warning' : 'success' ?>">
                <div class="card-header bg-<?= $result['status'] === 'skipped' ? 'warning' : 'light' ?>">
                    <strong><?= htmlspecialchars($result['period']) ?></strong>
                </div>
                <div class="card-body">
                    <?php if ($result['status'] === 'skipped'): ?>
                        <div class="text-warning">
                            <i class="bi bi-exclamation-triangle me-2"></i>
                            <strong>Skipped:</strong> <?= htmlspecialchars($result['reason']) ?>
                        </div>
                    <?php else: ?>
                        <div class="row">
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-6 text-<?= $result['created'] > 0 ? 'success' : 'secondary' ?>">
                                        <?= $result['created'] ?>
                                    </div>
                                    <small class="text-muted">Forms <?= $dryRun ? 'to Create' : 'Created' ?></small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-6 text-info">
                                        <?= $result['skipped'] ?>
                                    </div>
                                    <small class="text-muted">Already Have Forms</small>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="text-center p-3 bg-light rounded">
                                    <div class="display-6 text-primary">
                                        <?= $result['total_faculty'] ?>
                                    </div>
                                    <small class="text-muted">Total Eligible Faculty</small>
                                </div>
                            </div>
                        </div>
                        
                        <?php if ($result['created'] > 0): ?>
                        <div class="alert alert-success mt-3 mb-0">
                            <i class="bi bi-check-circle me-2"></i>
                            <?= $dryRun ? 'Would create' : 'Created' ?> <?= $result['created'] ?> missing form(s) for this period
                        </div>
                        <?php elseif ($result['skipped'] === $result['total_faculty']): ?>
                        <div class="alert alert-info mt-3 mb-0">
                            <i class="bi bi-info-circle me-2"></i>
                            All faculty already have forms for this period. Nothing to do!
                        </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>
            
            <?php endif; ?>
            
        </div>
        <div class="card-footer">
            <div class="d-flex gap-2 justify-content-between align-items-center">
                <div>
                    <?php if ($dryRun): ?>
                    <a href="<?= parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?>" 
                       class="btn btn-success"
                       onclick="return confirm('⚠️ This will create <?= $totalCreated ?> IPCR form(s).\n\nProceed?')">
                        <i class="bi bi-check-circle me-2"></i>
                        Run Migration (Create Forms)
                    </a>
                    <?php endif; ?>
                    
                    <a href="<?= BASE_URL ?>/test-period-opening.php" class="btn btn-info">
                        <i class="bi bi-clipboard-check me-2"></i>
                        Verify Results
                    </a>
                </div>
                
                <div>
                    <a href="<?= BASE_URL ?>/admin/periods.php" class="btn btn-primary">
                        <i class="bi bi-calendar3 me-2"></i>
                        Periods
                    </a>
                    <a href="<?= BASE_URL ?>/dashboard.php" class="btn btn-secondary">
                        <i class="bi bi-house-door me-2"></i>
                        Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <div class="card mt-4">
        <div class="card-header bg-light">
            <h6><i class="bi bi-info-circle me-2"></i>About This Migration</h6>
        </div>
        <div class="card-body">
            <p><strong>What does this script do?</strong></p>
            <ul>
                <li>Finds all open or rating periods</li>
                <li>For each period, finds eligible faculty (active, role=faculty, has assigned dean)</li>
                <li>Creates missing IPCR forms for faculty who don't have one yet</li>
                <li>Logs all changes to audit log and form history</li>
            </ul>
            
            <p><strong>When should you run this?</strong></p>
            <ul>
                <li>After upgrading to the new auto-creation system</li>
                <li>If faculty were added after a period was opened</li>
                <li>If you suspect some forms are missing</li>
            </ul>
            
            <p class="mb-0"><strong>Is it safe to run multiple times?</strong></p>
            <p class="text-success mb-0">
                <i class="bi bi-shield-check me-1"></i>
                Yes! The script checks for existing forms and will not create duplicates.
            </p>
        </div>
    </div>
</div>
</body>
</html>
