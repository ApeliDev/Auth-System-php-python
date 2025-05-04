
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Authentication System</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100 font-sans">
    <nav class="bg-white shadow-md">
        <div class="container mx-auto px-6 py-3 flex justify-between items-center">
            <a href="dashboard.php" class="text-xl font-bold text-gray-800">Auth System</a>
            <div class="flex items-center">
                <span class="mr-4 text-gray-700">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="?logout=true" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-md">Logout</a>
            </div>
        </div>
    </nav>

    <div class="container mx-auto px-4 py-8">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-2xl font-bold mb-6">Dashboard</h1>
            
            <?php echo flash_message(); ?>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">User Information</h2>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p><strong>Username:</strong> <?php echo htmlspecialchars($user['username']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                    <p><strong>Account Created:</strong> <?php echo date('F j, Y, g:i a', strtotime($user['created_at'])); ?></p>
                    <?php if ($user['last_login']): ?>
                        <p><strong>Last Login:</strong> <?php echo date('F j, Y, g:i a', strtotime($user['last_login'])); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="mb-6">
                <h2 class="text-xl font-semibold mb-2">Protected Content</h2>
                <div class="bg-gray-50 p-4 rounded-md">
                    <p>This is protected content that only logged-in users can see. You can add your application's secure content here.</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>