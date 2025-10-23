<?php
session_start();
include('includes/config.php');

// FIXED: Check for the correct session variables
if(!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) { 
    header('location:index.php');
    exit();
} else {
    $empid = $_SESSION['eid'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leave History | Employee Leave Management System</title>
    
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
        
        .table {
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
        }
        
        .table thead th {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border-bottom: 2px solid #e2e8f0;
            font-weight: 600;
            color: #374151;
            padding: 1rem;
        }
        
        .table tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f1f5f9;
        }
        
        .table tbody tr:hover {
            background-color: #f8fafc;
        }
        
        .badge {
            padding: 0.5rem 0.75rem;
            border-radius: 0.5rem;
            font-weight: 600;
            font-size: 0.75rem;
        }
        
        .badge-pending {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            color: #92400e;
        }
        
        .badge-approved {
            background: linear-gradient(135deg, #d1fae5 0%, #a7f3d0 100%);
            color: #065f46;
        }
        
        .badge-rejected {
            background: linear-gradient(135deg, #fee2e2 0%, #fecaca 100%);
            color: #991b1b;
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
        
        .no-leaves {
            text-align: center;
            padding: 3rem;
            color: #6b7280;
        }
        
        .no-leaves i {
            font-size: 3rem;
            margin-bottom: 1rem;
            color: #d1d5db;
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
                                <i class="fas fa-history me-3"></i>Leave History
                            </h1>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb">
                                    <li class="breadcrumb-item"><a href="employee/dashboard.php" class="text-decoration-none">Dashboard</a></li>
                                    <li class="breadcrumb-item active text-primary">Leave History</li>
                                </ol>
                            </nav>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="text-success mb-2">
                                        <i class="fas fa-check-circle fa-2x"></i>
                                    </div>
                                    <h3 class="text-success">
                                        <?php
                                        $sql_approved = "SELECT COUNT(*) as count FROM tblleaves WHERE empid=:empid AND Status=1";
                                        $query_approved = $dbh->prepare($sql_approved);
                                        $query_approved->bindParam(':empid', $empid, PDO::PARAM_STR);
                                        $query_approved->execute();
                                        $result_approved = $query_approved->fetch(PDO::FETCH_OBJ);
                                        echo $result_approved->count;
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Approved Leaves</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="text-warning mb-2">
                                        <i class="fas fa-clock fa-2x"></i>
                                    </div>
                                    <h3 class="text-warning">
                                        <?php
                                        $sql_pending = "SELECT COUNT(*) as count FROM tblleaves WHERE empid=:empid AND Status=0";
                                        $query_pending = $dbh->prepare($sql_pending);
                                        $query_pending->bindParam(':empid', $empid, PDO::PARAM_STR);
                                        $query_pending->execute();
                                        $result_pending = $query_pending->fetch(PDO::FETCH_OBJ);
                                        echo $result_pending->count;
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Pending Leaves</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card">
                                <div class="card-body text-center">
                                    <div class="text-danger mb-2">
                                        <i class="fas fa-times-circle fa-2x"></i>
                                    </div>
                                    <h3 class="text-danger">
                                        <?php
                                        $sql_rejected = "SELECT COUNT(*) as count FROM tblleaves WHERE empid=:empid AND Status=2";
                                        $query_rejected = $dbh->prepare($sql_rejected);
                                        $query_rejected->bindParam(':empid', $empid, PDO::PARAM_STR);
                                        $query_rejected->execute();
                                        $result_rejected = $query_rejected->fetch(PDO::FETCH_OBJ);
                                        echo $result_rejected->count;
                                        ?>
                                    </h3>
                                    <p class="text-muted mb-0">Rejected Leaves</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leave History Table -->
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">
                                <i class="fas fa-list me-2"></i>Your Leave Applications
                            </h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql = "SELECT tblleaves.id as lid, tblleaves.LeaveType, tblleaves.FromDate, tblleaves.ToDate, 
                                   tblleaves.Description, tblleaves.Status, tblleaves.PostingDate, 
                                   tblemployees.FirstName, tblemployees.LastName 
                                   FROM tblleaves 
                                   JOIN tblemployees ON tblleaves.empid = tblemployees.id 
                                   WHERE tblleaves.empid = :empid 
                                   ORDER BY tblleaves.PostingDate DESC";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':empid', $empid, PDO::PARAM_STR);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            $cnt = 1;

                            if($query->rowCount() > 0) {
                            ?>
                            <div class="table-responsive">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Leave Type</th>
                                            <th>From Date</th>
                                            <th>To Date</th>
                                            <th>Description</th>
                                            <th>Posting Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach($results as $result) { 
                                            $fromDate = new DateTime($result->FromDate);
                                            $toDate = new DateTime($result->ToDate);
                                            $postingDate = new DateTime($result->PostingDate);
                                        ?>
                                        <tr>
                                            <td><?php echo htmlentities($cnt); ?></td>
                                            <td><?php echo htmlentities($result->LeaveType); ?></td>
                                            <td><?php echo htmlentities($fromDate->format('d/m/Y')); ?></td>
                                            <td><?php echo htmlentities($toDate->format('d/m/Y')); ?></td>
                                            <td><?php echo htmlentities($result->Description); ?></td>
                                            <td><?php echo htmlentities($postingDate->format('d/m/Y')); ?></td>
                                            <td>
                                                <?php 
                                                $status = $result->Status;
                                                if($status == 0) {
                                                    echo '<span class="badge badge-pending">Pending</span>';
                                                } elseif($status == 1) {
                                                    echo '<span class="badge badge-approved">Approved</span>';
                                                } elseif($status == 2) {
                                                    echo '<span class="badge badge-rejected">Rejected</span>';
                                                }
                                                ?>
                                            </td>
                                        </tr>
                                        <?php $cnt++; } ?>
                                    </tbody>
                                </table>
                            </div>
                            <?php } else { ?>
                            <div class="no-leaves">
                                <i class="fas fa-inbox"></i>
                                <h4>No Leave Applications Found</h4>
                                <p class="text-muted">You haven't applied for any leaves yet.</p>
                                <a href="apply-leave.php" class="btn btn-primary mt-3">
                                    <i class="fas fa-plus me-2"></i>Apply for Leave
                                </a>
                            </div>
                            <?php } ?>
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