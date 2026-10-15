<?php
/**
 * Test Period Opening - Verify Auto-Creation of IPCR Forms
 * 
 * This script tests if IPCR forms are automatically created when a period is opened.
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/config/auth.php';
requireLogin();
requireRole('admin', 'dean');

$db = getDB();

echo "<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Test Period Opening</title>
    <link href='https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css' rel='stylesheet'>
    <link rel='stylesheet' href='https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css'>
</head>
<body class='bg-light'>
<div class='container mt-5'>
    <div class='card'>
        <div class='card-header bg-primary text-white'>
            <h3><i class='bi bi-clipboard-check me-2'></i>Period Opening Test Results</h3>
        </div>
        <div class='card-body'>";

// Get active period
$period = $db->query("SELECT * FROM rating_periods WHERE status IN ('open','rating') ORDER BY id DESC LIMIT 1")->fetch();

if (!$period) {
    echo "<div class='alert alert-warning'>
            <i class='bi bi-exclamation-triangle me-2'></i>
            No active period found. Please open a period first.
          </div>";
} else {
    echo "<div class='alert alert-info'>
            <strong>Active Period:</strong> {$period['name']}<br>
            <strong>Status:</strong> {$period['status']}<br>
            <strong>Template ID:</strong> " . ($period['template_id'] ?: '<span class="text-danger">None</span>') . "
          </div>";
    
    // Get all active faculty
    $faculty = $db->query("
        SELECT id, employee_id, first_name, last_name, assigned_dean_id
        FROM users
        WHERE role = 'faculty' AND is_active = 1
        ORDER BY last_name, first_name
    ")->fetchAll();
    
    echo "<h5 class='mt-4'>Faculty Status for Period: {$period['name']}</h5>";
    
    if (!$faculty) {
        echo "<div class='alert alert-warning'>No active faculty found in the system.</div>";
    } else {
        echo "<table class='table table-bordered table-striped'>
                <thead class='table-dark'>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Has Dean?</th>
                        <th>IPCR Form Status</th>
                        <th>Form ID</th>
                        <th>Created At</th>
                    </tr>
                </thead>
                <tbody>";
        
        $withForms = 0;
        $withoutForms = 0;
        $noDean = 0;
        
        foreach ($faculty as $fac) {
            $formCheck = $db->prepare("SELECT id, status, created_at FROM ipcr_forms WHERE user_id = ? AND period_id = ?");
            $formCheck->execute([$fac['id'], $period['id']]);
            $form = $formCheck->fetch();
            
            $hasDean = $fac['assigned_dean_id'] ? true : false;
            
            if (!$hasDean) {
                $noDean++;
                $statusBadge = '<span class="badge bg-secondary">No Dean Assigned</span>';
                $formId = '—';
                $createdAt = '—';
            } elseif ($form) {
                $withForms++;
                $statusBadge = '<span class="badge bg-success"><i class="bi bi-check-circle"></i> Form Created</span>';
                $formId = $form['id'];
                $createdAt = date('M d, Y H:i:s', strtotime($form['created_at']));
            } else {
                $withoutForms++;
                $statusBadge = '<span class="badge bg-danger"><i class="bi bi-x-circle"></i> No Form</span>';
                $formId = '—';
                $createdAt = '—';
            }
            
            $deanIcon = $hasDean ? '<i class="bi bi-check-circle-fill text-success"></i>' : '<i class="bi bi-x-circle-fill text-danger"></i>';
            
            echo "<tr>
                    <td>{$fac['employee_id']}</td>
                    <td>{$fac['first_name']} {$fac['last_name']}</td>
                    <td class='text-center'>{$deanIcon}</td>
                    <td>{$statusBadge}</td>
                    <td>{$formId}</td>
                    <td><small>{$createdAt}</small></td>
                  </tr>";
        }
        
        echo "</tbody></table>";
        
        // Summary
        echo "<div class='row g-3 mt-3'>
                <div class='col-md-4'>
                    <div class='card border-success'>
                        <div class='card-body text-center'>
                            <h2 class='text-success'>{$withForms}</h2>
                            <p class='mb-0'>Faculty with Forms</p>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card border-danger'>
                        <div class='card-body text-center'>
                            <h2 class='text-danger'>{$withoutForms}</h2>
                            <p class='mb-0'>Faculty without Forms<br><small class='text-muted'>(Should have dean assigned)</small></p>
                        </div>
                    </div>
                </div>
                <div class='col-md-4'>
                    <div class='card border-secondary'>
                        <div class='card-body text-center'>
                            <h2 class='text-secondary'>{$noDean}</h2>
                            <p class='mb-0'>No Dean Assigned<br><small class='text-muted'>(Expected)</small></p>
                        </div>
                    </div>
                </div>
              </div>";
        
        // Test Result
        echo "<div class='alert mt-4 " . ($withoutForms == 0 ? 'alert-success' : 'alert-danger') . "'>";
        if ($withoutForms == 0) {
            echo "<h5><i class='bi bi-check-circle-fill me-2'></i>✅ TEST PASSED!</h5>
                  <p class='mb-0'>All active faculty with assigned deans have IPCR forms created for this period.</p>";
        } else {
            echo "<h5><i class='bi bi-x-circle-fill me-2'></i>❌ TEST FAILED!</h5>
                  <p class='mb-0'>{$withoutForms} faculty member(s) with assigned dean(s) are missing IPCR forms.</p>
                  <p class='mt-2 mb-0'><strong>Action Required:</strong> Check the period opening logic in <code>admin/periods.php</code></p>";
        }
        echo "</div>";
    }
}

echo "        </div>
        <div class='card-footer'>
            <a href='" . BASE_URL . "/admin/periods.php' class='btn btn-primary'>
                <i class='bi bi-arrow-left me-2'></i>Back to Periods
            </a>
            <a href='" . BASE_URL . "/dashboard.php' class='btn btn-secondary'>
                <i class='bi bi-house-door me-2'></i>Dashboard
            </a>
        </div>
    </div>
</div>
</body>
</html>";
?>
