<?php
session_start();
include '../includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['alogin'])) {
    header('location:dashboard.php');
    exit();
}

if (isset($_SESSION['eid'])) {
    if ($_SESSION['emprole'] == 'Supervisor') {
        header('location:../supervisor/dashboard.php');
    } else {
        header('location:..emp-changepassword.php');
    }
    exit();
}

$error = "";

if (isset($_POST['signin'])) {
    $uname = $_POST['username'];
    $password = md5($_POST['password']);
    
    $sql = "SELECT UserName, Password FROM admin WHERE UserName=:uname AND Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':uname', $uname, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    
    if ($query->rowCount() > 0) {
        $_SESSION['alogin'] = $uname;
        echo "<script>window.location.href = 'dashboard.php';</script>";
        exit();
    } else {
        $error = "Invalid username or password";
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login | Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 1rem;
        }
        
        .login-card {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            max-width: 400px;
            width: 100%;
        }
        
        .login-header {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            color: white;
            padding: 2rem;
            text-align: center;
        }
        
        .login-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
        }
        
        .btn-login {
            background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            width: 100%;
        }
        
        .login-footer {
            text-align: center;
            padding: 1.5rem;
            background: #f8f9fa;
            border-top: 1px solid #e9ecef;
        }
        
        .nav-tabs {
            border: none;
            background: rgba(255,255,255,0.1);
            border-radius: 0.5rem;
            padding: 0.25rem;
        }
        
        .nav-link {
            color: rgba(255,255,255,0.8);
            border: none;
            border-radius: 0.375rem;
            padding: 0.75rem 1.5rem;
        }
        
        .nav-link.active {
            background: white;
            color: #dc2626;
            font-weight: 600;
        }
        
        .security-features {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 1.5rem;
        }
        
        .feature-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            color: #6b7280;
        }
        
        .feature-item i {
            color: #dc2626;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 text-center mb-5">
                    <h1 class="text-white mb-3">
                        <i class="fas fa-calendar-alt me-3"></i>
                        Employee Leave Management System
                    </h1>
                    <p class="text-white-50 lead">Administrative Control Panel</p>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <!-- Login Tabs -->
                    <div class="text-center mb-4">
                        <ul class="nav nav-tabs justify-content-center d-inline-flex">
                            <li class="nav-item">
                                <a class="nav-link" href="../index.php">
                                    <i class="fas fa-user me-2"></i>Employee Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link active" href="index.php">
                                    <i class="fas fa-user-shield me-2"></i>Admin Login
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="login-card">
                        <div class="login-header">
                            <i class="fas fa-user-shield fa-3x mb-3"></i>
                            <h3>Admin Portal</h3>
                            <p class="mb-0">System Administrator Access</p>
                        </div>
                        
                        <div class="login-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlentities($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post">
                                <!-- Hidden username field for accessibility -->
                                <input type="hidden" name="username_hidden" autocomplete="username">
                                
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input type="text" class="form-control" id="username" name="username" 
                                           placeholder="Enter admin username" required autocomplete="username">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter admin password" required autocomplete="current-password">
                                </div>
                                
                                <button type="submit" name="signin" class="btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Admin Sign In
                                </button>
                            </form>
                            
                            <!-- Security Features -->
                            <div class="security-features">
                                <div class="feature-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <span>Secure Access</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-user-lock"></i>
                                    <span>Admin Only</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-audit"></i>
                                    <span>Full Control</span>
                                </div>
                                <div class="feature-item">
                                    <i class="fas fa-database"></i>
                                    <span>System Data</span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="login-footer">
                            <small class="text-muted">
                                <i class="fas fa-exclamation-triangle me-1"></i>
                                Restricted access - authorized personnel only
                            </small>
                        </div>
                    </div>
                    
                    <!-- Security Notice -->
                    <div class="text-center mt-4">
                        <div class="alert alert-warning">
                            <i class="fas fa-lock me-2"></i>
                            <small>This area contains sensitive system information</small>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-5">
                <p class="text-white-50 mb-0">
                    &copy; <?php echo date('Y'); ?> Employee Leave Management System
                </p>
                <small class="text-white-30">Administrative Version</small>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>