<?php
session_start();
include 'includes/config.php';

// Redirect if already logged in
if (isset($_SESSION['eid'])) {
    if (isset($_SESSION['role'])) {
        if ($_SESSION['role'] == 'Supervisor') {
            header('location:supervisor/dashboard.php');
            exit();
        } elseif ($_SESSION['role'] == 'Employee') {
            header("Location: emp-changepassword.php");
            exit();
        }
    }
}

if (isset($_SESSION['alogin'])) {
    header('location:admin/dashboard.php');
    exit();
}

$error = "";

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    
    // Check employee table
    $sql = "SELECT id, EmpId, FirstName, LastName, EmailId, Department, Role, status FROM tblemployees WHERE EmailId=:email AND Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);
    
    if ($result) {
        if ($result->status == 1) {
            $_SESSION['eid'] = $result->id;
            $_SESSION['empname'] = $result->FirstName . ' ' . $result->LastName;
            $_SESSION['empemail'] = $result->EmailId;
            $_SESSION['role'] = $result->Role; // Changed from 'emprole' to 'role'
            $_SESSION['empdept'] = $result->Department;
            
            if ($result->Role == 'Supervisor') {
                echo "<script>window.location.href = 'supervisor/dashboard.php';</script>";
            } else {
                echo "<script>window.location.href = 'emp-changepassword.php';</script>";
            }
            exit();
        } else {
            $error = "Your account is inactive. Please contact administrator.";
        }
    } else {
        $error = "Invalid email or password";
    }
}
?>

<!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <style>
        .login-container {
            min-height: 100vh;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
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
            background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
            border: none;
            border-radius: 0.5rem;
            padding: 0.75rem 2rem;
            font-weight: 600;
            color: white;
            width: 100%;
            transition: all 0.3s ease;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.3);
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
            transition: all 0.3s ease;
        }
        
        .nav-link.active {
            background: white;
            color: #4f46e5;
            font-weight: 600;
        }
        
        .nav-link:not(.active):hover {
            background: rgba(255,255,255,0.2);
            color: white;
        }
        
        .alert {
            border-radius: 0.5rem;
            border: none;
        }
        
        .alert-danger {
            background-color: #fee2e2;
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .form-label {
            font-weight: 500;
            color: #374151;
            margin-bottom: 0.5rem;
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
                    <p class="text-white-50 lead">Streamlining leave management for modern organizations</p>
                </div>
                
                <div class="col-md-6 col-lg-4">
                    <!-- Login Tabs -->
                    <div class="text-center mb-4">
                        <ul class="nav nav-tabs justify-content-center d-inline-flex">
                            <li class="nav-item">
                                <a class="nav-link active" href="index.php">
                                    <i class="fas fa-user me-2"></i>Employee Login
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="admin/">
                                    <i class="fas fa-user-shield me-2"></i>Admin Login
                                </a>
                            </li>
                        </ul>
                    </div>
                    
                    <div class="login-card">
                        <div class="login-header">
                            <i class="fas fa-user fa-3x mb-3"></i>
                            <h3>Employee Portal</h3>
                            <p class="mb-0">Sign in to access your account</p>
                        </div>
                        
                        <div class="login-body">
                            <?php if ($error): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlentities($error); ?>
                                </div>
                            <?php endif; ?>
                            
                            <form method="post">
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email Address</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           placeholder="Enter your email" required autocomplete="email">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="password" class="form-label">Password</label>
                                    <input type="password" class="form-control" id="password" name="password" 
                                           placeholder="Enter your password" required autocomplete="current-password">
                                </div>
                                
                                <button type="submit" name="login" class="btn-login">
                                    <i class="fas fa-sign-in-alt me-2"></i>Sign In
                                </button>
                            </form>
                        </div>
                        
                        <div class="login-footer">
                            <small class="text-muted">
                                <i class="fas fa-info-circle me-1"></i>
                                Access your leave balance and request time off
                            </small>
                        </div>
                    </div>
                    
                    <!-- Quick Info -->
                    <div class="text-center mt-4">
                        <div class="row">
                            <div class="col-6">
                                <div class="text-white">
                                    <i class="fas fa-clock fa-2x mb-2"></i>
                                    <div class="small">Easy Leave Requests</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="text-white">
                                    <i class="fas fa-chart-bar fa-2x mb-2"></i>
                                    <div class="small">Track Your Balance</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Footer -->
            <div class="text-center mt-5">
                <p class="text-white-50 mb-0">
                    &copy; <?php echo date('Y'); ?> Employee Leave Management System
                </p>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>