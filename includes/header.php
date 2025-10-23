<?php
// Enhanced Employee Header with Modern Design
if (!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) {
    header('location:index.php');
    exit();
} else {
    $empid = $_SESSION['eid'];
    
    // Get employee details for display
    $sql = "SELECT * FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $employee = $query->fetch(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <title>Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/modern.css">
    
    <style>
        .employee-navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            padding: 0.75rem 0;
            position: sticky;
            top: 0;
            z-index: 1030;
        }
        
        .navbar-brand-employee {
            font-size: 1.5rem;
            font-weight: 700;
            color: white;
            display: flex;
            align-items: center;
            gap: 0.75rem;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .navbar-brand-employee:hover {
            color: rgba(255, 255, 255, 0.9);
            transform: translateY(-1px);
        }
        
        .navbar-brand-icon {
            font-size: 1.75rem;
            background: rgba(255, 255, 255, 0.2);
            padding: 0.5rem;
            border-radius: 0.75rem;
            backdrop-filter: blur(10px);
        }
        
        .user-dropdown {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 1rem;
            padding: 0.5rem 1rem;
            transition: all 0.3s ease;
        }
        
        .user-dropdown:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: white;
            border: 2px solid rgba(255, 255, 255, 0.3);
        }
        
        .user-info {
            display: flex;
            flex-direction: column;
            margin-left: 0.75rem;
        }
        
        .user-name {
            font-weight: 600;
            color: white;
            font-size: 0.9rem;
            line-height: 1.2;
        }
        
        .user-role {
            font-size: 0.75rem;
            color: rgba(255, 255, 255, 0.8);
            line-height: 1.2;
        }
        
        .dropdown-menu-employee {
            border: none;
            border-radius: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.15);
            padding: 0.5rem;
            margin-top: 0.75rem;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .dropdown-item-employee {
            border-radius: 0.5rem;
            padding: 0.75rem 1rem;
            font-weight: 500;
            color: #374151;
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }
        
        .dropdown-item-employee:hover {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            transform: translateX(5px);
        }
        
        .dropdown-item-employee i {
            width: 20px;
            text-align: center;
            font-size: 0.9rem;
        }
        
        .nav-notification {
            position: relative;
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.75rem;
            padding: 0.5rem 0.75rem;
            margin-right: 1rem;
            transition: all 0.3s ease;
        }
        
        .nav-notification:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            border: 2px solid #667eea;
        }
        
        .mobile-menu-btn {
            background: rgba(255, 255, 255, 0.15);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 0.75rem;
            padding: 0.5rem 0.75rem;
            color: white;
            transition: all 0.3s ease;
        }
        
        .mobile-menu-btn:hover {
            background: rgba(255, 255, 255, 0.25);
            transform: translateY(-1px);
        }
        
        @media (max-width: 991.98px) {
            .user-info {
                display: none;
            }
            
            .user-dropdown {
                padding: 0.5rem;
            }
        }
        
        @media (max-width: 575.98px) {
            .navbar-brand-employee span:last-child {
                display: none;
            }
            
            .nav-notification span {
                display: none;
            }
        }
    </style>
</head>
<body>
    <!-- Enhanced Employee Navigation -->
    <nav class="navbar navbar-expand-lg employee-navbar">
        <div class="container-fluid">
            <!-- Brand Logo & Mobile Menu -->
            <div class="d-flex align-items-center">
                <button class="mobile-menu-btn d-lg-none me-2" type="button" data-bs-toggle="collapse" data-bs-target="#sidebarMenu">
                    <i class="fas fa-bars"></i>
                </button>
                <a class="navbar-brand-employee" href="employee/dashboard.php">
                    <div class="navbar-brand-icon">
                        <i class="fas fa-calendar-alt"></i>
                    </div>
                    <span>ELMS Portal</span>
                </a>
            </div>

            <!-- Navigation Items -->
            <div class="d-flex align-items-center">
                <!-- Notifications -->
                <div class="nav-notification position-relative">
                    <i class="fas fa-bell text-white"></i>
                    <span class="d-none d-md-inline ms-2 text-white">Notifications</span>
                    <span class="notification-badge">3</span>
                </div>

                <!-- User Dropdown -->
                <div class="dropdown">
                    <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown dropdown-toggle" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <div class="user-avatar">
                            <?php echo strtoupper(substr(htmlentities($employee->FirstName), 0, 1)); ?>
                        </div>
                        <div class="user-info d-none d-lg-block">
                            <span class="user-name"><?php echo htmlentities($employee->FirstName) . ' ' . htmlentities($employee->LastName); ?></span>
                            <span class="user-role"><?php echo htmlentities($employee->Department); ?></span>
                        </div>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end dropdown-menu-employee">
                        <li>
                            <a class="dropdown-item dropdown-item-employee" href="myprofile.php">
                                <i class="fas fa-user"></i>
                                My Profile
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-employee" href="emp-changepassword.php">
                                <i class="fas fa-key"></i>
                                Change Password
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item dropdown-item-employee" href="leavehistory.php">
                                <i class="fas fa-history"></i>
                                Leave History
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item dropdown-item-employee" href="apply-leave.php">
                                <i class="fas fa-plus-circle"></i>
                                Apply Leave
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <a class="dropdown-item dropdown-item-employee text-danger" href="logout.php">
                                <i class="fas fa-sign-out-alt"></i>
                                Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Enhanced dropdown interactions
        $(document).ready(function() {
            // Add smooth animations to dropdown
            $('.dropdown-toggle').on('show.bs.dropdown', function() {
                $(this).find('.user-dropdown').css({
                    'background': 'rgba(255, 255, 255, 0.3)',
                    'transform': 'translateY(-2px)'
                });
            });
            
            $('.dropdown-toggle').on('hide.bs.dropdown', function() {
                $(this).find('.user-dropdown').css({
                    'background': 'rgba(255, 255, 255, 0.15)',
                    'transform': 'translateY(0)'
                });
            });
            
            // Notification click handler
            $('.nav-notification').on('click', function(e) {
                e.preventDefault();
                // You can implement notification functionality here
                console.log('Notifications clicked');
            });
            
            // Mobile menu enhancement
            $('.mobile-menu-btn').on('click', function() {
                $(this).toggleClass('active');
            });
        });
    </script>
<?php } ?>