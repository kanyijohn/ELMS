<?php
session_start();
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['eid'];
$error = "";
$msg = "";
$result = null;
$leaveid = 0;

// Get supervisor's department first
$sup_sql = "SELECT Department FROM tblemployees WHERE id = :supid";
$sup_query = $dbh->prepare($sup_sql);
$sup_query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$sup_query->execute();
$supervisor = $sup_query->fetch(PDO::FETCH_OBJ);

if (!$supervisor) {
    $error = "Supervisor information not found.";
} elseif(isset($_GET['leaveid'])) {
    $leaveid = intval($_GET['leaveid']);
    
    // Get leave details with supervisor department check
    $sql = "SELECT tblleaves.*, tblemployees.FirstName, tblemployees.LastName, 
            tblemployees.EmpId, tblemployees.Phonenumber, tblemployees.EmailId,
            tblemployees.Department
            FROM tblleaves 
            JOIN tblemployees ON tblleaves.empid = tblemployees.id 
            WHERE tblleaves.id = :leaveid 
            AND tblemployees.Department = :department";
    $query = $dbh->prepare($sql);
    $query->bindParam(':leaveid', $leaveid, PDO::PARAM_INT);
    $query->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if(!$result) {
        $error = "Leave application not found or you don't have permission to view it.";
    }
} else {
    $error = "No leave application specified.";
}

// Handle approval/rejection
if(isset($_POST['approve']) || isset($_POST['reject'])) {
    if (!$result) {
        $error = "Cannot process action. Leave application not found.";
    } else {
        $adminremark = trim($_POST['adminremark']);
        $status = isset($_POST['approve']) ? 1 : 2;
        
        if(empty($adminremark)) {
            $error = "Please provide remarks for your decision.";
        } else {
            $update_sql = "UPDATE tblleaves SET Status = :status, AdminRemark = :adminremark, 
                          AdminRemarkDate = NOW() WHERE id = :leaveid";
            $update_query = $dbh->prepare($update_sql);
            $update_query->bindParam(':status', $status, PDO::PARAM_INT);
            $update_query->bindParam(':adminremark', $adminremark, PDO::PARAM_STR);
            $update_query->bindParam(':leaveid', $leaveid, PDO::PARAM_INT);
            
            if($update_query->execute()) {
                $msg = "Leave application " . ($status == 1 ? "approved" : "rejected") . " successfully!";
                // Refresh data
                $query->execute();
                $result = $query->fetch(PDO::FETCH_OBJ);
                
                // Show success message and redirect
                echo "<script>alert('Leave application " . ($status == 1 ? "approved" : "rejected") . " successfully!'); window.location.href = 'leaves.php';</script>";
                exit();
            } else {
                $error = "Failed to update leave application. Please try again.";
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
    <title>Leave Application Details | Employee Leave Management System</title>
    
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
            cursor: pointer;
        }
        .btn-enhanced:hover {
            transform: translateY(-1px);
        }
        .btn-primary { background: #0d6efd; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-success { background: #198754; color: white; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-warning { background: #ffc107; color: #000; }
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .badge-approved {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .badge-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
        .badge-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        .bg-success { background: #198754 !important; }
        .bg-info { background: #0dcaf0 !important; }
        .bg-primary { background: #0d6efd !important; }
        
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
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .avatar-lg {
            width: 60px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 1.25rem;
        }
        
        .form-control-static {
            padding: 0.375rem 0;
            margin-bottom: 0;
            background: transparent;
            border: none;
            min-height: auto;
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
                <a href="leaves.php" class="btn-enhanced btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Leave Application Details</h1>
                <p class="text-muted mb-0">Review and take action on this leave request</p>
            </div>
            <div>
                <a href="leaves.php" class="btn-enhanced btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>
            </div>
        </div>

        <?php if(!empty($error)): ?>
            <div class="alert-modern alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?>
            </div>
            <div class="text-center mt-4">
                <a href="leaves.php" class="btn-enhanced btn-primary">Return to Leave Requests</a>
            </div>
        <?php elseif($result): ?>
        
        <?php if(!empty($msg)): ?>
            <div class="alert-modern alert-success">
                <i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Leave Details -->
            <div class="col-lg-8">
                <div class="enhanced-card">
                    <div class="card-header">
                        <h5><i class="fas fa-file-alt"></i> Application Information</h5>
                        <span class="badge-enhanced <?php 
                            echo ($result->Status == 1) ? 'badge-approved' : 
                                 (($result->Status == 2) ? 'badge-rejected' : 'badge-pending'); 
                        ?>">
                            <i class="fas fa-<?php 
                                echo ($result->Status == 1) ? 'check' : 
                                     (($result->Status == 2) ? 'times' : 'clock'); 
                            ?>"></i>
                            <?php 
                                echo ($result->Status == 1) ? 'Approved' : 
                                     (($result->Status == 2) ? 'Rejected' : 'Pending'); 
                            ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Employee Name</label>
                                    <p class="form-control-static"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Employee ID</label>
                                    <p class="form-control-static"><?php echo htmlentities($result->EmpId); ?></p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Department</label>
                                    <p class="form-control-static"><?php echo htmlentities($result->Department); ?></p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Leave Type</label>
                                    <p class="form-control-static">
                                        <span class="badge-enhanced bg-info">
                                            <?php echo htmlentities($result->LeaveType); ?>
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">From Date</label>
                                    <p class="form-control-static">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        <?php echo htmlentities($result->FromDate); ?>
                                    </p>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">To Date</label>
                                    <p class="form-control-static">
                                        <i class="fas fa-calendar-alt text-primary me-2"></i>
                                        <?php echo htmlentities($result->ToDate); ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Duration</label>
                            <p class="form-control-static">
                                <i class="fas fa-clock text-warning me-2"></i>
                                <?php 
                                $from = new DateTime($result->FromDate);
                                $to = new DateTime($result->ToDate);
                                $duration = $to->diff($from)->days + 1;
                                echo $duration . ' day(s)';
                                ?>
                            </p>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Description</label>
                            <div class="border rounded p-3 bg-light">
                                <?php echo htmlentities($result->Description); ?>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Application Date</label>
                                    <p class="form-control-static"><?php echo htmlentities($result->PostingDate); ?></p>
                                </div>
                            </div>
                            <?php if($result->Status != 0) { ?>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">
                                        <?php echo ($result->Status == 1) ? 'Approval' : 'Rejection'; ?> Date
                                    </label>
                                    <p class="form-control-static"><?php echo htmlentities($result->AdminRemarkDate); ?></p>
                                </div>
                            </div>
                            <?php } ?>
                        </div>

                        <?php if(!empty($result->AdminRemark)) { ?>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Admin Remarks</label>
                            <div class="border rounded p-3 bg-light">
                                <?php echo htmlentities($result->AdminRemark); ?>
                            </div>
                        </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Action Panel -->
            <div class="col-lg-4">
                <!-- Employee Contact -->
                <div class="enhanced-card mb-4">
                    <div class="card-header">
                        <h6><i class="fas fa-user-circle"></i> Employee Contact</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex align-items-center mb-3">
                            <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                <span class="text-white fw-bold">
                                    <?php echo substr(htmlentities($result->FirstName), 0, 1); ?>
                                </span>
                            </div>
                            <div>
                                <h6 class="mb-1"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?></h6>
                                <small class="text-muted"><?php echo htmlentities($result->Department); ?></small>
                            </div>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-envelope text-muted me-2"></i>
                            <small><?php echo htmlentities($result->EmailId); ?></small>
                        </div>
                        <div class="mb-2">
                            <i class="fas fa-phone text-muted me-2"></i>
                            <small><?php echo htmlentities($result->Phonenumber) ?: 'Not provided'; ?></small>
                        </div>
                    </div>
                </div>

                <!-- Approval Actions -->
                <?php if($result->Status == 0) { ?>
                <div class="enhanced-card">
                    <div class="card-header">
                        <h6><i class="fas fa-tasks"></i> Take Action</h6>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Add Remarks <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="adminremark" rows="3" 
                                          placeholder="Provide your remarks and decision reason..." required></textarea>
                                <div class="form-text">Your remarks will be visible to the employee.</div>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="approve" class="btn-enhanced btn-success">
                                    <i class="fas fa-check"></i> Approve Leave
                                </button>
                                <button type="submit" name="reject" class="btn-enhanced btn-danger">
                                    <i class="fas fa-times"></i> Reject Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php } else { ?>
                <div class="enhanced-card">
                    <div class="card-header">
                        <h6><i class="fas fa-info-circle"></i> Application Status</h6>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3">
                            <i class="fas fa-<?php echo ($result->Status == 1) ? 'check-circle text-success' : 'times-circle text-danger'; ?> fa-3x"></i>
                        </div>
                        <h5 class="<?php echo ($result->Status == 1) ? 'text-success' : 'text-danger'; ?>">
                            <?php echo ($result->Status == 1) ? 'Approved' : 'Rejected'; ?>
                        </h5>
                        <p class="text-muted small">This application has been processed.</p>
                        <?php if(!empty($result->AdminRemark)) { ?>
                        <div class="mt-3 p-2 bg-light rounded">
                            <small class="text-muted">Remarks: <?php echo htmlentities($result->AdminRemark); ?></small>
                        </div>
                        <?php } ?>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>