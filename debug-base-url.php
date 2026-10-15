<?php
/**
 * Debug script to check BASE_URL detection
 * Access this file from different locations to verify routing works correctly
 */

require_once __DIR__ . '/config/constants.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BASE_URL Debug Info</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 40px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        h1 {
            color: #2c5f2d;
            margin-top: 0;
        }
        h2 {
            color: #555;
            border-bottom: 2px solid #2c5f2d;
            padding-bottom: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        td {
            padding: 10px;
            border-bottom: 1px solid #eee;
        }
        td:first-child {
            font-weight: bold;
            width: 250px;
            color: #666;
        }
        td:last-child {
            font-family: 'Courier New', monospace;
            background: #f9f9f9;
            word-break: break-all;
        }
        .success {
            background: #d4edda;
            color: #155724;
            padding: 15px;
            border-radius: 5px;
            border-left: 4px solid #28a745;
        }
        .test-links {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 10px;
            margin-top: 15px;
        }
        .test-links a {
            padding: 10px 15px;
            background: #2c5f2d;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            text-align: center;
            transition: background 0.3s;
        }
        .test-links a:hover {
            background: #1e4620;
        }
    </style>
</head>
<body>
    <div class="card">
        <h1>🔍 BASE_URL Debug Information</h1>
        <div class="success">
            <strong>Detected BASE_URL:</strong><br>
            <h2 style="border:none; margin:10px 0;"><?= BASE_URL ?></h2>
        </div>
    </div>

    <div class="card">
        <h2>📊 Server Variables</h2>
        <table>
            <tr>
                <td>HTTP_HOST</td>
                <td><?= $_SERVER['HTTP_HOST'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>SCRIPT_NAME</td>
                <td><?= $_SERVER['SCRIPT_NAME'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>REQUEST_URI</td>
                <td><?= $_SERVER['REQUEST_URI'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>DOCUMENT_ROOT</td>
                <td><?= $_SERVER['DOCUMENT_ROOT'] ?? 'N/A' ?></td>
            </tr>
            <tr>
                <td>HTTPS</td>
                <td><?= (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'Yes' : 'No' ?></td>
            </tr>
            <tr>
                <td>SERVER_PORT</td>
                <td><?= $_SERVER['SERVER_PORT'] ?? 'N/A' ?></td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>🧮 Calculated Values</h2>
        <table>
            <?php
            $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443 ? 'https://' : 'http://';
            $docRoot = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']);
            $appRootPath = str_replace('\\', '/', dirname(__DIR__));
            $webPath = '';
            
            if (strpos($appRootPath, $docRoot) === 0) {
                $webPath = substr($appRootPath, strlen($docRoot));
            }
            
            $webPath = '/' . trim($webPath, '/');
            if ($webPath === '/') {
                $webPath = '';
            }
            ?>
            <tr>
                <td>Protocol</td>
                <td><?= $protocol ?></td>
            </tr>
            <tr>
                <td>Document Root (normalized)</td>
                <td><?= $docRoot ?></td>
            </tr>
            <tr>
                <td>App Root Path (physical)</td>
                <td><?= $appRootPath ?></td>
            </tr>
            <tr>
                <td>Web Path (calculated)</td>
                <td><?= $webPath ?: '(root)' ?></td>
            </tr>
            <tr>
                <td>Final BASE_URL</td>
                <td><strong><?= BASE_URL ?></strong></td>
            </tr>
        </table>
    </div>

    <div class="card">
        <h2>🧪 Test Links (Click to verify routing)</h2>
        <div class="test-links">
            <a href="<?= BASE_URL ?>">Home/Login</a>
            <a href="<?= BASE_URL ?>/dashboard.php">Dashboard</a>
            <a href="<?= BASE_URL ?>/admin/users.php">Admin Users</a>
            <a href="<?= BASE_URL ?>/admin/periods.php">Admin Periods</a>
            <a href="<?= BASE_URL ?>/faculty/history.php">Faculty History</a>
            <a href="<?= BASE_URL ?>/dean/approvals.php">Dean Approvals</a>
        </div>
        <p style="margin-top: 20px; color: #666; font-size: 14px;">
            <strong>Note:</strong> Some links may require authentication. The goal is to check if the URLs are formed correctly (no double slashes).
        </p>
    </div>

    <div class="card">
        <h2>✅ What to Check</h2>
        <ul style="line-height: 1.8;">
            <li>BASE_URL should NOT have a trailing slash</li>
            <li>All test links should NOT have double slashes (//)</li>
            <li>URLs should match your actual installation path</li>
            <li>Protocol should be https:// on production, http:// on localhost</li>
        </ul>
    </div>
</body>
</html>
