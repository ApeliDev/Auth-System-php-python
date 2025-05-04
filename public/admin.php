<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/middleware.php';

Session::start();
Middleware::admin(); // Only allow admin access

$auth = new Auth();
$user = $auth->getCurrentUser();

// Get statistics from Python helper
$stats = Middleware::pythonHelper('generate_auth_report', ['days' => 30]);
$suspicious = Middleware::pythonHelper('detect_suspicious_activity');

// Handle user actions
if (is_post() && verify_csrf_token($_POST['csrf_token'] ?? '')) {
    $action = $_POST['action'] ?? '';
    $user_id = (int)($_POST['user_id'] ?? 0);
    
    switch ($action) {
        case 'deactivate':
            $conn = connect_db();
            $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $stmt->execute([$user_id]);
            set_flash_message('User has been deactivated', 'success');
            break;
            
        case 'activate':
            $conn = connect_db();
            $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            $stmt->execute([$user_id]);
            set_flash_message('User has been activated', 'success');
            break;
            
        case 'delete':
            $conn = connect_db();
            $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            set_flash_message('User has been deleted', 'success');
            break;
    }
    
    redirect('admin.php');
}

// Get all users
$conn = connect_db();
$stmt = $conn->prepare("SELECT id, username, email, created_at, last_login, is_active FROM users ORDER BY id DESC");
$stmt->execute();
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-gray-800 text-white shadow-md">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="admin.php" class="text-xl font-bold">Admin Dashboard</a>
            <div class="flex items-center">
                <a href="dashboard.php" class="mr-4 text-gray-300 hover:text-white">User Dashboard</a>
                <span class="mr-4 text-gray-300">Admin: <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="?logout=true" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Admin Dashboard</h1>
        
        <?php echo flash_message(); ?>
        
        <!-- System Statistics -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">User Statistics</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Total Users:</span> <?php echo $stats['users']['total']; ?></p>
                    <p><span class="font-medium">Active Users (30d):</span> <?php echo $stats['users']['active_last_month']; ?></p>
                    <p><span class="font-medium">New Users (30d):</span> <?php echo $stats['users']['new_last_month']; ?></p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Session Statistics</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Active Sessions:</span> <?php echo $stats['sessions']['total_active']; ?></p>
                    <p><span class="font-medium">Avg. Sessions per User:</span> <?php echo number_format($stats['sessions']['average_per_user'], 2); ?></p>
                </div>
            </div>
            
            <div class="bg-white rounded-lg shadow-md p-6">
                <h2 class="text-xl font-semibold mb-4">Login Statistics</h2>
                <div class="space-y-2">
                    <p><span class="font-medium">Total Attempts (30d):</span> <?php echo $stats['login_attempts']['total']; ?></p>
                    <p><span class="font-medium">Success Rate:</span> <?php echo $stats['login_attempts']['success_rate']; ?>%</p>
                    <p><span class="font-medium">Failure
            <form method="POST" action="<?php echo current_url(); ?>">
                <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                
                <div class="mb-4">
                    <label for="username" class="block text-gray-700 text-sm font-bold mb-2">Username or Email</label>
                    <input type="text" id="username" name="username" value="<?php echo $username; ?>" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           required>
                </div>
                
                <div class="mb-6">
                    <label for="password" class="block text-gray-700 text-sm font-bold mb-2">Password</label>
                    <input type="password" id="password" name="password" 
                           class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" 
                           required>
                </div>
                
                <div class="mb-6">
                    <label class="flex items-center">
                        <input type="checkbox" name="remember" class="form-checkbox h-4 w-4 text-blue-600">
                        <span class="ml-2 text-gray-700 text-sm">Remember me</span>
                    </label>
                </div>
                
                <div class="flex items-center justify-between">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                        Login
                    </button>
                    <a href="reset-password.php" class="inline-block align-baseline font-bold text-sm text-blue-500 hover:text-blue-700">
                        Forgot Password?
                    </a>
                </div>
            </form>
            
            <p class="mt-6 text-center text-sm">
                Don't have an account? <a href="register.php" class="text-blue-500 hover:text-blue-700">Register here</a>
            </p>
        </div>
    </div>
</body>
</html>