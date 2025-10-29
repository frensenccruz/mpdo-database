<?php
session_start();

// IMPORTANT: Turn OFF error display for production API calls
// Errors should be logged, not displayed
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
    strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
    // This is an AJAX/API request - don't show HTML errors
    ini_set('display_errors', 0);
    error_reporting(E_ALL);
} else {
    // Regular page request - can show errors for debugging
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// Auto-detect base path
$base_path = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
if ($base_path === '/') {
    $base_path = '';
}
define('BASE_PATH', $base_path);

// Database configuration
$host = '192.168.130.189';
$dbname = 'mpdo_db';
$username = 'mpdo_admin';
$password = 'Mpdo1Limay@';

// Try to connect
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Log the error
    error_log("Database connection failed: " . $e->getMessage());
    
    // For API requests, return JSON error
    if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) || 
        strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        header('Content-Type: application/json');
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'error' => 'Database connection failed'
        ]);
        exit();
    }
    
    // For regular pages, show user-friendly error
    die("
    <html>
    <head>
        <title>Database Error</title>
        <style>
            body { font-family: Arial; padding: 50px; background: #f5f5f5; }
            .error-box {
                background: white;
                padding: 30px;
                border-radius: 10px;
                box-shadow: 0 2px 10px rgba(0,0,0,0.1);
                max-width: 600px;
                margin: 0 auto;
            }
            h2 { color: #d32f2f; }
            .error-msg {
                background: #ffebee;
                padding: 15px;
                border-left: 4px solid #d32f2f;
                margin: 20px 0;
            }
        </style>
    </head>
    <body>
        <div class='error-box'>
            <h2>❌ Database Connection Failed</h2>
            <div class='error-msg'>
                Unable to connect to the database. Please check your configuration.
            </div>
            <p><strong>What to do:</strong></p>
            <ol>
                <li>Check if MySQL is running</li>
                <li>Verify database credentials in config.php</li>
                <li>Ensure the database 'mpdo_db' exists</li>
            </ol>
        </div>
    </body>
    </html>
    ");
}

// CSRF Token Generation
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function validateCSRF($token) {
    if (!isset($_SESSION['csrf_token']) || empty($token)) {
        return false;
    }
    return hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect to login if not logged in (skip for API endpoints)
$public_pages = ['index.php', 'logout.php'];
$current_page = basename($_SERVER['PHP_SELF']);

if (!isLoggedIn() && 
    !in_array($current_page, $public_pages) && 
    strpos($_SERVER['REQUEST_URI'], '/api/') === false) {
    header('Location: ' . BASE_PATH . '/index.php');
    exit();
}
?>