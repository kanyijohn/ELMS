<?php
session_start();
include('includes/config.php');

if(strlen($_SESSION['emplogin'])==0) { 
    header('location:index.php');
    exit();
} else {
    $empid = $_SESSION['eid'];
    
    $sql = "SELECT * FROM tblleaves WHERE empid=:eid ORDER BY PostingDate DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
}
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
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <!-- Employee Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-alt me-2"></i> ELMS - Employee Portal
            </a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-secondary btn-sm me-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
                <a href="apply-leave.php" class="btn btn-primary btn-sm">
                    <i class="fas fa-plus"></i> Apply Leave
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row">
            <div class="col-12">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">Leave History</h1>
                        <p class="text-muted mb-0">Track your leave applications and their status</p>
                    </div>
                    <div class="btn-group">
                        <button type="button" class="btn btn-outline-secondary active">All</button>
                        <button type="button" class="btn btn-outline-secondary">Pending</button>
                        <button type="button" class="btn btn-outline-secondary">Approved</button>
                        <button type="button" class="btn btn-outline-secondary">Rejected</button>
                    </div>
                </div>

                <!-- Leave History Table -->
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-light">
                        <h5><i class="fas fa-history"></i> My Leave Applications</h5>
                        <span class="badge bg-primary"><?php echo $query->rowCount(); ?> total</span>
                    </div>
                    <div class="card-body">
                        <?php if($query->rowCount() > 0) { ?>
                        <div class="table-responsive">
                            <table class="table table-striped align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Leave Type</th>
                                        <th>From</th>
                                        <th>To</th>
                                        <th>Days</th>
                                        <th>Applied On</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $cnt = 1;
                                    foreach($results as $result) {
                                        // Handle both d/m/Y and Y-m-d date formats safely
                                        $from = DateTime::createFromFormat('d/m/Y', $result->FromDate);
                                        $to = DateTime::createFromFormat('d/m/Y', $result->ToDate);

                                        if(!$from || !$to) {
                                            // Fallback in case stored as Y-m-d
                                            $from = new DateTime($result->FromDate);
                                            $to = new DateTime($result->ToDate);
                                        }

                                        $interval = $from->diff($to);
                                        $num_days = $interval->days + 1; // include both start & end days

                                        // Determine status label and color
                                        if($result->Status == 1) {
                                            $statusClass = 'bg-success';
                                            $statusText = 'Approved';
                                        } elseif($result->Status == 2) {
                                            $statusClass = 'bg-danger';
                                            $statusText = 'Rejected';
                                        } else {
                                            $statusClass = 'bg-warning text-dark';
                                            $statusText = 'Pending';
                                        }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlentities($cnt); ?></td>
                                        <td><?php echo htmlentities($result->LeaveType); ?></td>
                                        <td><?php echo htmlentities($result->FromDate); ?></td>
                                        <td><?php echo htmlentities($result->ToDate); ?></td>
                                        <td><?php echo htmlentities($num_days); ?></td>
                                        <td><?php echo htmlentities($result->PostingDate); ?></td>
                                        <td><span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></td>
                                        <td><?php echo !empty($result->AdminRemark) ? htmlentities($result->AdminRemark) : '-'; ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#detailsModal<?php echo $cnt; ?>">
                                                <i class="fas fa-eye"></i> View
                                            </button>
                                        </td>
                                    </tr>

                                    <!-- Details Modal -->
                                    <div class="modal fade" id="detailsModal<?php echo $cnt; ?>" tabindex="-1">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title">Leave Application Details</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><strong>Leave Type:</strong> <?php echo htmlentities($result->LeaveType); ?></p>
                                                    <p><strong>From:</strong> <?php echo htmlentities($result->FromDate); ?></p>
                                                    <p><strong>To:</strong> <?php echo htmlentities($result->ToDate); ?></p>
                                                    <p><strong>Number of Days:</strong> <?php echo htmlentities($num_days); ?></p>
                                                    <p><strong>Status:</strong> <span class="badge <?php echo $statusClass; ?>"><?php echo $statusText; ?></span></p>
                                                    <p><strong>Description:</strong> <?php echo htmlentities($result->Description); ?></p>
                                                    <?php if(!empty($result->AdminRemark)) { ?>
                                                        <p><strong>Admin Remarks:</strong> <?php echo htmlentities($result->AdminRemark); ?></p>
                                                    <?php } ?>
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
                                <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                                <h4 class="text-muted">No Leave History</h4>
                                <p class="text-muted">You haven't applied for any leaves yet.</p>
                                <a href="apply-leave.php" class="btn btn-primary">
                                    <i class="fas fa-plus"></i> Apply for Leave
                                </a>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- jQuery and Bootstrap -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Prevent crash if setupFormEnhancements() missing -->
    <script>
        if (typeof setupFormEnhancements !== "function") {
            console.warn("setupFormEnhancements() placeholder — no logic implemented, creating fallback.");
            window.setupFormEnhancements = function() {
                console.info("setupFormEnhancements() safely stubbed.");
            };
        }
        $(document).ready(function() {
            setupFormEnhancements();

            // Filter logic
            $('.btn-group .btn').on('click', function() {
                $('.btn-group .btn').removeClass('active');
                $(this).addClass('active');
                const filter = $(this).text().toLowerCase();
                const rows = $('table tbody tr');
                if (filter === 'all') {
                    rows.show();
                } else {
                    rows.hide().filter(function() {
                        return $(this).find('td:nth-child(7)').text().toLowerCase() === filter;
                    }).show();
                }
            });
        });
    </script>

    <!-- Load modern.js last -->
    <script src="assets/js/modern.js"></script>
</body>
</html>
