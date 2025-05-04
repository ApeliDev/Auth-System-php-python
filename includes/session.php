<?php
class Session {
    /**
     * Start session
     */
    public static function start() {
        if (session_status() === PHP_SESSION_NONE) {
            // Set secure session cookie parameters
            session_set_cookie_params([
                'lifetime' => 0,
                'path' => '/',
                'domain' => '',
                'secure' => true, // Use only with HTTPS
                'httponly' => true, // Not accessible via JavaScript
                'samesite' => 'Lax' // Protects against CSRF
            ]);
            
            session_start();
            
            // Regenerate session ID to prevent session fixation
            if (!isset($_SESSION['last_regeneration'])) {
                self::regenerate();
            } else if ($_SESSION['last_regeneration'] < time() - 1800) {
                // Regenerate session ID every 30 minutes
                self::regenerate();
            }
        }
    }
    
    /**
     * Regenerate session ID
     */
    public static function regenerate() {
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
    
    /**
     * Set session value
     */
    public static function set($key, $value) {
        $_SESSION[$key] = $value;
    }
    
    /**
     * Get session value
     */
    public static function get($key) {
        return $_SESSION[$key] ?? null;
    }
    
    /**
     * Delete session value
     */
    public static function delete($key) {
        if (isset($_SESSION[$key])) {
            unset($_SESSION[$key]);
            return true;
        }
        return false;
    }
    
    /**
     * Destroy session
     */
    public static function destroy() {
        session_unset();
        session_destroy();
    }
}
?>