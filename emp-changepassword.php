<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'includes/config.php';

// Check for employee session
if (!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) {
    header('location:index.php');
    exit();
}

$msg = $error = "";
$debug_info = ""; // For debugging information

if (isset($_POST['change'])) {
    $current_password = $_POST['password'];
    $new_password = $_POST['newpassword'];
    $confirm_password = $_POST['confirmpassword'];
    $username = $_SESSION['empemail'];
    $employee_id = $_SESSION['eid'];

    // Debug information
    $debug_info .= "Form submitted<br>";
    $debug_info .= "Username: " . $username . "<br>";
    $debug_info .= "Employee ID: " . $employee_id . "<br>";

    // Validate that new password and confirm password match
    if ($new_password !== $confirm_password) {
        $error = "New password and confirm password do not match.";
    } elseif (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "All password fields are required.";
    } else {
        try {
            // Check if current password is correct
            $sql = "SELECT Password, EmailId, EmpId FROM tblemployees WHERE id = :id";
            $query = $dbh->prepare($sql);
            $query->bindParam(':id', $employee_id, PDO::PARAM_INT);
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);

            if ($result) {
                $debug_info .= "User found in database<br>";
                $debug_info .= "Stored password hash: " . $result->Password . "<br>";
                
                // Verify current password using MD5
                $current_password_md5 = md5($current_password);
                $debug_info .= "Entered current password MD5: " . $current_password_md5 . "<br>";
                
                if ($current_password_md5 === $result->Password) {
                    $debug_info .= "Current password matches!<br>";
                    
                    // Current password is correct, update to new password
                    $new_password_md5 = md5($new_password);
                    $debug_info .= "New password MD5: " . $new_password_md5 . "<br>";
                    
                    $update_sql = "UPDATE tblemployees SET Password = :newpassword WHERE id = :id";
                    $update_query = $dbh->prepare($update_sql);
                    $update_query->bindParam(':id', $employee_id, PDO::PARAM_INT);
                    $update_query->bindParam(':newpassword', $new_password_md5, PDO::PARAM_STR);
                    
                    if ($update_query->execute()) {
                        $rows_affected = $update_query->rowCount();
                        $debug_info .= "Rows affected: " . $rows_affected . "<br>";
                        
                        if ($rows_affected > 0) {
                            $msg = "Your password has been successfully changed.";
                            $debug_info .= "Password updated successfully!<br>";
                        } else {
                            $error = "No changes were made. Please check if the new password is different from the current one.";
                        }
                    } else {
                        $error = "Failed to update password. Database error occurred.";
                        $debug_info .= "Update query failed<br>";
                    }
                } else {
                    $error = "Your current password is incorrect.";
                    $debug_info .= "Current password does not match stored password<br>";
                }
            } else {
                $error = "User not found in database.";
                $debug_info .= "No user found with ID: " . $employee_id . "<br>";
            }
        } catch (PDOException $e) {
            $error = "Database error: " . $e->getMessage();
            $debug_info .= "PDO Exception: " . $e->getMessage() . "<br>";
        }
    }
}

// Debug: Check database connection
try {
    $test_sql = "SELECT COUNT(*) as count FROM tblemployees";
    $test_query = $dbh->query($test_sql);
    $test_result = $test_query->fetch(PDO::FETCH_OBJ);
    $debug_info .= "Database connection test: " . $test_result->count . " employees in database<br>";
} catch (PDOException $e) {
    $debug_info .= "Database connection error: " . $e->getMessage() . "<br>";
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Employee Leave Management System | Change Password</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        :root {
            --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            --secondary-gradient: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8fafc;
            overflow-x: hidden;
            margin: 0;
            padding: 0;
        }
        
        /* Header should be fixed at top */
        .header-container {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            height: 70px;
        }
        
        /* Main layout container */
        .main-container {
            display: flex;
            min-height: 100vh;
            padding-top: 70px; /* Account for fixed header */
        }
        
        /* Sidebar styling */
        .sidebar-container {
            width: 280px;
            position: fixed;
            left: 0;
            top: 70px;
            bottom: 0;
            z-index: 1020;
            background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
            overflow-y: auto;
            transition: transform 0.3s ease;
        }
        
        /* Main content area */
        .content-container {
            flex: 1;
            margin-left: 280px;
            min-height: calc(100vh - 70px);
            transition: margin-left 0.3s ease;
        }
        
        .content-area {
            padding: 30px;
            min-height: 100%;
        }
        
        @media (max-width: 991.98px) {
            .sidebar-container {
                transform: translateX(-100%);
            }
            
            .sidebar-container.show {
                transform: translateX(0);
            }
            
            .content-container {
                margin-left: 0;
            }
        }
        
        .page-title {
            color: #1e293b;
            font-weight: 700;
            font-size: 1.75rem;
            margin-bottom: 0.5rem;
        }
        
        .breadcrumb {
            background: transparent;
            padding: 0;
            margin-bottom: 2rem;
        }
        
        .breadcrumb-item a {
            color: #64748b;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .breadcrumb-item a:hover {
            color: #4f46e5;
        }
        
        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            overflow: hidden;
            margin-bottom: 2rem;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 35px rgba(0, 0, 0, 0.12);
        }
        
        .card-header {
            background: var(--secondary-gradient);
            color: white;
            border-radius: 16px 16px 0 0 !important;
            padding: 1.5rem 2rem;
            border: none;
        }
        
        .card-header h5 {
            margin: 0;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.75rem;
            font-size: 0.95rem;
        }
        
        .form-control {
            border: 2px solid #e2e8f0;
            border-radius: 12px;
            padding: 0.875rem 1rem;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.1);
        }
        
        .input-group .form-control {
            border-right: none;
        }
        
        .input-group .btn {
            border-left: none;
            border-color: #e2e8f0;
            background: white;
            transition: all 0.3s ease;
        }
        
        .input-group .btn:hover {
            background: #f8fafc;
            border-color: #e2e8f0;
        }
        
        .password-strength {
            height: 6px;
            background: #e2e8f0;
            border-radius: 3px;
            margin-top: 0.75rem;
            overflow: hidden;
        }
        
        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: all 0.3s ease;
            border-radius: 3px;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 1rem 2.5rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
        }
        
        .btn-primary:disabled {
            opacity: 0.7;
            transform: none;
            box-shadow: none;
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
            border-left: 4px solid #dc2626;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
            border-left: 4px solid #10b981;
        }
        
        .alert-info {
            background: linear-gradient(135deg, #dbeafe 0%, #e0e7ff 100%);
            color: #1e40af;
            border-left: 4px solid #3b82f6;
        }
        
        .security-tips ul {
            list-style: none;
            padding: 0;
        }
        
        .security-tips li {
            padding: 0.5rem 0;
            color: #475569;
            display: flex;
            align-items: flex-start;
        }
        
        .security-tips i {
            margin-right: 0.75rem;
            margin-top: 0.25rem;
            flex-shrink: 0;
        }
        
        .best-practices ul {
            list-style: none;
            padding: 0;
        }
        
        .best-practices li {
            padding: 0.75rem 0;
            color: #475569;
            border-bottom: 1px solid #f1f5f9;
            position: relative;
            padding-left: 1.5rem;
        }
        
        .best-practices li:last-child {
            border-bottom: none;
        }
        
        .best-practices li:before {
            content: "•";
            color: #4f46e5;
            font-weight: bold;
            position: absolute;
            left: 0;
        }
        
        .bg-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%) !important;
        }
        
        .bg-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important;
        }
        
        /* Mobile sidebar backdrop */
        .sidebar-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1019;
            display: none;
        }
        
        .sidebar-backdrop.show {
            display: block;
        }
        
        .debug-info {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 15px;
            margin-top: 20px;
            font-family: monospace;
            font-size: 12px;
            white-space: pre-wrap;
        }
    </style>
</head>

<body>
    <!-- Header Container (Fixed at top) -->
    <div class="header-container">
        <?php include 'includes/header.php'; ?>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar Container (Fixed beside content) -->
        <div class="sidebar-container" id="sidebarContainer">
            <?php include 'includes/sidebar.php'; ?>
        </div>

        <!-- Content Container (Beside sidebar) -->
        <div class="content-container">
            <div class="content-area">
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h1 class="page-title">
                                <i class="fas fa-key me-3"></i>Change Password
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="employee/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-primary">Change Password</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Alerts -->
                    <?php if ($error): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-exclamation-circle me-3 fs-5"></i>
                                <div class="flex-grow-1">
                                    <strong>Error!</strong> <?php echo htmlentities($error); ?>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if ($msg): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-3 fs-5"></i>
                                <div class="flex-grow-1">
                                    <strong>Success!</strong> <?php echo htmlentities($msg); ?>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Debug Information -->
                    <?php if (!empty($debug_info)): ?>
                        <div class="alert alert-info">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-bug me-3 fs-5"></i>
                                <div class="flex-grow-1">
                                    <strong>Debug Information:</strong>
                                    <div class="debug-info mt-2">
                                        <?php echo htmlentities($debug_info); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Password Change Form -->
                        <div class="col-xl-6 col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-lock me-2"></i>Update Your Password
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <form method="post" id="passwordForm" autocomplete="off">
                                        <!-- Hidden username field for accessibility -->
                                        <input type="hidden" autocomplete="username" value="<?php echo htmlentities($_SESSION['empemail']); ?>">
                                        
                                        <div class="mb-4">
                                            <label for="currentPassword" class="form-label">Current Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="currentPassword" 
                                                       name="password" required autocomplete="current-password"
                                                       placeholder="Enter your current password">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                        data-target="currentPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label for="newPassword" class="form-label">New Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="newPassword" 
                                                       name="newpassword" required autocomplete="new-password"
                                                       placeholder="Enter your new password">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                        data-target="newPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <div class="password-strength mt-3">
                                                <div class="password-strength-fill" id="passwordStrength"></div>
                                            </div>
                                            <small class="form-text text-muted mt-2 d-block">
                                                Password strength: <span id="strengthText" class="fw-bold">None</span>
                                            </small>
                                        </div>

                                        <div class="mb-4">
                                            <label for="confirmPassword" class="form-label">Confirm New Password</label>
                                            <div class="input-group">
                                                <input type="password" class="form-control" id="confirmPassword" 
                                                       name="confirmpassword" required autocomplete="new-password"
                                                       placeholder="Confirm your new password">
                                                <button type="button" class="btn btn-outline-secondary toggle-password" 
                                                        data-target="confirmPassword">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </div>
                                            <small id="confirmMessage" class="form-text fw-bold mt-2 d-block"></small>
                                        </div>

                                        <div class="d-grid gap-2">
                                            <button type="submit" name="change" id="submitButton" 
                                                    class="btn btn-primary py-3">
                                                <i class="fas fa-save me-2"></i>Update Password
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Tips & Best Practices -->
                        <div class="col-xl-6 col-lg-4">
                            <div class="card">
                                <div class="card-header bg-info">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>Password Security Tips
                                    </h5>
                                </div>
                                <div class="card-body security-tips">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <ul>
                                                <li>
                                                    <i class="fas fa-check text-success"></i>
                                                    Minimum 8 characters
                                                </li>
                                                <li>
                                                    <i class="fas fa-check text-success"></i>
                                                    Uppercase & lowercase
                                                </li>
                                                <li>
                                                    <i class="fas fa-check text-success"></i>
                                                    Include numbers (0-9)
                                                </li>
                                            </ul>
                                        </div>
                                        <div class="col-md-6">
                                            <ul>
                                                <li>
                                                    <i class="fas fa-check text-success"></i>
                                                    Special characters
                                                </li>
                                                <li>
                                                    <i class="fas fa-times text-danger"></i>
                                                    Avoid personal info
                                                </li>
                                                <li>
                                                    <i class="fas fa-times text-danger"></i>
                                                    Don't reuse passwords
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="card mt-4">
                                <div class="card-header bg-warning">
                                    <h5 class="card-title mb-0 text-dark">
                                        <i class="fas fa-lightbulb me-2"></i>Best Practices
                                    </h5>
                                </div>
                                <div class="card-body best-practices">
                                    <ul>
                                        <li>Change your password regularly (every 90 days)</li>
                                        <li>Use a unique password for this system only</li>
                                        <li>Consider using a reputable password manager</li>
                                        <li>Never share your password with anyone</li>
                                        <li>Enable two-factor authentication if available</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Sidebar Backdrop -->
    <div class="sidebar-backdrop"></div>

    <!-- Bootstrap & jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        $(document).ready(function() {
            // Toggle Password Visibility
            $('.toggle-password').on('click', function() {
                const target = $(this).data('target');
                const input = $('#' + target);
                const icon = $(this).find('i');
                
                if (input.attr('type') === 'password') {
                    input.attr('type', 'text');
                    icon.removeClass('fa-eye').addClass('fa-eye-slash');
                    $(this).addClass('active');
                } else {
                    input.attr('type', 'password');
                    icon.removeClass('fa-eye-slash').addClass('fa-eye');
                    $(this).removeClass('active');
                }
            });

            // Password Strength Checker
            $('#newPassword').on('input', function() {
                const password = $(this).val();
                let strength = 0;
                let strengthText = "None";
                let strengthColor = "#dc3545";
                
                // Length check
                if (password.length >= 8) strength += 25;
                // Uppercase check
                if (/[A-Z]/.test(password)) strength += 25;
                // Lowercase check
                if (/[a-z]/.test(password)) strength += 25;
                // Number check
                if (/[0-9]/.test(password)) strength += 25;
                // Special character check
                if (/[^A-Za-z0-9]/.test(password)) strength = Math.min(100, strength + 10);

                const bar = $('#passwordStrength');
                bar.css('width', strength + '%');
                
                // Update strength indicator
                if (strength < 50) {
                    strengthColor = '#dc3545';
                    strengthText = 'Weak';
                } else if (strength < 75) {
                    strengthColor = '#ffc107';
                    strengthText = 'Medium';
                } else {
                    strengthColor = '#28a745';
                    strengthText = 'Strong';
                }
                
                bar.css('background', strengthColor);
                $('#strengthText').text(strengthText).css('color', strengthColor);
                
                validateForm();
            });

            // Confirm Password Validation
            $('#confirmPassword, #newPassword').on('input', validateForm);

            function validateForm() {
                const newPass = $('#newPassword').val();
                const confirmPass = $('#confirmPassword').val();
                const msg = $('#confirmMessage');

                if (newPass && confirmPass) {
                    if (newPass === confirmPass) {
                        msg.text('✓ Passwords match').css('color', 'green');
                    } else {
                        msg.text('✗ Passwords do not match').css('color', 'red');
                    }
                } else {
                    msg.text('');
                }
            }

            // Form submission handler
            $('#passwordForm').on('submit', function() {
                const newPass = $('#newPassword').val();
                const confirmPass = $('#confirmPassword').val();
                
                if (newPass !== confirmPass) {
                    alert('New password and confirm password do not match. Please fix before submitting.');
                    return false;
                }
                
                $('#submitButton').prop('disabled', true)
                    .html('<i class="fas fa-spinner fa-spin me-2"></i>Updating Password...');
                return true;
            });

            // Mobile sidebar functionality
            $('.mobile-menu-btn').on('click', function() {
                $('#sidebarContainer').toggleClass('show');
                $('.sidebar-backdrop').toggleClass('show');
            });
            
            // Close sidebar when clicking on backdrop
            $('.sidebar-backdrop').on('click', function() {
                $('#sidebarContainer').removeClass('show');
                $(this).removeClass('show');
            });
            
            // Auto-close sidebar on mobile when clicking a link
            $('.sidebar-container .nav-link').on('click', function() {
                if ($(window).width() < 992) {
                    $('#sidebarContainer').removeClass('show');
                    $('.sidebar-backdrop').removeClass('show');
                }
            });
        });
    </script>
</body>
</html>