<?php
session_start();
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['empid'] ?? $_SESSION['eid'] ?? null;

if (!$supervisor_id) {
    die("Session expired or invalid. Please log in again.");
}

try {
    // ✅ Get supervisor department
    $sql = "SELECT Department FROM tblemployees WHERE id = :empid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':empid', $supervisor_id, PDO::PARAM_INT);
    $query->execute();
    $supervisor = $query->fetch(PDO::FETCH_OBJ);

    if (!$supervisor) {
        die("Supervisor record not found.");
    }

    // ✅ Get pending leaves (Status = 0)
    $sql = "SELECT 
                tblleaves.id AS lid,
                tblemployees.FirstName,
                tblemployees.LastName,
                tblemployees.EmpId,
                tblleaves.LeaveType,
                tblleaves.PostingDate,
                tblleaves.FromDate,
                tblleaves.ToDate,
                tblleaves.Description
            FROM tblleaves
            JOIN tblemployees ON tblleaves.empid = tblemployees.id
            WHERE tblleaves.Status = 0
              AND tblemployees.Department = :department
            ORDER BY tblleaves.PostingDate DESC";
    $query = $dbh->prepare($sql);
    $query->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $pendingCount = $query->rowCount();

    // ✅ Fetch quick stats
    // Approved
    $sql = "SELECT COUNT(*) AS approved_count
            FROM tblleaves
            JOIN tblemployees ON tblleaves.empid = tblemployees.id
            WHERE tblemployees.Department = :department
              AND tblleaves.Status = 1";
    $query2 = $dbh->prepare($sql);
    $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query2->execute();
    $approved = $query2->fetch(PDO::FETCH_OBJ)->approved_count ?? 0;

    // Rejected
    $sql = "SELECT COUNT(*) AS rejected_count
            FROM tblleaves
            JOIN tblemployees ON tblleaves.empid = tblemployees.id
            WHERE tblemployees.Department = :department
              AND tblleaves.Status = 2";
    $query2 = $dbh->prepare($sql);
    $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query2->execute();
    $rejected = $query2->fetch(PDO::FETCH_OBJ)->rejected_count ?? 0;

    // Total team members
    $sql = "SELECT COUNT(*) AS team_size
            FROM tblemployees
            WHERE Department = :department AND Status = 1";
    $query2 = $dbh->prepare($sql);
    $query2->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query2->execute();
    $team = $query2->fetch(PDO::FETCH_OBJ)->team_size ?? 0;

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Leaves | Supervisor</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="../assets/css/modern.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Inter', sans-serif;
        }
        .content-wrapper {
            margin-left: 250px;
            padding: 20px;
        }
        .sidebar {
            position: fixed;
            top: 0; left: 0;
            width: 250px;
            height: 100%;
            background: #212529;
            color: white;
            padding-top: 20px;
        }
        .sidebar a {
            display: block;
            color: #ddd;
            padding: 10px 20px;
            text-decoration: none;
        }
        .sidebar a:hover {
            background: #495057;
            color: #fff;
        }
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
        }
    </style>
</head>
<body>

<!-- Sidebar -->
<div class="sidebar">
    <h5 class="text-center mb-4"><i class="fas fa-user-tie me-2"></i>Supervisor Panel</h5>
    <a href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Dashboard</a>
    <a href="leaves.php" class="active"><i class="fas fa-calendar-day me-2"></i>Pending Leaves</a>
    <a href="approvedleave-history.php"><i class="fas fa-check-circle me-2"></i>Approved Leaves</a>
    <a href="notapproved-leaves.php"><i class="fas fa-times-circle me-2"></i>Rejected Leaves</a>
    <a href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
</div>

<!-- Main Content -->
<div class="content-wrapper">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3>Pending Leave Requests</h3>
            <p class="text-muted mb-0">Department: <?= htmlentities($supervisor->Department) ?></p>
        </div>
        <div>
            <span class="badge bg-warning text-dark fs-6"><i class="fas fa-clock"></i> <?= $pendingCount ?> Pending</span>
        </div>
    </div>

    <!-- Pending Leaves Table -->
    <div class="card mb-4">
        <div class="card-header bg-dark text-white"><i class="fas fa-list"></i> Team Leave Requests</div>
        <div class="card-body">
            <?php if ($pendingCount > 0): ?>
            <div class="table-responsive">
                <table class="table table-striped align-middle">
                    <thead class="table-dark">
                        <tr>
                            <th>#</th>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>Leave Period</th>
                            <th>Duration</th>
                            <th>Applied On</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $cnt = 1; foreach($results as $r):
                            $from = new DateTime($r->FromDate);
                            $to = new DateTime($r->ToDate);
                            $duration = $to->diff($from)->days + 1;
                        ?>
                        <tr>
                            <td><?= $cnt++ ?></td>
                            <td><?= htmlentities($r->FirstName . ' ' . $r->LastName) ?><br><small class="text-muted"><?= htmlentities($r->EmpId) ?></small></td>
                            <td><span class="badge bg-info text-dark"><?= htmlentities($r->LeaveType) ?></span></td>
                            <td><?= htmlentities($r->FromDate) ?> → <?= htmlentities($r->ToDate) ?></td>
                            <td><?= $duration ?> day(s)</td>
                            <td><?= htmlentities($r->PostingDate) ?></td>
                            <td><a href="leave-details.php?leaveid=<?= htmlentities($r->lid) ?>" class="btn btn-sm btn-primary"><i class="fas fa-eye"></i> Review</a></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-check-circle fa-3x text-success mb-3"></i>
                    <h5>No Pending Requests</h5>
                    <p class="text-muted">All leave applications have been reviewed.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row g-3">
        <div class="col-md-3"><div class="card text-center"><div class="card-body"><h3><?= $pendingCount ?></h3><p class="text-muted">Pending</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body text-success"><h3><?= $approved ?></h3><p>Approved</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body text-danger"><h3><?= $rejected ?></h3><p>Rejected</p></div></div></div>
        <div class="col-md-3"><div class="card text-center"><div class="card-body text-primary"><h3><?= $team ?></h3><p>Team Members</p></div></div></div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
