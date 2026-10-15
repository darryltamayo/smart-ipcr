<?php
/**
 * Database Configuration Template
 * 
 * Copy this file to database.php and update with your actual credentials
 * DO NOT commit database.php to version control
 */

// Database Connection Settings
define('DB_HOST', 'localhost');              // Database host (usually 'localhost')
define('DB_USER', 'your_database_user');     // Database username
define('DB_PASS', 'your_database_password'); // Database password
define('DB_NAME', 'ipcr_system');            // Database name
define('DB_CHARSET', 'utf8mb4');             // Character set (don't change)

/**
 * Get PDO database connection
 * 
 * @return PDO Database connection instance
 */
function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            // Log error and show user-friendly message
            error_log('Database connection failed: ' . $e->getMessage());
            die(json_encode(['error' => 'Database connection failed. Please check configuration.']));
        }
    }
    return $pdo;
}
