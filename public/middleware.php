<?php
require_once __DIR__ . '/../config/database.php';
require_once 'session.php';

class Middleware {
    /**
     * Check if user is authenticated
     */
    public static function auth() {
        Session::start();
        
        if (!isset($_SESSION['user_id'])) {
            set_flash_message('Please login to access this page.', 'warning');
            redirect('login.php');
        }
        
        // Check if session is valid in database
        $conn = connect_db();
        $stmt = $conn->prepare("SELECT * FROM sessions WHERE user_id = ? AND session_id = ? AND expires_at > NOW()");
        $stmt->execute([$_SESSION['user_id'], session_id()]);
        
        if ($stmt->rowCount() === 0) {
            // Invalid session, destroy it
            Session::destroy();
            set_flash_message('Your session has expired. Please login again.', 'warning');
            redirect('login.php');
        }
    }
    
    /**
     * Check if user has admin role
     */
    public static function admin() {
        self::auth(); // First check if authenticated
        
        // Check if user is admin
        $conn = connect_db();
        $stmt = $conn->prepare("SELECT role FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || $user['role'] !== 'admin') {
            set_flash_message('You do not have permission to access this page.', 'danger');
            redirect('dashboard.php');
        }
    }
    
    /**
     * Rate limiting middleware
     */
    public static function rateLimit($max_requests = 10, $time_window = 60) {
        $ip = $_SERVER['REMOTE_ADDR'];
        $conn = connect_db();
        
        // Clean up old entries
        $stmt = $conn->prepare("DELETE FROM rate_limits WHERE expires_at < NOW()");
        $stmt->execute();
        
        // Check current rate
        $stmt = $conn->prepare("SELECT COUNT(*) as count FROM rate_limits WHERE ip_address = ?");
        $stmt->execute([$ip]);
        $count = $stmt->fetchColumn();
        
        if ($count >= $max_requests) {
            http_response_code(429);
            die('Too many requests. Please try again later.');
        }
        
        // Record this request
        $stmt = $conn->prepare("INSERT INTO rate_limits (ip_address, expires_at) VALUES (?, DATE_ADD(NOW(), INTERVAL ? SECOND))");
        $stmt->execute([$ip, $time_window]);
    }
    
    /**
     * CSRF protection middleware
     */
    public static function csrf() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (!isset($_POST['csrf_token']) || !verify_csrf_token($_POST['csrf_token'])) {
                http_response_code(403);
                die('CSRF token validation failed.');
            }
        }
    }
    
    /**
     * Execute Python helper function
     */
    public static function pythonHelper($function, $params = []) {
        $python_script = __DIR__ . '/../python/auth_helper.py';
        
        // Prepare the command with JSON encoded parameters
        $json_params = escapeshellarg(json_encode($params));
        $command = "python3 {$python_script} {$function} {$json_params}";
        
        // Execute the Python script
        $output = shell_exec($command);
        
        if ($output === null) {
            return ['error' => 'Failed to execute Python helper'];
        }
        
        // Parse the JSON output
        $result = json_decode($output, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            return ['error' => 'Invalid response from Python helper'];
        }
        
        return $result;
    }
}

function create_rate_limits_table() {
    $conn = connect_db();
    $query = "
    CREATE TABLE IF NOT EXISTS rate_limits (
        id INT AUTO_INCREMENT PRIMARY KEY,
        ip_address VARCHAR(45) NOT NULL,
        expires_at TIMESTAMP NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        INDEX (ip_address),
        INDEX (expires_at)
    )";
    $conn->exec($query);
}

// Initialize rate limits table
create_rate_limits_table();
?>