<?php
session_start();
include('includes/config.php');

// FIXED: Check for the correct session variables
if(!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) { 
    header('location:index.php');
    exit();
} else {

    $msg = "";
    $error = "";

    if(isset($_POST['apply'])) {
        $empid = $_SESSION['eid']; // Employee ID from session
        $leavetype = trim($_POST['leavetype']);
        $fromdate = $_POST['fromdate'];
        $todate = $_POST['todate'];
        $description = trim($_POST['description']);
        $status = 0; // 0 means Pending
        $isread = 0; // Default unread

        // Validate input
        if (empty($leavetype) || empty($fromdate) || empty($todate) || empty($description)) {
            $error = "All fields are required.";
        } elseif (strtotime($fromdate) > strtotime($todate)) {
            $error = "'To Date' cannot be earlier than 'From Date'.";
        } else {
            // Check for duplicate overlapping leaves
            $sqlCheck = "SELECT * FROM tblleaves WHERE empid=:empid AND 
                        ((FromDate BETWEEN :fromdate AND :todate) OR (ToDate BETWEEN :fromdate AND :todate))";
            $queryCheck = $dbh->prepare($sqlCheck);
            $queryCheck->bindParam(':empid', $empid, PDO::PARAM_STR);
            $queryCheck->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
            $queryCheck->bindParam(':todate', $todate, PDO::PARAM_STR);
            $queryCheck->execute();

            if($queryCheck->rowCount() > 0) {
                $error = "You have already applied for leave within this period.";
            } else {
                // Insert leave application
                $sql = "INSERT INTO tblleaves(LeaveType, FromDate, ToDate, Description, Status, IsRead, empid)
                        VALUES(:leavetype, :fromdate, :todate, :description, :status, :isread, :empid)";
                $query = $dbh->prepare($sql);
                $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
                $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
                $query->bindParam(':todate', $todate, PDO::PARAM_STR);
                $query->bindParam(':description', $description, PDO::PARAM_STR);
                $query->bindParam(':status', $status, PDO::PARAM_INT);
                $query->bindParam(':isread', $isread, PDO::PARAM_INT);
                $query->bindParam(':empid', $empid, PDO::PARAM_STR);

                if($query->execute()) {
                    $msg = "Leave applied successfully!";
                } else {
                    $error = "Something went wrong. Please try again.";
                }
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
    <title>Apply for Leave | Employee Leave Management System</title>
    
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
        
        .date-input-group { 
            position: relative; 
        }
        
        .date-input-group .form-control { 
            padding-right: 2.5rem; 
        }
        
        .date-input-group i {
            position: absolute; 
            right: 1rem; 
            top: 50%;
            transform: translateY(-50%); 
            color: #6b7280;
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
        
        .btn-secondary {
            background: #6b7280;
            border: none;
            border-radius: 12px;
            padding: 1rem 2.5rem;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
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
        
        .bg-info {
            background: linear-gradient(135deg, #06b6d4 0%, #0ea5e9 100%) !important;
        }
        
        .bg-light {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%) !important;
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
                                <i class="fas fa-paper-plane me-3"></i>Apply for Leave
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="employee/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-primary">Apply Leave</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <div class="row justify-content-center">
                        <div class="col-lg-10">
                            <!-- Leave Form -->
                            <div class="card">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-paper-plane me-2"></i>Apply for Leave
                                    </h5>
                                    <small class="text-white-50">Fill the form to submit your leave request</small>
                                </div>
                                <div class="card-body">
                                    <?php if($error) { ?>
                                        <div class="alert alert-danger">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-exclamation-circle me-3 fs-5"></i>
                                                <div class="flex-grow-1">
                                                    <strong>Error!</strong> <?php echo htmlentities($error); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } elseif($msg) { ?>
                                        <div class="alert alert-success">
                                            <div class="d-flex align-items-center">
                                                <i class="fas fa-check-circle me-3 fs-5"></i>
                                                <div class="flex-grow-1">
                                                    <strong>Success!</strong> <?php echo htmlentities($msg); ?>
                                                </div>
                                            </div>
                                        </div>
                                    <?php } ?>

                                    <form method="post" name="apply">
                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">Leave Type <span class="text-danger">*</span></label>
                                                <select class="form-control" name="leavetype" required>
                                                    <option value="">Select Leave Type</option>
                                                    <?php
                                                    $sql = "SELECT LeaveType FROM tblleavetype";
                                                    $query = $dbh->prepare($sql);
                                                    $query->execute();
                                                    $types = $query->fetchAll(PDO::FETCH_OBJ);
                                                    foreach($types as $type) {
                                                        echo '<option value="'.htmlentities($type->LeaveType).'">'.htmlentities($type->LeaveType).'</option>';
                                                    }
                                                    ?>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">From Date <span class="text-danger">*</span></label>
                                                <div class="date-input-group">
                                                    <input type="date" class="form-control" name="fromdate" required>
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                            </div>
                                            <div class="col-md-6 mb-4">
                                                <label class="form-label">To Date <span class="text-danger">*</span></label>
                                                <div class="date-input-group">
                                                    <input type="date" class="form-control" name="todate" required>
                                                    <i class="fas fa-calendar-alt"></i>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">Number of Days</label>
                                            <input type="text" class="form-control" name="duration" placeholder="Auto-calculated" readonly>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label">Description <span class="text-danger">*</span></label>
                                            <textarea class="form-control" name="description" rows="4" placeholder="Reason for leave..." required></textarea>
                                        </div>

                                        <div class="text-end">
                                            <a href="employee/dashboard.php" class="btn btn-secondary me-2">
                                                <i class="fas fa-times me-2"></i> Cancel
                                            </a>
                                            <button type="submit" name="apply" class="btn btn-primary">
                                                <i class="fas fa-paper-plane me-2"></i> Apply Leave
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Policy Info -->
                            <div class="card mt-4">
                                <div class="card-header bg-info">
                                    <h6 class="card-title mb-0 text-white">
                                        <i class="fas fa-info-circle me-2"></i> Leave Policy Guidelines
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Apply at least 3 days before leave</li>
                                        <li class="mb-2"><i class="fas fa-check text-success me-2"></i> Provide clear description</li>
                                        <li class="mb-2"><i class="fas fa-clock text-warning me-2"></i> Approval takes 1–2 business days</li>
                                        <li class="mb-0"><i class="fas fa-phone text-info me-2"></i> Contact admin for urgent requests</li>
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
            // Calculate number of days automatically
            function calculateDays() {
                const fromDate = new Date($('input[name="fromdate"]').val());
                const toDate = new Date($('input[name="todate"]').val());
                if(fromDate && toDate && fromDate <= toDate) {
                    const diff = Math.ceil((toDate - fromDate) / (1000 * 60 * 60 * 24)) + 1;
                    $('input[name="duration"]').val(diff + ' day(s)');
                } else {
                    $('input[name="duration"]').val('');
                }
            }

            $('input[name="fromdate"], input[name="todate"]').on('change', calculateDays);

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