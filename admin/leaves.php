<?php
session_start();
include_once('includes/config.php');

// Check if user is logged in
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

// Database connection check and fetch pending leaves
try {
    // SQL query to get pending leave requests
    $sql = "SELECT 
                tblleaves.id as leave_id,
                tblleaves.LeaveType,
                tblleaves.PostingDate,
                tblleaves.Status,
                tblleaves.empid,
                tblemployees.FirstName,
                tblemployees.LastName,
                tblemployees.EmpId,
                tblemployees.EmailId
            FROM tblleaves 
            INNER JOIN tblemployees ON tblleaves.empid = tblemployees.id 
            WHERE tblleaves.Status IS NULL OR tblleaves.Status = 0
            ORDER BY tblleaves.PostingDate DESC";
    
    $query = $dbh->prepare($sql);
    $query->execute();
    $leaves = $query->fetchAll(PDO::FETCH_OBJ);
    
    $totalPending = $query->rowCount();
    
} catch (PDOException $e) {
    $error = "Database error: " . $e->getMessage();
    $leaves = [];
    $totalPending = 0;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Leave Requests | Employee Leave Management System</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .sidebar {
            background: #2c3e50;
            min-height: 100vh;
            color: white;
        }
        .main-content {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .card {
            border: none;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
        .table th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
            font-weight: 600;
            color: #495057;
        }
        .badge-pending {
            background-color: #6c757d;
        }
        .employee-info {
            line-height: 1.2;
        }
        .employee-name {
            font-weight: 600;
            color: #2c3e50;
        }
        .employee-email {
            font-size: 0.875rem;
            color: #6c757d;
        }
        .action-btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }
        .page-title {
            color: #2c3e50;
            font-weight: 700;
        }
        .stat-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-0">
                <?php include('includes/sidebar.php'); ?>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 main-content">
                <div class="container-fluid py-4">
                    <!-- Page Header -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h1 class="h2 page-title mb-1">
                                        <i class="fas fa-clock me-2"></i>Pending Leave Requests
                                    </h1>
                                    <p class="text-muted mb-0">Review and manage employee leave applications awaiting approval</p>
                                </div>
                                <div class="d-flex gap-2">
                                    <a href="approvedleave-history.php" class="btn btn-success btn-sm">
                                        <i class="fas fa-check-circle me-1"></i> Approved Leaves
                                    </a>
                                    <a href="notapproved-leaves.php" class="btn btn-danger btn-sm">
                                        <i class="fas fa-times-circle me-1"></i> Rejected Leaves
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Card -->
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card stat-card">
                                <div class="card-body">
                                    <div class="row align-items-center">
                                        <div class="col">
                                            <h3 class="card-title text-white mb-1">Pending Requests</h3>
                                            <p class="card-text text-white-50 mb-0">Leaves awaiting your review</p>
                                        </div>
                                        <div class="col-auto">
                                            <div class="display-4 text-white fw-bold"><?php echo $totalPending; ?></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Leaves Table -->
                    <div class="row">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-white d-flex justify-content-between align-items-center py-3">
                                    <h5 class="card-title mb-0">
                                        <i class="fas fa-list-alt me-2"></i>Pending Applications
                                    </h5>
                                    <span class="badge bg-warning text-dark fs-6">
                                        <?php echo $totalPending; ?> Pending
                                    </span>
                                </div>
                                <div class="card-body p-0">
                                    <?php if ($totalPending > 0) { ?>
                                        <div class="table-responsive">
                                            <table id="pendingLeavesTable" class="table table-hover align-middle">
                                                <thead>
                                                    <tr>
                                                        <th width="5%">#</th>
                                                        <th width="25%">Employee Details</th>
                                                        <th width="10%">Emp ID</th>
                                                        <th width="15%">Leave Type</th>
                                                        <th width="15%">Application Date</th>
                                                        <th width="10%">Status</th>
                                                        <th width="20%">Actions</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $counter = 1;
                                                    foreach ($leaves as $leave) {
                                                        // Format dates
                                                        $postingDate = !empty($leave->PostingDate) ? 
                                                            date('M j, Y g:i A', strtotime($leave->PostingDate)) : 
                                                            'Not set';
                                                    ?>
                                                    <tr>
                                                        <td>
                                                            <span class="fw-bold text-muted"><?php echo $counter; ?></span>
                                                        </td>
                                                        <td>
                                                            <div class="employee-info">
                                                                <div class="employee-name">
                                                                    <?php echo htmlentities($leave->FirstName . ' ' . $leave->LastName); ?>
                                                                </div>
                                                                <div class="employee-email">
                                                                    <?php echo htmlentities($leave->EmailId); ?>
                                                                </div>
                                                            </div>
                                                        </td>
                                                        <td>
                                                            <span class="badge bg-light text-dark fs-6">
                                                                <?php echo htmlentities($leave->EmpId); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <span class="fw-medium text-primary">
                                                                <?php echo htmlentities($leave->LeaveType); ?>
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <small class="text-muted">
                                                                <i class="far fa-calendar me-1"></i>
                                                                <?php echo htmlentities($postingDate); ?>
                                                            </small>
                                                        </td>
                                                        <td>
                                                            <span class="badge badge-pending rounded-pill px-3 py-2">
                                                                <i class="fas fa-clock me-1"></i>Pending
                                                            </span>
                                                        </td>
                                                        <td>
                                                            <div class="d-flex gap-2">
                                                                <a href="leave-details.php?leaveid=<?php echo urlencode($leave->leave_id); ?>" 
                                                                   class="btn btn-primary btn-sm action-btn"
                                                                   data-bs-toggle="tooltip" 
                                                                   title="View Details">
                                                                    <i class="fas fa-eye me-1"></i>View
                                                                </a>
                                                                <a href="approve-leave.php?leaveid=<?php echo urlencode($leave->leave_id); ?>" 
                                                                   class="btn btn-success btn-sm action-btn"
                                                                   data-bs-toggle="tooltip" 
                                                                   title="Approve Leave">
                                                                    <i class="fas fa-check me-1"></i>Approve
                                                                </a>
                                                                <a href="reject-leave.php?leaveid=<?php echo urlencode($leave->leave_id); ?>" 
                                                                   class="btn btn-danger btn-sm action-btn"
                                                                   data-bs-toggle="tooltip" 
                                                                   title="Reject Leave">
                                                                    <i class="fas fa-times me-1"></i>Reject
                                                                </a>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                    <?php 
                                                        $counter++;
                                                    } 
                                                    ?>
                                                </tbody>
                                            </table>
                                        </div>
                                    <?php } else { ?>
                                        <!-- Empty State -->
                                        <div class="text-center py-5">
                                            <div class="py-5">
                                                <i class="fas fa-check-circle fa-4x text-success mb-4"></i>
                                                <h3 class="text-muted mb-3">No Pending Requests</h3>
                                                <p class="text-muted mb-4">All leave requests have been processed. Great job!</p>
                                                <a href="dashboard.php" class="btn btn-primary">
                                                    <i class="fas fa-tachometer-alt me-2"></i>Go to Dashboard
                                                </a>
                                            </div>
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

    <!-- JavaScript Libraries -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
    $(document).ready(function() {
        // Initialize DataTable
        if ($('#pendingLeavesTable').length) {
            $('#pendingLeavesTable').DataTable({
                responsive: true,
                pageLength: 10,
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "All"]],
                order: [[4, 'desc']], // Sort by application date descending
                language: {
                    search: "_INPUT_",
                    searchPlaceholder: "Search leaves...",
                    lengthMenu: "Show _MENU_ entries",
                    info: "Showing _START_ to _END_ of _TOTAL_ entries",
                    infoEmpty: "Showing 0 to 0 of 0 entries",
                    infoFiltered: "(filtered from _MAX_ total entries)"
                }
            });
        }

        // Initialize Bootstrap tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl);
        });

        // Add confirmation for approve/reject actions
        $('a[href*="approve-leave.php"]').on('click', function(e) {
            if (!confirm('Are you sure you want to approve this leave request?')) {
                e.preventDefault();
            }
        });

        $('a[href*="reject-leave.php"]').on('click', function(e) {
            if (!confirm('Are you sure you want to reject this leave request?')) {
                e.preventDefault();
            }
        });
    });
    </script>
</body>
</html>