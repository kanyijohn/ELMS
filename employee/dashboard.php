<?php
session_start();
include('../includes/config.php');

// FIXED: Check for the correct session variables
if(!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) { 
    header('location:../index.php');
    exit();
} else {
    $empid = $_SESSION['eid'];
    
    // Get employee details
    $sql = "SELECT * FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $employee = $query->fetch(PDO::FETCH_OBJ);
    
    // Get leave statistics
    $sql = "SELECT 
            COUNT(*) as total_applications,
            SUM(CASE WHEN Status = 1 THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN Status = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN Status = 2 THEN 1 ELSE 0 END) as rejected
            FROM tblleaves WHERE empid=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $stats = $query->fetch(PDO::FETCH_OBJ);
    
    // Get recent leave applications
    $sql = "SELECT * FROM tblleaves WHERE empid=:eid ORDER BY PostingDate DESC LIMIT 5";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $recent_leaves = $query->fetchAll(PDO::FETCH_OBJ);
    
    // Get upcoming approved leaves
    $sql = "SELECT * FROM tblleaves WHERE empid=:eid AND Status = 1 AND FromDate >= CURDATE() ORDER BY FromDate ASC LIMIT 3";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $upcoming_leaves = $query->fetchAll(PDO::FETCH_OBJ);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Dashboard | Employee Leave Management System</title>
    
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
        
        .stat-card {
            background: white;
            border-radius: 16px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.08);
            transition: all 0.3s ease;
            border: 1px solid #e2e8f0;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 35px rgba(0, 0, 0, 0.12);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1rem;
            font-size: 1.5rem;
        }
        
        .stat-icon.approved {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
            color: white;
        }
        
        .stat-icon.pending {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
        }
        
        .stat-icon.rejected {
            background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);
            color: white;
        }
        
        .stat-icon.total {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .stat-content h3 {
            font-size: 2rem;
            font-weight: 700;
            margin-bottom: 0.5rem;
            color: #1e293b;
        }
        
        .stat-content p {
            color: #64748b;
            margin: 0;
            font-weight: 500;
        }
        
        .btn-enhanced {
            display: block;
            border: none;
            border-radius: 12px;
            padding: 1.5rem;
            text-decoration: none;
            color: white;
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .btn-enhanced:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
            color: white;
        }
        
        .btn-primary {
            background: var(--primary-gradient);
        }
        
        .btn-success {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }
        
        .btn-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%);
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        }
        
        .btn-secondary {
            background: linear-gradient(135deg, #6b7280 0%, #4b5563 100%);
        }
        
        .badge-enhanced {
            padding: 0.5rem 1rem;
            border-radius: 2rem;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .badge-approved {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }
        
        .badge-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }
        
        .badge-rejected {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #dc2626;
        }
        
        .badge-active {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }
        
        .badge-inactive {
            background: linear-gradient(135deg, #f3f4f6 0%, #e5e7eb 100%);
            color: #6b7280;
        }
        
        .progress {
            border-radius: 1rem;
            background: #f1f5f9;
        }
        
        .progress-bar {
            border-radius: 1rem;
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
        <?php include '../includes/header.php'; ?>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar Container (Fixed beside content) -->
        <div class="sidebar-container" id="sidebarContainer">
            <?php include '../includes/sidebar.php'; ?>
        </div>

        <!-- Content Container (Beside sidebar) -->
        <div class="content-container">
            <div class="content-area">
                <div class="container-fluid">
                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <h1 class="page-title">
                                <i class="fas fa-tachometer-alt me-3"></i>Employee Dashboard
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item active text-primary">Dashboard</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Welcome Section -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card bg-primary text-white">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col-md-8">
                                            <h2 class="text-white mb-2">Welcome back, <?php echo htmlentities($employee->FirstName); ?>!</h2>
                                            <p class="text-white-50 mb-0">Here's your leave management overview for today.</p>
                                        </div>
                                        <div class="col-md-4 text-end">
                                            <div class="bg-white bg-opacity-20 rounded p-3 d-inline-block">
                                                <small class="text-white-50">Employee ID</small>
                                                <div class="h5 mb-0 text-white"><?php echo htmlentities($employee->EmpId); ?></div>
                                            </div>
                                            <div class="bg-white bg-opacity-20 rounded p-3 d-inline-block ms-2">
                                                <small class="text-white-50">Department</small>
                                                <div class="h5 mb-0 text-white"><?php echo htmlentities($employee->Department); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Stats Grid -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon approved">
                                    <i class="fas fa-check-circle"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?php echo $stats->approved ?? 0; ?></h3>
                                    <p>Approved Leaves</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon pending">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?php echo $stats->pending ?? 0; ?></h3>
                                    <p>Pending Leaves</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon rejected">
                                    <i class="fas fa-times-circle"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?php echo $stats->rejected ?? 0; ?></h3>
                                    <p>Rejected Leaves</p>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="stat-card">
                                <div class="stat-icon total">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="stat-content">
                                    <h3><?php echo $stats->total_applications ?? 0; ?></h3>
                                    <p>Total Applications</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions & Recent Activity -->
                    <div class="row">
                        <div class="col-lg-8">
                            <!-- Quick Actions -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-rocket me-2"></i>Quick Actions
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <a href="../apply-leave.php" class="btn-enhanced btn-primary w-100 h-100 py-3">
                                                <div class="text-center">
                                                    <i class="fas fa-plus-circle fa-2x mb-2"></i>
                                                    <h6>Apply for Leave</h6>
                                                    <small class="text-white-50">Submit a new leave request</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="../leavehistory.php" class="btn-enhanced btn-success w-100 h-100 py-3">
                                                <div class="text-center">
                                                    <i class="fas fa-history fa-2x mb-2"></i>
                                                    <h6>Leave History</h6>
                                                    <small class="text-white-50">View your past applications</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="../myprofile.php" class="btn-enhanced btn-info w-100 h-100 py-3">
                                                <div class="text-center">
                                                    <i class="fas fa-user fa-2x mb-2"></i>
                                                    <h6>My Profile</h6>
                                                    <small class="text-white-50">Update personal information</small>
                                                </div>
                                            </a>
                                        </div>
                                        <div class="col-md-6">
                                            <a href="../chatwith-admin.php" class="btn-enhanced btn-warning w-100 h-100 py-3">
                                                <div class="text-center">
                                                    <i class="fas fa-comments fa-2x mb-2"></i>
                                                    <h6>Contact Admin</h6>
                                                    <small class="text-white-50">Get help or ask questions</small>
                                                </div>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Leave Balance -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-chart-pie me-2"></i>Leave Balance Overview
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-semibold">Annual Leave</span>
                                                    <span class="text-muted">15/21 days</span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success" style="width: 71%"></div>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-semibold">Sick Leave</span>
                                                    <span class="text-muted">8/10 days</span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-info" style="width: 80%"></div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-semibold">Casual Leave</span>
                                                    <span class="text-muted">5/7 days</span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-warning" style="width: 71%"></div>
                                                </div>
                                            </div>
                                            <div class="mb-4">
                                                <div class="d-flex justify-content-between mb-2">
                                                    <span class="fw-semibold">Emergency Leave</span>
                                                    <span class="text-muted">3/5 days</span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-danger" style="width: 60%"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-lg-4">
                            <!-- Recent Activity -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-bell me-2"></i>Recent Activity
                                    </h5>
                                    <a href="../leavehistory.php" class="btn btn-secondary btn-sm">View All</a>
                                </div>
                                <div class="card-body">
                                    <?php if(count($recent_leaves) > 0) { 
                                        foreach($recent_leaves as $leave) {
                                            $statusClass = '';
                                            $statusIcon = '';
                                            if($leave->Status == 1) {
                                                $statusClass = 'text-success';
                                                $statusIcon = 'fa-check-circle';
                                            } elseif($leave->Status == 2) {
                                                $statusClass = 'text-danger';
                                                $statusIcon = 'fa-times-circle';
                                            } else {
                                                $statusClass = 'text-warning';
                                                $statusIcon = 'fa-clock';
                                            }
                                    ?>
                                    <div class="d-flex align-items-start mb-3">
                                        <div class="flex-shrink-0">
                                            <i class="fas <?php echo $statusIcon; ?> <?php echo $statusClass; ?> mt-1"></i>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-1"><?php echo htmlentities($leave->LeaveType); ?> application</p>
                                            <small class="text-muted"><?php echo htmlentities($leave->PostingDate); ?></small>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="badge-enhanced <?php 
                                                echo $leave->Status == 1 ? 'badge-approved' : 
                                                     ($leave->Status == 2 ? 'badge-rejected' : 'badge-pending'); 
                                            ?>">
                                                <?php 
                                                echo $leave->Status == 1 ? 'Approved' : 
                                                     ($leave->Status == 2 ? 'Rejected' : 'Pending'); 
                                                ?>
                                            </span>
                                        </div>
                                    </div>
                                    <?php }
                                    } else { ?>
                                        <div class="text-center py-3">
                                            <i class="fas fa-inbox fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No recent activity</p>
                                            <small class="text-muted">Apply for your first leave to get started</small>
                                        </div>
                                    <?php } ?>
                                </div>
                            </div>

                            <!-- Upcoming Leaves -->
                            <div class="card mt-4">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-calendar-check me-2"></i>Upcoming Approved Leaves
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <?php if(count($upcoming_leaves) > 0) {
                                        foreach($upcoming_leaves as $leave) {
                                    ?>
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="flex-shrink-0">
                                            <div class="bg-success rounded-circle d-flex align-items-center justify-content-center" 
                                                 style="width: 32px; height: 32px;">
                                                <i class="fas fa-calendar text-white" style="font-size: 0.8rem;"></i>
                                            </div>
                                        </div>
                                        <div class="flex-grow-1 ms-3">
                                            <p class="mb-1"><?php echo htmlentities($leave->LeaveType); ?></p>
                                            <small class="text-muted">
                                                <?php echo date('M j', strtotime($leave->FromDate)); ?> - 
                                                <?php echo date('M j', strtotime($leave->ToDate)); ?>
                                            </small>
                                        </div>
                                    </div>
                                    <?php }
                                    } else { ?>
                                        <div class="text-center py-2">
                                            <i class="fas fa-calendar-times fa-2x text-muted mb-2"></i>
                                            <p class="text-muted mb-0">No upcoming leaves</p>
                                            <small class="text-muted">Your approved leaves will appear here</small>
                                        </div>
                                    <?php } ?>
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

            // Add animation to stat cards
            $('.stat-card').hover(
                function() {
                    $(this).css('transform', 'translateY(-5px)');
                },
                function() {
                    $(this).css('transform', 'translateY(0)');
                }
            );

            // Quick action buttons animation
            $('.btn-enhanced').hover(
                function() {
                    $(this).find('i').css('transform', 'scale(1.1)');
                },
                function() {
                    $(this).find('i').css('transform', 'scale(1)');
                }
            );
        });
    </script>
</body>
</html>