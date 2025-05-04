<?php
require_once __DIR__ . '/../config/database.php';
require_once 'functions.php';
require_once 'session.php';

class Auth {
    private $db;
    
    public function __construct() {
        $this->db = connect_db();
    }
    
    /**
     * Register a new user
     */
    public function register($username, $email, $password) {
        // Validate input
        if (empty($username) || empty($email) || empty($password)) {
            return ['success' => false, 'message' => 'All fields are required'];
        }
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Check if user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        
        if ($stmt->rowCount() > 0) {
            return ['success' => false, 'message' => 'Username or email already exists'];
        }
        
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Insert user
        try {
            $stmt = $this->db->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
            $result = $stmt->execute([$username, $email, $hashed_password]);
            
            if ($result) {
                return ['success' => true, 'message' => 'Registration successful'];
            } else {
                return ['success' => false, 'message' => 'Registration failed'];
            }
        } catch (PDOException $e) {
            error_log("Registration Error: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during registration'];
        }
    }
    
    /**
     * Login user
     */
    public function login($username, $password, $remember = false) {
        // Validate input
        if (empty($username) || empty($password)) {
            return ['success' => false, 'message' => 'Username and password are required'];
        }
        
        // Record login attempt
        $this->recordLoginAttempt($username);
        
        // Check if too many failed attempts
        if ($this->isIpBlocked($_SERVER['REMOTE_ADDR'])) {
            return ['success' => false, 'message' => 'Too many failed login attempts. Please try again later.'];
        }
        
        // Get user
        $stmt = $this->db->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user || !password_verify($password, $user['password'])) {
            return ['success' => false, 'message' => 'Invalid username or password'];
        }
        
        if (!$user['is_active']) {
            return ['success' => false, 'message' => 'This account has been deactivated'];
        }
        
        // Update last login
        $update = $this->db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        $update->execute([$user['id']]);
        
        // Start session
        Session::start();
        Session::set('user_id', $user['id']);
        Session::set('username', $user['username']);
        
        // Create session record
        $session_id = session_id();
        $expires = $remember ? date('Y-m-d H:i:s', time() + 30 * 24 * 60 * 60) : date('Y-m-d H:i:s', time() + 2 * 60 * 60);
        
        $stmt = $this->db->prepare("INSERT INTO sessions (user_id, session_id, expires_at, ip_address, user_agent) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $user['id'], 
            $session_id, 
            $expires, 
            $_SERVER['REMOTE_ADDR'], 
            $_SERVER['HTTP_USER_AGENT'] ?? ''
        ]);
        
        return ['success' => true, 'message' => 'Login successful', 'user' => $user];
    }
    
    /**
     * Logout user
     */
    public function logout() {
        $session_id = session_id();
        
        // Delete session from database
        $stmt = $this->db->prepare("DELETE FROM sessions WHERE session_id = ?");
        $stmt->execute([$session_id]);
        
        // End session
        Session::destroy();
        
        return ['success' => true, 'message' => 'Logout successful'];
    }
    
    /**
     * Request password reset
     */
    public function requestPasswordReset($email) {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return ['success' => false, 'message' => 'Invalid email format'];
        }
        
        // Check if user exists
        $stmt = $this->db->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            // Still return success to prevent email enumeration
            return ['success' => true, 'message' => 'If your email is registered, you will receive a password reset link'];
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', time() + 60 * 60); // 1 hour
        
        // Save token
        $stmt = $this->db->prepare("UPDATE users SET reset_token = ?, reset_token_expires = ? WHERE id = ?");
        $stmt->execute([$token, $expires, $user['id']]);
        
        // Send email (implement your email sending logic here)
        $reset_link = "http://" . $_SERVER['HTTP_HOST'] . "/reset-password.php?token=" . $token;
        
        // For demonstration purposes only - in a real app, use a proper email sending method
        error_log("Password Reset Link for {$email}: {$reset_link}");
        
        return ['success' => true, 'message' => 'If your email is registered, you will receive a password reset link'];
    }
    
    /**
     * Reset password
     */
    public function resetPassword($token, $new_password) {
        if (empty($token) || empty($new_password)) {
            return ['success' => false, 'message' => 'Token and new password are required'];
        }
        
        // Validate token
        $stmt = $this->db->prepare("SELECT * FROM users WHERE reset_token = ? AND reset_token_expires > NOW()");
        $stmt->execute([$token]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return ['success' => false, 'message' => 'Invalid or expired reset token'];
        }
        
        // Hash new password
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        
        // Update password and clear token
        $stmt = $this->db->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expires = NULL WHERE id = ?");
        $result = $stmt->execute([$hashed_password, $user['id']]);
        
        if ($result) {
            return ['success' => true, 'message' => 'Password has been reset successfully'];
        } else {
            return ['success' => false, 'message' => 'Password reset failed'];
        }
    }
    
    /**
     * Record login attempt
     */
    private function recordLoginAttempt($username) {
        $stmt = $this->db->prepare("INSERT INTO login_attempts (username, ip_address) VALUES (?, ?)");
        $stmt->execute([$username, $_SERVER['REMOTE_ADDR']]);
    }
    
    /**
     * Check if IP is blocked due to too many attempts
     */
    private function isIpBlocked($ip) {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM login_attempts WHERE ip_address = ? AND attempted_at > DATE_SUB(NOW(), INTERVAL 10 MINUTE)");
        $stmt->execute([$ip]);
        $count = $stmt->fetchColumn();
        
        return $count > 5; // Block after 5 failed attempts in 10 minutes
    }
    
    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return Session::get('user_id') !== null;
    }
    
    /**
     * Get current user data
     */
    public function getCurrentUser() {
        $user_id = Session::get('user_id');
        
        if (!$user_id) {
            return null;
        }
        
        $stmt = $this->db->prepare("SELECT id, username, email, created_at, last_login FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>