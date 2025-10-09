<?php
session_start();
include('includes/config.php');

if(strlen($_SESSION['emplogin'])==0) { 
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
    <link rel="stylesheet" href="assets/css/modern.css">

    <style>
        .date-input-group { position: relative; }
        .date-input-group .form-control { padding-right: 2.5rem; }
        .date-input-group i {
            position: absolute; right: 1rem; top: 50%;
            transform: translateY(-50%); color: var(--gray-500);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-alt me-2"></i> ELMS - Employee Portal
            </a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-secondary btn-sm me-2">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <!-- Leave Form -->
                <div class="card shadow-sm">
                    <div class="card-header bg-primary text-white">
                        <h5><i class="fas fa-paper-plane"></i> Apply for Leave</h5>
                        <small>Fill the form to submit your leave request</small>
                    </div>
                    <div class="card-body">
                        <?php if($error) { ?>
                            <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?></div>
                        <?php } elseif($msg) { ?>
                            <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?></div>
                        <?php } ?>

                        <form method="post" name="apply">
                            <div class="row">
                                <div class="col-md-6 mb-3">
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
                               

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">From Date <span class="text-danger">*</span></label>
                                    <div class="date-input-group">
                                        <input type="date" class="form-control" name="fromdate" required>
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">To Date <span class="text-danger">*</span></label>
                                    <div class="date-input-group">
                                        <input type="date" class="form-control" name="todate" required>
                                        <i class="fas fa-calendar-alt"></i>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Number of Days</label>
                                <input type="text" class="form-control" name="duration" placeholder="Auto-calculated" readonly>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Description <span class="text-danger">*</span></label>
                                <textarea class="form-control" name="description" rows="4" placeholder="Reason for leave..." required></textarea>
                            </div>

                            <div class="text-end">
                                <a href="index.php" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Cancel
                                </a>
                                <button type="submit" name="apply" class="btn btn-primary">
                                    <i class="fas fa-paper-plane"></i> Apply Leave
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Policy Info -->
                <div class="card shadow-sm mt-4">
                    <div class="card-header bg-light">
                        <h6><i class="fas fa-info-circle text-primary"></i> Leave Policy Guidelines</h6>
                    </div>
                    <div class="card-body">
                        <ul class="list-unstyled mb-0">
                            <li><i class="fas fa-check text-success me-2"></i> Apply at least 3 days before leave</li>
                            <li><i class="fas fa-check text-success me-2"></i> Provide clear description</li>
                            <li><i class="fas fa-clock text-warning me-2"></i> Approval takes 1–2 business days</li>
                            <li><i class="fas fa-phone text-info me-2"></i> Contact admin for urgent requests</li>
                        </ul>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- JS (use local or CDN) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/modern.js"></script>

    <script>
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
    </script>
</body>
</html>
