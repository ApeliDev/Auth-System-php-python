<?php
require_once '../includes/auth.php';
require_once '../includes/functions.php';

Session::start();
$auth = new Auth();

// Check if user is logged in
if (!$auth->isLoggedIn()) {
    set_flash_message('Please login to access the dashboard.', 'warning');
    redirect('login.php');
}

$user = $auth->getCurrentUser();

// Handle logout
if (isset($_GET['logout']) && $_GET['logout'] === 'true') {
    $auth->logout();
    set_flash_message('You have been logged out successfully.', 'success');
    redirect('login.php');
}
?>

<!DOCTYPE html>
<html lang="en" class="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modern Dashboard</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Tailwind CSS -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar" id="sidebar">
        <div class="sidebar-header">
            <a href="#" class="d-flex align-items-center text-decoration-none">
                <i class="fas fa-rocket text-primary me-2 fs-4"></i>
                <span class="fs-4 fw-bold text-dark d-none d-lg-inline">Auth System</span>
            </a>
        </div>
        
        <div class="sidebar-menu">
            <a href="#" class="sidebar-menu-item active">
                <i class="fas fa-home"></i>
                <span>Dashboard</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-chart-line"></i>
                <span>Analytics</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-shopping-cart"></i>
                <span>Orders</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-users"></i>
                <span>Customers</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-box"></i>
                <span>Products</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-file-invoice"></i>
                <span>Invoices</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-calendar"></i>
                <span>Calendar</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-envelope"></i>
                <span>Messages</span>
                <span class="badge bg-primary rounded-pill ms-auto">5</span>
            </a>
            
            <div class="px-4 mt-4 mb-2">
                <span class="text-xs font-semibold text-gray-500 uppercase">Settings</span>
            </div>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
            
            <a href="#" class="sidebar-menu-item">
                <i class="fas fa-question-circle"></i>
                <span>Help</span>
            </a>
        </div>
    </div>
    
    <!-- Header -->
    <header class="header">
        <button class="btn btn-link text-gray-600 p-0 me-3 d-lg-none" id="sidebarToggle">
            <i class="fas fa-bars fa-lg"></i>
        </button>
        
        <div class="header-search">
            <i class="fas fa-search"></i>
            <input type="text" class="form-control border-0 shadow-none" placeholder="Search...">
        </div>
        
        <div class="header-actions">
            <div class="position-relative">
                <label class="toggle-switch me-2">
                    <input type="checkbox" id="darkModeToggle">
                    <span class="toggle-slider"></span>
                </label>
                <span class="d-none d-lg-inline">Dark Mode</span>
            </div>
            
            <div class="position-relative">
                <button class="btn btn-link text-gray-600 p-0">
                    <i class="fas fa-bell fa-lg"></i>
                    <span class="notification-badge">3</span>
                </button>
            </div>
            
            <div class="dropdown">
                <div class="user-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="User" class="user-avatar">
                    <span class="d-none d-lg-inline"><?php echo htmlspecialchars($user['username']); ?></span>
                    <i class="fas fa-chevron-down d-none d-lg-inline"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end mt-2">
                    <li><a class="dropdown-item" href="#"><i class="fas fa-user me-2"></i> Profile</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-cog me-2"></i> Settings</a></li>
                    <li><a class="dropdown-item" href="#"><i class="fas fa-envelope me-2"></i> Messages</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="?logout=true"><i class="fas fa-sign-out-alt me-2"></i> Logout</a></li>
                </ul>
            </div>
        </div>
    </header>
    
    <!-- Main Content -->
    <main class="main-content">
        <div class="container-fluid py-4">
            <!-- Page Title -->
            <div class="row mb-4">
                <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center">
                        <h1 class="h3 mb-0">Dashboard</h1>
                        <nav aria-label="breadcrumb">
                            <ol class="breadcrumb mb-0">
                                <li class="breadcrumb-item"><a href="#">Home</a></li>
                                <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
                            </ol>
                        </nav>
                    </div>
                </div>
            </div>
            
            <!-- Stats Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6 mb-4 fade-in delay-100">
                    <div class="card stat-card h-100">
                        <div class="stat-card-icon">
                            <i class="fas fa-dollar-sign"></i>
                        </div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up me-1"></i> 12.5%
                        </div>
                        <h3 class="stat-card-value">$24,780</h3>
                        <p class="stat-card-label">Total Revenue</p>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4 fade-in delay-200">
                    <div class="card stat-card h-100">
                        <div class="stat-card-icon bg-success">
                            <i class="fas fa-users"></i>
                        </div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up me-1"></i> 8.3%
                        </div>
                        <h3 class="stat-card-value">1,254</h3>
                        <p class="stat-card-label">New Customers</p>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4 fade-in delay-300">
                    <div class="card stat-card h-100">
                        <div class="stat-card-icon bg-warning">
                            <i class="fas fa-shopping-cart"></i>
                        </div>
                        <div class="stat-card-change negative">
                            <i class="fas fa-arrow-down me-1"></i> 3.2%
                        </div>
                        <h3 class="stat-card-value">432</h3>
                        <p class="stat-card-label">Total Orders</p>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4 fade-in">
                    <div class="card stat-card h-100">
                        <div class="stat-card-icon bg-danger">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <div class="stat-card-change positive">
                            <i class="fas fa-arrow-up me-1"></i> 5.7%
                        </div>
                        <h3 class="stat-card-value">84.5%</h3>
                        <p class="stat-card-label">Conversion Rate</p>
                    </div>
                </div>
            </div>
            
            <!-- Charts Row -->
            <div class="row mb-4">
                <div class="col-xl-8 mb-4 fade-in delay-100">
                    <div class="card h-100">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Revenue Overview</h5>
                            <div class="dropdown">
                                <button class="btn btn-link p-0 text-gray-600 dropdown-toggle" type="button" id="revenueDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                    This Month
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="revenueDropdown">
                                    <li><a class="dropdown-item" href="#">Today</a></li>
                                    <li><a class="dropdown-item" href="#">This Week</a></li>
                                    <li><a class="dropdown-item" href="#">This Month</a></li>
                                    <li><a class="dropdown-item" href="#">This Year</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="revenueChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-4 mb-4 fade-in delay-200">
                    <div class="card h-100">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Sales by Category</h5>
                        </div>
                        <div class="card-body">
                            <div class="chart-container">
                                <canvas id="categoryChart"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Orders & Top Products -->
            <div class="row">
                <div class="col-lg-8 mb-4 fade-in delay-100">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Recent Orders</h5>
                            <a href="#" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Customer</th>
                                            <th>Date</th>
                                            <th>Amount</th>
                                            <th>Status</th>
                                            <th>Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>#ORD-7841</td>
                                            <td>Sarah Johnson</td>
                                            <td>12 May 2023</td>
                                            <td>$245.50</td>
                                            <td><span class="status-badge success">Completed</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-link p-0">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-7840</td>
                                            <td>Michael Brown</td>
                                            <td>11 May 2023</td>
                                            <td>$189.99</td>
                                            <td><span class="status-badge success">Completed</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-link p-0">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-7839</td>
                                            <td>Emily Wilson</td>
                                            <td>10 May 2023</td>
                                            <td>$432.00</td>
                                            <td><span class="status-badge warning">Processing</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-link p-0">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-7838</td>
                                            <td>David Thompson</td>
                                            <td>9 May 2023</td>
                                            <td>$156.75</td>
                                            <td><span class="status-badge success">Completed</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-link p-0">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td>#ORD-7837</td>
                                            <td>Jennifer Lee</td>
                                            <td>8 May 2023</td>
                                            <td>$321.50</td>
                                            <td><span class="status-badge danger">Cancelled</span></td>
                                            <td>
                                                <button class="btn btn-sm btn-link p-0">
                                                    <i class="fas fa-ellipsis-v"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-lg-4 mb-4 fade-in delay-200">
                    <div class="card">
                        <div class="card-header d-flex justify-content-between align-items-center">
                            <h5 class="card-title mb-0">Top Products</h5>
                            <a href="#" class="btn btn-sm btn-primary">View All</a>
                        </div>
                        <div class="card-body">
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://via.placeholder.com/60" alt="Product" class="rounded me-3" width="60">
                                    <div>
                                        <h6 class="mb-1">Wireless Headphones</h6>
                                        <div class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                            <span class="ms-1">4.5</span>
                                        </div>
                                    </div>
                                    <div class="ms-auto fw-bold">$129.99</div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-primary" role="progressbar" style="width: 85%;" aria-valuenow="85" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://via.placeholder.com/60" alt="Product" class="rounded me-3" width="60">
                                    <div>
                                        <h6 class="mb-1">Smart Watch</h6>
                                        <div class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="far fa-star"></i>
                                            <span class="ms-1">4.0</span>
                                        </div>
                                    </div>
                                    <div class="ms-auto fw-bold">$199.99</div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: 72%;" aria-valuenow="72" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://via.placeholder.com/60" alt="Product" class="rounded me-3" width="60">
                                    <div>
                                        <h6 class="mb-1">Bluetooth Speaker</h6>
                                        <div class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <span class="ms-1">5.0</span>
                                        </div>
                                    </div>
                                    <div class="ms-auto fw-bold">$89.99</div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-warning" role="progressbar" style="width: 65%;" aria-valuenow="65" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                            
                            <div class="mb-2">
                                <div class="d-flex align-items-center mb-3">
                                    <img src="https://via.placeholder.com/60" alt="Product" class="rounded me-3" width="60">
                                    <div>
                                        <h6 class="mb-1">Laptop Backpack</h6>
                                        <div class="text-warning small">
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star"></i>
                                            <i class="fas fa-star-half-alt"></i>
                                            <span class="ms-1">4.5</span>
                                        </div>
                                    </div>
                                    <div class="ms-auto fw-bold">$49.99</div>
                                </div>
                                <div class="progress" style="height: 6px;">
                                    <div class="progress-bar bg-info" role="progressbar" style="width: 58%;" aria-valuenow="58" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Recent Activity -->
            <div class="row fade-in delay-300">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <div class="timeline">
                                <div class="timeline-item">
                                    <div class="timeline-item-marker">
                                        <div class="timeline-item-marker-indicator bg-primary"></div>
                                    </div>
                                    <div class="timeline-item-content">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold">New order received</span>
                                            <small class="text-muted">2 min ago</small>
                                        </div>
                                        <p class="mb-0">Order #ORD-7842 from Sarah Johnson for $189.99</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-item-marker">
                                        <div class="timeline-item-marker-indicator bg-success"></div>
                                    </div>
                                    <div class="timeline-item-content">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold">Payment received</span>
                                            <small class="text-muted">1 hour ago</small>
                                        </div>
                                        <p class="mb-0">Payment of $432.00 for order #ORD-7839</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-item-marker">
                                        <div class="timeline-item-marker-indicator bg-warning"></div>
                                    </div>
                                    <div class="timeline-item-content">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold">New customer registered</span>
                                            <small class="text-muted">3 hours ago</small>
                                        </div>
                                        <p class="mb-0">Robert Davis registered as a new customer</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-item-marker">
                                        <div class="timeline-item-marker-indicator bg-info"></div>
                                    </div>
                                    <div class="timeline-item-content">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold">Product restocked</span>
                                            <small class="text-muted">5 hours ago</small>
                                        </div>
                                        <p class="mb-0">Wireless Headphones (50 units) have been restocked</p>
                                    </div>
                                </div>
                                
                                <div class="timeline-item">
                                    <div class="timeline-item-marker">
                                        <div class="timeline-item-marker-indicator bg-danger"></div>
                                    </div>
                                    <div class="timeline-item-content">
                                        <div class="d-flex justify-content-between mb-2">
                                            <span class="fw-bold">Order cancelled</span>
                                            <small class="text-muted">Yesterday</small>
                                        </div>
                                        <p class="mb-0">Order #ORD-7837 from Jennifer Lee has been cancelled</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Sidebar Toggle
            const sidebar = document.getElementById('sidebar');
            const sidebarToggle = document.getElementById('sidebarToggle');
            const mainContent = document.querySelector('.main-content');
            const header = document.querySelector('.header');
            
            sidebarToggle.addEventListener('click', function() {
                sidebar.classList.toggle('collapsed');
                
                // For mobile view
                if (window.innerWidth < 992) {
                    sidebar.classList.toggle('show');
                }
            });
            
            // Dark Mode Toggle
            const darkModeToggle = document.getElementById('darkModeToggle');
            const html = document.documentElement;
            
            darkModeToggle.addEventListener('change', function() {
                if (this.checked) {
                    html.classList.add('dark');
                    localStorage.setItem('darkMode', 'enabled');
                } else {
                    html.classList.remove('dark');
                    localStorage.setItem('darkMode', 'disabled');
                }
            });
            
            // Check for saved dark mode preference
            if (localStorage.getItem('darkMode')) {
                if (localStorage.getItem('darkMode') === 'enabled') {
                    html.classList.add('dark');
                    darkModeToggle.checked = true;
                } else {
                    html.classList.remove('dark');
                    darkModeToggle.checked = false;
                }
            } else if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
                // Check system preference
                html.classList.add('dark');
                darkModeToggle.checked = true;
                localStorage.setItem('darkMode', 'enabled');
            }
            
            // Initialize Charts
            // Revenue Chart
            const revenueCtx = document.getElementById('revenueChart').getContext('2d');
            const revenueChart = new Chart(revenueCtx, {
                type: 'line',
                data: {
                    labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul'],
                    datasets: [{
                        label: 'Revenue',
                        data: [12000, 15000, 18000, 21000, 24000, 22000, 25000],
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99, 102, 241, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }, {
                        label: 'Profit',
                        data: [8000, 10000, 12000, 14000, 16000, 15000, 17000],
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'top',
                        },
                        tooltip: {
                            mode: 'index',
                            intersect: false,
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            grid: {
                                drawBorder: false
                            },
                            ticks: {
                                callback: function(value) {
                                    return '$' + value.toLocaleString();
                                }
                            }
                        },
                        x: {
                            grid: {
                                display: false,
                                drawBorder: false
                            }
                        }
                    }
                }
            });
            
            // Category Chart
            const categoryCtx = document.getElementById('categoryChart').getContext('2d');
            const categoryChart = new Chart(categoryCtx, {
                type: 'doughnut',
                data: {
                    labels: ['Electronics', 'Clothing', 'Home & Garden', 'Books', 'Other'],
                    datasets: [{
                        data: [35, 25, 20, 10, 10],
                        backgroundColor: [
                            '#6366f1',
                            '#10b981',
                            '#f59e0b',
                            '#3b82f6',
                            '#ef4444'
                        ],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '70%',
                    plugins: {
                        legend: {
                            position: 'bottom',
                        }
                    }
                }
            });
            
            // Update charts on dark mode toggle
            darkModeToggle.addEventListener('change', function() {
                revenueChart.update();
                categoryChart.update();
            });
            
            // Animate elements on scroll
            const animateOnScroll = function() {
                const elements = document.querySelectorAll('.fade-in');
                
                elements.forEach(element => {
                    const elementPosition = element.getBoundingClientRect().top;
                    const screenPosition = window.innerHeight / 1.2;
                    
                    if (elementPosition < screenPosition) {
                        element.style.opacity = '1';
                        element.style.transform = 'translateY(0)';
                    }
                });
            };
            
            window.addEventListener('scroll', animateOnScroll);
            animateOnScroll(); // Run once on load
        });
    </script>
</body>
</html>