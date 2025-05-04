<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Session::start();
$auth = new Auth();

// Check if user is logged in, redirect to dashboard if true
if ($auth->isLoggedIn()) {
    redirect('dashboard.php');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold text-center mb-6">Welcome to Auth System</h1>
            
            <?php echo flash_message(); ?>
            
            <div class="flex justify-center space-x-4">
                <a href="login.php" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-md">Login</a>
                <a href="register.php" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-md">Register</a>
            </div>
        </div>
    </div>
</body>
</html>