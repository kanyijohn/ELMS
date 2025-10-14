<?php
session_start();
error_reporting(0);
include 'includes/config.php';
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {
    // Fetch all rejected leaves
    $status = 2;
    $sql = "SELECT 
                tblleaves.id AS lid,
                tblemployees.FirstName,
                tblemployees.LastName,
                tblemployees.EmpId,
                tblemployees.id AS empid,
                tblleaves.LeaveType,
                tblleaves.PostingDate,
                tblleaves.FromDate,
                tblleaves.ToDate,
                tblleaves.Status,
                tblleaves.AdminRemark,
                tblleaves.AdminRemarkDate,
                tblleaves.Description
            FROM tblleaves 
            JOIN tblemployees ON tblleaves.empid = tblemployees.id 
            WHERE tblleaves.Status = :status 
            ORDER BY lid DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':status', $status, PDO::PARAM_INT);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
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
                    <h1 class="h3 mb-1">Rejected Leave History</h1>
                    <p class="text-muted mb-0">View all rejected leave applications</p>
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

            <!-- Rejected Leaves Table -->
            <div class="enhanced-card">
                <div class="card-header">
                    <h5><i class="fas fa-times-circle"></i> Rejected Applications</h5>
                    <span class="badge-enhanced badge-rejected"><?php echo $query->rowCount(); ?> rejected</span>
                </div>
                <div class="card-body">
                    <?php if ($query->rowCount() > 0) { ?>
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
                                foreach ($results as $result) {

                                    // Handle date format (either YYYY-MM-DD or DD/MM/YYYY)
                                    $from = DateTime::createFromFormat('Y-m-d', $result->FromDate) ?: DateTime::createFromFormat('d/m/Y', $result->FromDate);
                                    $to = DateTime::createFromFormat('Y-m-d', $result->ToDate) ?: DateTime::createFromFormat('d/m/Y', $result->ToDate);
                                    
                                    if ($from && $to) {
                                        $duration = $to->diff($from)->days + 1;
                                    } else {
                                        $duration = 'N/A';
                                    }
                                ?>
                                <tr>
                                    <td><?php echo htmlentities($cnt); ?></td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="avatar-sm bg-danger rounded-circle d-flex align-items-center justify-content-center me-3" 
                                                 style="width: 36px; height: 36px;">
                                                <span class="text-white fw-bold">
                                                    <?php echo substr(htmlentities($result->FirstName), 0, 1); ?>
                                                </span>
                                            </div>
                                            <div>
                                                <div class="fw-semibold"><?php echo htmlentities($result->FirstName) . ' ' . htmlentities($result->LastName); ?></div>
                                                <small class="text-muted"><?php echo htmlentities($result->EmpId); ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td><span class="badge bg-info text-dark"><?php echo htmlentities($result->LeaveType); ?></span></td>
                                    <td>
                                        <small>
                                            <i class="fas fa-calendar-alt text-primary me-1"></i>
                                            <?php echo htmlentities($result->FromDate); ?><br>
                                            <i class="fas fa-arrow-right text-muted me-1"></i>
                                            <?php echo htmlentities($result->ToDate); ?>
                                        </small>
                                    </td>
                                    <td><span class="badge bg-secondary"><?php echo $duration; ?> day(s)</span></td>
                                    <td><?php echo htmlentities($result->PostingDate); ?></td>
                                    <td class="text-danger"><?php echo htmlentities($result->AdminRemarkDate); ?></td>
                                    <td>
                                        <?php if (!empty($result->AdminRemark)) { ?>
                                            <span class="text-muted" data-bs-toggle="tooltip" title="<?php echo htmlentities($result->AdminRemark); ?>">
                                                <i class="fas fa-comment"></i> View Reason
                                            </span>
                                        <?php } else { ?>
                                            <span class="text-muted">No reason provided</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $cnt; ?>">
                                            <i class="fas fa-eye"></i> Details
                                        </button>
                                    </td>
                                </tr>

                                <!-- Modal -->
                                <div class="modal fade" id="detailsModal<?php echo $cnt; ?>" tabindex="-1">
                                    <div class="modal-dialog modal-lg">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title">Rejected Leave Details</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Employee</label>
                                                    <p><?php echo htmlentities($result->FirstName . ' ' . $result->LastName . " (" . $result->EmpId . ")"); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Leave Type</label>
                                                    <p><?php echo htmlentities($result->LeaveType); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Leave Period</label>
                                                    <p><?php echo htmlentities($result->FromDate . " to " . $result->ToDate); ?></p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Duration</label>
                                                    <p><?php echo $duration; ?> day(s)</p>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Rejection Reason</label>
                                                    <div class="border p-3 bg-light text-danger">
                                                        <?php echo htmlentities($result->AdminRemark ?: 'No reason provided'); ?>
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
                            <h4 class="text-muted">No Rejected Leaves</h4>
                            <p class="text-muted">There are no rejected leave applications in the system.</p>
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
<?php } ?>
