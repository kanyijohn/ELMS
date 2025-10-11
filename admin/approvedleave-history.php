<?php
session_start();
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

// ✅ Fetch all approved leave requests
try {
    $sql = "SELECT 
                tblleaves.id AS leave_id,
                tblemployees.FirstName,
                tblemployees.LastName,
                tblemployees.EmpId,
                tblleaves.LeaveType,
                tblleaves.FromDate,
                tblleaves.ToDate,
                tblleaves.Description,
                tblleaves.PostingDate,
                tblleaves.AdminRemark,
                tblleaves.AdminRemarkDate
            FROM tblleaves
            JOIN tblemployees ON tblleaves.empid = tblemployees.id
            WHERE tblleaves.Status = 1
            ORDER BY tblleaves.AdminRemarkDate DESC";

    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
} catch (Exception $e) {
    $results = [];
    error_log("Error fetching approved leaves: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approved Leave History | Employee Leave Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include('includes/sidebar.php'); ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Approved Leave History</h1>
                    <p class="text-muted mb-0">View all approved leave applications</p>
                </div>
                <div class="d-flex gap-2">
                    <a href="leaves.php" class="btn-enhanced btn-warning">
                        <i class="fas fa-clock"></i> Pending Requests
                    </a>
                    <a href="notapproved-leaves.php" class="btn-enhanced btn-danger">
                        <i class="fas fa-times-circle"></i> Rejected Leaves
                    </a>
                </div>
            </div>

            <!-- Approved Leaves Table -->
            <div class="enhanced-card">
                <div class="card-header">
                    <h5><i class="fas fa-check-circle"></i> Approved Applications</h5>
                    <span class="badge-enhanced badge-approved">
                        <?php echo isset($query) ? $query->rowCount() : 0; ?> approved
                    </span>
                </div>
                <div class="card-body">
                    <?php if (!empty($results)) { ?>
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
                                    <th>Approved On</th>
                                    <th>Admin Remarks</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $cnt = 1; foreach ($results as $result) { 
                                    // ✅ Define $duration safely before use
                                    $duration = "N/A";
                                    try {
                                        $from = new DateTime($result->FromDate);
                                        $to = new DateTime($result->ToDate);
                                        $duration = $to->diff($from)->days + 1;
                                    } catch (Exception $e) {
                                        $duration = "N/A";
                                    }
                                ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-success rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 36px; height: 36px;">
                                                <span class="text-white fw-bold">
                                                    <?php echo substr(htmlentities($result->FirstName), 0, 1); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold">
                                                    <?php echo htmlentities($result->FirstName) . ' ' . htmlentities($result->LastName); ?>
                                                </div>
                                                <small class="text-muted"><?php echo htmlentities($result->EmpId); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge bg-info">
                                            <i class="fas fa-calendar"></i> <?php echo htmlentities($result->LeaveType); ?>
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
                                        <span class="badge bg-secondary">
                                            <?php echo $duration . ' day(s)'; ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlentities($result->PostingDate); ?></td>
                                    <td>
                                        <span class="text-success">
                                            <i class="fas fa-check me-1"></i>
                                            <?php echo htmlentities($result->AdminRemarkDate); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if (!empty($result->AdminRemark)) { ?>
                                            <span class="text-muted" data-bs-toggle="tooltip" 
                                                  title="<?php echo htmlentities($result->AdminRemark); ?>">
                                                <i class="fas fa-comment"></i> View
                                            </span>
                                        <?php } else { echo '<span class="text-muted">-</span>'; } ?>
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
                                                <h5 class="modal-title">Approved Leave Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="row mb-4">
                                                    <div class="col-md-6">
                                                        <div class="d-flex align-items-center">
                                                            <div class="avatar-lg bg-success rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                                 style="width: 60px; height: 60px;">
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
                                                        <span class="badge bg-success">
                                                            <i class="fas fa-check"></i> Approved
                                                        </span>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="fw-semibold">Leave Type</label>
                                                        <p><?php echo htmlentities($result->LeaveType); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="fw-semibold">Duration</label>
                                                        <p><?php echo is_numeric($duration) ? $duration . ' day(s)' : 'N/A'; ?></p>
                                                    </div>
                                                </div>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="fw-semibold">From Date</label>
                                                        <p><?php echo htmlentities($result->FromDate); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="fw-semibold">To Date</label>
                                                        <p><?php echo htmlentities($result->ToDate); ?></p>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="fw-semibold">Description</label>
                                                    <div class="border rounded p-3 bg-light">
                                                        <?php echo htmlentities($result->Description); ?>
                                                    </div>
                                                </div>

                                                <?php if (!empty($result->AdminRemark)) { ?>
                                                <div class="mb-3">
                                                    <label class="fw-semibold">Approval Remarks</label>
                                                    <div class="border rounded p-3 bg-light">
                                                        <?php echo htmlentities($result->AdminRemark); ?>
                                                    </div>
                                                </div>
                                                <?php } ?>

                                                <div class="row">
                                                    <div class="col-md-6">
                                                        <label class="fw-semibold">Applied On</label>
                                                        <p><?php echo htmlentities($result->PostingDate); ?></p>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="fw-semibold">Approved On</label>
                                                        <p><?php echo htmlentities($result->AdminRemarkDate); ?></p>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
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
                            <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                            <h4 class="text-muted">No Approved Leaves</h4>
                            <p class="text-muted">There are no approved leave applications in the system yet.</p>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/modern.js"></script>
</body>
</html>
