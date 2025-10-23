<?php
session_start();
include('includes/config.php');

// FIXED: Check for the correct session variables
if(!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) { 
    header('location:index.php');
    exit();
} else {
    $empid = $_SESSION['eid'];
    
    // Get employee details
    $sql = "SELECT * FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $employee = $query->fetch(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile | Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: var(--primary-gradient);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            font-weight: 700;
            color: white;
            margin: 0 auto 1.5rem;
            border: 4px solid white;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .info-item {
            display: flex;
            align-items: flex-start;
            margin-bottom: 1rem;
            padding: 1rem;
            background: #f8fafc;
            border-radius: 12px;
            border-left: 4px solid #4f46e5;
        }
        
        .info-item i {
            width: 20px;
            margin-right: 1rem;
            margin-top: 0.25rem;
            color: #4f46e5;
        }
        
        .info-label {
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.25rem;
        }
        
        .info-value {
            color: #6b7280;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
            border: none;
            border-radius: 12px;
            padding: 0.75rem 1.5rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.35);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
        }
        
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
            border: 1px solid #f1f5f9;
        }
        
        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .stat-label {
            color: #6b7280;
            font-size: 0.875rem;
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
                                <i class="fas fa-user me-3"></i>My Profile
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="employee/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-primary">My Profile</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Statistics -->
                    <div class="stats-grid">
                        <div class="stat-card">
                            <div class="stat-number text-primary">
                                <?php
                                $sql_approved = "SELECT COUNT(*) as count FROM tblleaves WHERE empid=:empid AND Status=1";
                                $query_approved = $dbh->prepare($sql_approved);
                                $query_approved->bindParam(':empid', $empid, PDO::PARAM_STR);
                                $query_approved->execute();
                                $result_approved = $query_approved->fetch(PDO::FETCH_OBJ);
                                echo $result_approved->count;
                                ?>
                            </div>
                            <div class="stat-label">Approved Leaves</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-warning">
                                <?php
                                $sql_pending = "SELECT COUNT(*) as count FROM tblleaves WHERE empid=:empid AND Status=0";
                                $query_pending = $dbh->prepare($sql_pending);
                                $query_pending->bindParam(':empid', $empid, PDO::PARAM_STR);
                                $query_pending->execute();
                                $result_pending = $query_pending->fetch(PDO::FETCH_OBJ);
                                echo $result_pending->count;
                                ?>
                            </div>
                            <div class="stat-label">Pending Leaves</div>
                        </div>
                        <div class="stat-card">
                            <div class="stat-number text-danger">
                                <?php
                                $sql_rejected = "SELECT COUNT(*) as count FROM tblleaves WHERE empid=:empid AND Status=2";
                                $query_rejected = $dbh->prepare($sql_rejected);
                                $query_rejected->bindParam(':empid', $empid, PDO::PARAM_STR);
                                $query_rejected->execute();
                                $result_rejected = $query_rejected->fetch(PDO::FETCH_OBJ);
                                echo $result_rejected->count;
                                ?>
                            </div>
                            <div class="stat-label">Rejected Leaves</div>
                        </div>
                    </div>

                    <div class="row">
                        <!-- Profile Information -->
                        <div class="col-lg-8">
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-user-circle me-2"></i>Personal Information
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="profile-avatar">
                                        <?php echo strtoupper(substr(htmlentities($employee->FirstName), 0, 1)); ?>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-id-card"></i>
                                                <div>
                                                    <div class="info-label">Employee ID</div>
                                                    <div class="info-value"><?php echo htmlentities($employee->EmpId); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-user"></i>
                                                <div>
                                                    <div class="info-label">Full Name</div>
                                                    <div class="info-value"><?php echo htmlentities($employee->FirstName) . ' ' . htmlentities($employee->LastName); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-envelope"></i>
                                                <div>
                                                    <div class="info-label">Email Address</div>
                                                    <div class="info-value"><?php echo htmlentities($employee->EmailId); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-phone"></i>
                                                <div>
                                                    <div class="info-label">Phone Number</div>
                                                    <div class="info-value"><?php echo htmlentities($employee->Phonenumber); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-building"></i>
                                                <div>
                                                    <div class="info-label">Department</div>
                                                    <div class="info-value"><?php echo htmlentities($employee->Department); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-briefcase"></i>
                                                <div>
                                                    <div class="info-label">Role</div>
                                                    <div class="info-value"><?php echo htmlentities($employee->Role); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-calendar"></i>
                                                <div>
                                                    <div class="info-label">Registration Date</div>
                                                    <div class="info-value">
                                                        <?php 
                                                        $regDate = new DateTime($employee->RegDate);
                                                        echo htmlentities($regDate->format('F j, Y'));
                                                        ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="info-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <div>
                                                    <div class="info-label">Address</div>
                                                    <div class="info-value"><?php echo htmlentities($employee->Address); ?></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-lg-4">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-cog me-2"></i>Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="d-grid gap-2">
                                        <a href="emp-changepassword.php" class="btn btn-primary">
                                            <i class="fas fa-key me-2"></i>Change Password
                                        </a>
                                        <a href="apply-leave.php" class="btn btn-outline-primary">
                                            <i class="fas fa-paper-plane me-2"></i>Apply for Leave
                                        </a>
                                        <a href="leavehistory.php" class="btn btn-outline-primary">
                                            <i class="fas fa-history me-2"></i>View Leave History
                                        </a>
                                        <a href="chatwith-admin.php" class="btn btn-outline-primary">
                                            <i class="fas fa-comments me-2"></i>Contact Admin
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <!-- Account Status -->
                            <div class="card mt-4">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-shield-alt me-2"></i>Account Status
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <div class="mb-3">
                                            <i class="fas fa-check-circle fa-3x text-success"></i>
                                        </div>
                                        <h5 class="text-success">Active</h5>
                                        <p class="text-muted mb-0">Your account is active and in good standing</p>
                                    </div>
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
<?php } ?>