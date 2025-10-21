<?php
session_start();
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['eid'];
$error = "";
$results = [];
$rowCount = 0;

// Get supervisor's department
$sql = "SELECT Department FROM tblemployees WHERE id=:supid";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT); // Fixed: using $supervisor_id instead of $supid
$query->execute();
$supervisor = $query->fetch(PDO::FETCH_OBJ);

if (!$supervisor) {
    $error = "Supervisor information not found. Please contact administrator.";
} else {
    // Get rejected leaves for supervisor's department
    $sql = "SELECT tblleaves.id as lid, tblemployees.FirstName, tblemployees.LastName, 
            tblemployees.EmpId, tblemployees.id, tblleaves.LeaveType, tblleaves.PostingDate,
            tblleaves.FromDate, tblleaves.ToDate, tblleaves.Description, tblleaves.AdminRemark,
            tblleaves.AdminRemarkDate
            FROM tblleaves 
            JOIN tblemployees ON tblleaves.empid = tblemployees.id 
            WHERE tblleaves.Status = 2 
            AND tblemployees.Department = :department
            ORDER BY tblleaves.AdminRemarkDate DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $rowCount = $query->rowCount();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rejected Leave History | Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
    
    <style>
        .btn-enhanced {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
        }
        .btn-enhanced:hover {
            transform: translateY(-1px);
        }
        .btn-primary { background: #0d6efd; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #198754; color: white; }
        .btn-info { background: #0dcaf0; color: #000; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
        
        .enhanced-card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .enhanced-card .card-header {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .enhanced-card .card-body {
            padding: 1.5rem;
        }
        
        .badge-enhanced {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
        .bg-success { background: #198754 !important; }
        .bg-info { background: #0dcaf0 !important; }
        .bg-secondary { background: #6c757d !important; }
        .bg-warning { background: #ffc107 !important; color: #000; }
        .bg-danger { background: #dc3545 !important; }
        
        .table-modern th {
            background: #f8f9fa;
            font-weight: 600;
            border-bottom: 2px solid #dee2e6;
        }
        
        .avatar-sm, .avatar-lg {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }
        .avatar-sm {
            width: 36px;
            height: 36px;
            font-size: 0.875rem;
        }
        .avatar-lg {
            width: 60px;
            height: 60px;
            font-size: 1.25rem;
        }
        
        .alert-modern {
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
    </style>
</head>
<body>
    <!-- Supervisor Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-user-tie me-2"></i>
                ELMS - Supervisor Portal
            </a>
            <div class="d-flex align-items-center">
                <?php if(!$error): ?>
                <span class="badge-enhanced bg-warning me-3">
                    <i class="fas fa-users"></i> <?php echo htmlentities($supervisor->Department); ?>
                </span>
                <?php endif; ?>
                <a href="dashboard.php" class="btn-enhanced btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Error Display -->
        <?php if($error): ?>
            <div class="alert-modern alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?>
            </div>
            <div class="text-center mt-4">
                <a href="dashboard.php" class="btn-enhanced btn-primary">Return to Dashboard</a>
            </div>
        <?php else: ?>
        
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Rejected Leave History</h1>
                <p class="text-muted mb-0">View all rejected leave applications from your team</p>
            </div>
            <div class="d-flex gap-2">
                <a href="leaves.php" class="btn-enhanced btn-warning">
                    <i class="fas fa-clock"></i> Pending Requests
                </a>
                <a href="approvedleave-history.php" class="btn-enhanced btn-success">
                    <i class="fas fa-check-circle"></i> Approved Leaves
                </a>
            </div>
        </div>

        <!-- Department Info -->
        <div class="enhanced-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1"><?php echo htmlentities($supervisor->Department); ?> Department</h5>
                        <p class="text-muted mb-0">Showing <strong><?php echo $rowCount; ?></strong> rejected leave applications</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge-enhanced badge-rejected">
                            <i class="fas fa-times-circle"></i> <?php echo $rowCount; ?> Rejected
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Rejected Leaves Table -->
        <div class="enhanced-card">
            <div class="card-header">
                <h5><i class="fas fa-times-circle"></i> Rejected Applications</h5>
            </div>
            <div class="card-body">
                <?php if($rowCount > 0) { ?>
                <div class="table-responsive">
                    <table class="table table-modern">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Employee</th>
                                <th>Leave Type</th>
                                <th>Leave Period</th>
                                <th>Duration</th>
                                <th>Applied On</th>
                                <th>Rejected On</th>
                                <th>Rejection Reason</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $cnt = 1;
                            foreach($results as $result) {
                                $from = new DateTime($result->FromDate);
                                $to = new DateTime($result->ToDate);
                                $duration = $to->diff($from)->days + 1;
                            ?>
                            <tr>
                                <td><?php echo htmlentities($cnt); ?></td>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <div class="avatar-sm bg-danger rounded-circle d-flex align-items-center justify-content-center me-3">
                                            <span class="text-white fw-bold">
                                                <?php echo substr(htmlentities($result->FirstName), 0, 1); ?>
                                            </span>
                                        </div>
                                        <div>
                                            <div class="fw-semibold"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?></div>
                                            <small class="text-muted"><?php echo htmlentities($result->EmpId); ?></small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge-enhanced bg-info">
                                        <i class="fas fa-calendar"></i>
                                        <?php echo htmlentities($result->LeaveType); ?>
                                    </span>
                                </td>
                                <td>
                                    <small>
                                        <i class="fas fa-calendar-alt text-primary me-1"></i>
                                        <?php echo htmlentities($result->FromDate); ?><br>
                                        <i class="fas fa-arrow-right text-muted me-1"></i>
                                        <?php echo htmlentities($result->ToDate); ?>
                                    </small>
                                </td>
                                <td>
                                    <span class="badge-enhanced bg-secondary">
                                        <?php echo $duration; ?> day(s)
                                    </span>
                                </td>
                                <td><?php echo htmlentities($result->PostingDate); ?></td>
                                <td>
                                    <span class="text-danger">
                                        <i class="fas fa-times me-1"></i>
                                        <?php echo htmlentities($result->AdminRemarkDate); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if(!empty($result->AdminRemark)) { ?>
                                        <span class="text-muted" data-bs-toggle="tooltip" 
                                              title="<?php echo htmlentities($result->AdminRemark); ?>">
                                            <i class="fas fa-comment"></i> View Reason
                                        </span>
                                    <?php } else { ?>
                                        <span class="text-muted">No reason provided</span>
                                    <?php } ?>
                                </td>
                                <td>
                                    <button class="btn-enhanced btn-primary btn-sm" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#detailsModal<?php echo $cnt; ?>">
                                        <i class="fas fa-eye"></i> Details
                                    </button>
                                </td>
                            </tr>

                            <!-- Details Modal -->
                            <div class="modal fade" id="detailsModal<?php echo $cnt; ?>" tabindex="-1">
                                <div class="modal-dialog modal-lg">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h5 class="modal-title">Rejected Leave Details</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="row mb-4">
                                                <div class="col-md-6">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-lg bg-danger rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <span class="text-white fw-bold">
                                                                <?php echo substr(htmlentities($result->FirstName), 0, 1); ?>
                                                            </span>
                                                        </div>
                                                        <div>
                                                            <h6 class="mb-1"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?></h6>
                                                            <small class="text-muted"><?php echo htmlentities($result->EmpId); ?></small>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6 text-end">
                                                    <span class="badge-enhanced badge-rejected">
                                                        <i class="fas fa-times"></i> Rejected
                                                    </span>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Leave Type</label>
                                                        <p><?php echo htmlentities($result->LeaveType); ?></p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Duration</label>
                                                        <p><?php echo $duration; ?> day(s)</p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">From Date</label>
                                                        <p><?php echo htmlentities($result->FromDate); ?></p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">To Date</label>
                                                        <p><?php echo htmlentities($result->ToDate); ?></p>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Employee's Description</label>
                                                <div class="border rounded p-3 bg-light">
                                                    <?php echo htmlentities($result->Description); ?>
                                                </div>
                                            </div>

                                            <?php if(!empty($result->AdminRemark)) { ?>
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold">Rejection Reason</label>
                                                <div class="border rounded p-3 bg-light border-danger">
                                                    <i class="fas fa-exclamation-circle text-danger me-2"></i>
                                                    <?php echo htmlentities($result->AdminRemark); ?>
                                                </div>
                                            </div>
                                            <?php } ?>

                                            <div class="row">
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Applied On</label>
                                                        <p><?php echo htmlentities($result->PostingDate); ?></p>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="mb-3">
                                                        <label class="form-label fw-semibold">Rejected On</label>
                                                        <p><?php echo htmlentities($result->AdminRemarkDate); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn-enhanced btn-secondary" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $cnt++; } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                    <div class="text-center py-5">
                        <i class="fas fa-times-circle fa-3x text-danger mb-3"></i>
                        <h4 class="text-muted">No Rejected Leaves</h4>
                        <p class="text-muted">There are no rejected leave applications in your department.</p>
                        <a href="leaves.php" class="btn-enhanced btn-primary">
                            <i class="fas fa-clipboard-list"></i> View Pending Requests
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Statistics -->
        <?php if($rowCount > 0) { ?>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-danger"><?php echo $rowCount; ?></h3>
                        <p class="text-muted mb-0">Total Rejected</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-primary">
                            <?php
                            // Get this month's rejections
                            $sql = "SELECT COUNT(*) as this_month FROM tblleaves 
                                    JOIN tblemployees ON tblleaves.empid = tblemployees.id 
                                    WHERE tblemployees.Department = :department 
                                    AND tblleaves.Status = 2 
                                    AND MONTH(tblleaves.AdminRemarkDate) = MONTH(CURRENT_DATE())";
                            $query2 = $dbh->prepare($sql);
                            $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
                            $query2->execute();
                            $this_month = $query2->fetch(PDO::FETCH_OBJ);
                            echo $this_month ? $this_month->this_month : 0;
                            ?>
                        </h3>
                        <p class="text-muted mb-0">This Month</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-info">
                            <?php
                            // Get rejection rate
                            $sql = "SELECT 
                                    COUNT(*) as total,
                                    SUM(CASE WHEN Status = 2 THEN 1 ELSE 0 END) as rejected
                                    FROM tblleaves 
                                    JOIN tblemployees ON tblleaves.empid = tblemployees.id 
                                    WHERE tblemployees.Department = :department";
                            $query2 = $dbh->prepare($sql);
                            $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
                            $query2->execute();
                            $rates = $query2->fetch(PDO::FETCH_OBJ);
                            $rejection_rate = $rates && $rates->total > 0 ? round(($rates->rejected / $rates->total) * 100, 1) : 0;
                            echo $rejection_rate . '%';
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Rejection Rate</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">
                            <?php
                            // Get most common rejection reason count
                            $sql = "SELECT COUNT(*) as count 
                                    FROM tblleaves 
                                    JOIN tblemployees ON tblleaves.empid = tblemployees.id 
                                    WHERE tblemployees.Department = :department 
                                    AND tblleaves.Status = 2 
                                    AND AdminRemark IS NOT NULL 
                                    GROUP BY AdminRemark 
                                    ORDER BY count DESC 
                                    LIMIT 1";
                            $query2 = $dbh->prepare($sql);
                            $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
                            $query2->execute();
                            $common_reason = $query2->fetch(PDO::FETCH_OBJ);
                            echo $common_reason ? $common_reason->count : '0';
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Most Common Reason</p>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });
    </script>
</body>
</html>