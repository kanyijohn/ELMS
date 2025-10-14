<?php
session_start();
include('includes/config.php');
if(strlen($_SESSION['login'])==0) { 
    header('location:../index.php');
    exit();
} else {
    $supid = $_SESSION['supid'];
    
    // Get supervisor's department
    $sql = "SELECT Department FROM tblemployees WHERE id=:supid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':supid', $supid, PDO::PARAM_STR);
    $query->execute();
    $supervisor = $query->fetch(PDO::FETCH_OBJ);
    
    // Get pending leaves for supervisor's department
    $sql = "SELECT tblleaves.id as lid, tblemployees.FirstName, tblemployees.LastName, 
            tblemployees.EmpId, tblemployees.id, tblleaves.LeaveType, tblleaves.PostingDate,
            tblleaves.FromDate, tblleaves.ToDate, tblleaves.Description
            FROM tblleaves 
            JOIN tblemployees ON tblleaves.empid = tblemployees.id 
            WHERE tblleaves.Status IS NULL 
            AND tblemployees.Department = :department
            ORDER BY tblleaves.PostingDate DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Leave Requests | Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
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
                <span class="badge-enhanced bg-warning me-3">
                    <i class="fas fa-users"></i> <?php echo htmlentities($supervisor->Department); ?>
                </span>
                <a href="dashboard.php" class="btn-enhanced btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Pending Leave Requests</h1>
                <p class="text-muted mb-0">Review and manage leave applications from your team</p>
            </div>
            <div class="d-flex gap-2">
                <a href="approvedleave-history.php" class="btn-enhanced btn-success">
                    <i class="fas fa-check-circle"></i> Approved Leaves
                </a>
                <a href="notapproved-leaves.php" class="btn-enhanced btn-danger">
                    <i class="fas fa-times-circle"></i> Rejected Leaves
                </a>
            </div>
        </div>

        <!-- Department Info -->
        <div class="enhanced-card mb-4">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h5 class="mb-1"><?php echo htmlentities($supervisor->Department); ?> Department</h5>
                        <p class="text-muted mb-0">You have <strong><?php echo $query->rowCount(); ?></strong> pending leave requests to review</p>
                    </div>
                    <div class="col-md-4 text-end">
                        <span class="badge-enhanced badge-pending">
                            <i class="fas fa-clock"></i> <?php echo $query->rowCount(); ?> Pending
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Leave Requests Table -->
        <div class="enhanced-card">
            <div class="card-header">
                <h5><i class="fas fa-clipboard-list"></i> Team Leave Requests</h5>
            </div>
            <div class="card-body">
                <?php if($query->rowCount() > 0) { ?>
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
                                <th>Status</th>
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
                                        <div class="avatar-sm bg-primary rounded-circle d-flex align-items-center justify-content-center me-3" 
                                             style="width: 36px; height: 36px;">
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
                                    <span class="badge-enhanced badge-pending">
                                        <i class="fas fa-clock"></i> Pending
                                    </span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <a href="leave-details.php?leaveid=<?php echo htmlentities($result->lid); ?>" 
                                           class="btn-enhanced btn-primary btn-sm"
                                           data-bs-toggle="tooltip"
                                           title="Review Application">
                                            <i class="fas fa-eye"></i> Review
                                        </a>
                                    </div>
                                </td>
                            </tr>
                            <?php $cnt++; } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                    <div class="text-center py-5">
                        <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                        <h4 class="text-muted">No Pending Requests</h4>
                        <p class="text-muted">All leave requests from your team have been processed.</p>
                        <a href="dashboard.php" class="btn-enhanced btn-primary">
                            <i class="fas fa-tachometer-alt"></i> Back to Dashboard
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>

        <!-- Quick Stats -->
        <?php if($query->rowCount() > 0) { ?>
        <div class="row mt-4">
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-primary"><?php echo $query->rowCount(); ?></h3>
                        <p class="text-muted mb-0">Pending Requests</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-success">
                            <?php
                            $sql = "SELECT COUNT(*) as approved_count FROM tblleaves 
                                    JOIN tblemployees ON tblleaves.empid = tblemployees.id 
                                    WHERE tblemployees.Department = :department AND tblleaves.Status = 1";
                            $query2 = $dbh->prepare($sql);
                            $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
                            $query2->execute();
                            $approved = $query2->fetch(PDO::FETCH_OBJ);
                            echo $approved->approved_count;
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Approved This Month</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-warning">
                            <?php
                            $sql = "SELECT COUNT(DISTINCT empid) as team_size FROM tblemployees 
                                    WHERE Department = :department AND status = 1";
                            $query2 = $dbh->prepare($sql);
                            $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
                            $query2->execute();
                            $team = $query2->fetch(PDO::FETCH_OBJ);
                            echo $team->team_size;
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Team Members</p>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="enhanced-card text-center">
                    <div class="card-body">
                        <h3 class="text-info">
                            <?php
                            $sql = "SELECT COUNT(*) as total_leaves FROM tblleaves 
                                    JOIN tblemployees ON tblleaves.empid = tblemployees.id 
                                    WHERE tblemployees.Department = :department";
                            $query2 = $dbh->prepare($sql);
                            $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
                            $query2->execute();
                            $total = $query2->fetch(PDO::FETCH_OBJ);
                            echo $total->total_leaves;
                            ?>
                        </h3>
                        <p class="text-muted mb-0">Total Applications</p>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/modern.js"></script>
</body>
</html>