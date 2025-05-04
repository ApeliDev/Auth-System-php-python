<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Session::start();
$auth = new Auth();

// Check if user is already logged in
if ($auth->isLoggedIn()) {
    redirect('dashboard.php');
}

$errors = [];
$username = '';
$email = '';

// Process registration form
if (is_post()) {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid form submission.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitize($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        
        // Validate password match
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match.';
        } elseif (strlen($password) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        } else {
            $result = $auth->register($username, $email, $password);
            
            if ($result['success']) {
                set_flash_message('Registration successful! You can now login.', 'success');
                redirect('login.php');
            } else {
                $errors[] = $result['message'];
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Auth System</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #6366f1;
            --primary-hover: #4f46e5;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            color: #111827;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .register-container {
            max-width: 420px;
            width: 100%;
            margin: 0 auto;
            animation: fadeIn 0.5s ease-out forwards;
        }
        
        .register-card {
            border: none;
            border-radius: 0.75rem;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
            overflow: hidden;
            transition: transform 0.3s, box-shadow 0.3s;
            background-color: white;
        }
        
        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05);
        }
        
        .register-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e5e7eb;
            text-align: center;
        }
        
        .register-logo {
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: 1rem;
        }
        
        .register-logo-icon {
            font-size: 2rem;
            color: var(--primary-color);
            margin-right: 0.75rem;
        }
        
        .register-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 0;
        }
        
        .register-body {
            padding: 2rem;
        }
        
        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #374151;
        }
        
        .form-control {
            display: block;
            width: 100%;
            padding: 0.625rem 1rem;
            font-size: 0.875rem;
            font-weight: 400;
            line-height: 1.5;
            color: #111827;
            background-color: #fff;
            background-clip: padding-box;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            transition: border-color 0.15s ease-in-out, box-shadow 0.15s ease-in-out;
        }
        
        .form-control:focus {
            color: #111827;
            background-color: #fff;
            border-color: var(--primary-color);
            outline: 0;
            box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.25);
        }
        
        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 500;
            padding: 0.625rem 1.5rem;
            border-radius: 0.5rem;
            transition: all 0.15s ease-in-out;
        }
        
        .btn-primary {
            color: #fff;
            background-color: var(--primary-color);
            border: 1px solid var(--primary-color);
        }
        
        .btn-primary:hover {
            background-color: var(--primary-hover);
            border-color: var(--primary-hover);
        }
        
        .btn-block {
            display: block;
            width: 100%;
        }
        
        .alert {
            position: relative;
            padding: 1rem 1rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
            border-radius: 0.5rem;
        }
        
        .alert-danger {
            color: #991b1b;
            background-color: #fee2e2;
            border-color: #fecaca;
        }
        
        .form-footer {
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .form-footer a {
            color: var(--primary-color);
            font-weight: 500;
            text-decoration: none;
        }
        
        .form-footer a:hover {
            text-decoration: underline;
        }
        
        .password-hint {
            font-size: 0.75rem;
            color: #6b7280;
            margin-top: 0.25rem;
        }
        
        /* Animations */
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <div class="register-logo">
                    <i class="fas fa-rocket register-logo-icon"></i>
                    <span class="text-xl font-bold">Auth System</span>
                </div>
                <h1 class="register-title">Create your account</h1>
            </div>
            
            <div class="register-body">
                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger mb-4">
                        <ul class="list-disc pl-4">
                            <?php foreach ($errors as $error): ?>
                                <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>
                
                <!-- Registration Form -->
                <form method="POST" action="<?php echo current_url(); ?>">
                    <input type="hidden" name="csrf_token" value="<?php echo generate_csrf_token(); ?>">
                    
                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($username); ?>" 
                               class="form-control" placeholder="Enter your username" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="email" class="form-label">Email</label>
                        <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" 
                               class="form-control" placeholder="Enter your email" required>
                    </div>
                    
                    <div class="mb-4">
                        <label for="password" class="form-label">Password</label>
                        <input type="password" id="password" name="password" 
                               class="form-control" placeholder="Create a password" required>
                        <p class="password-hint">Must be at least 8 characters long</p>
                    </div>
                    
                    <div class="mb-4">
                        <label for="confirm_password" class="form-label">Confirm Password</label>
                        <input type="password" id="confirm_password" name="confirm_password" 
                               class="form-control" placeholder="Confirm your password" required>
                    </div>
                    
                    <button type="submit" class="btn btn-primary btn-block">
                        Create Account
                    </button>
                    
                    <div class="form-footer mt-4">
                        <p>Already have an account? <a href="login.php">Sign in</a></p>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Focus on username field
            document.getElementById('username').focus();
        });
    </script>
</body>
</html>