<?php
session_start();
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['eid'];

// ============================
// Handle Supervisor Actions (Approve / Decline)
// ============================
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_id'], $_POST['action'])) {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    $remark = trim($_POST['SupervisorRemark']);
    $status = $action === 'Approve' ? 'Approved' : 'Declined';
    $actionDate = date('Y-m-d H:i:s');

    $sql = "UPDATE tblleaves 
            SET SupervisorStatus = :status, 
                SupervisorRemark = :remark, 
                SupervisorActionDate = :actionDate 
            WHERE id = :leave_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->bindParam(':remark', $remark, PDO::PARAM_STR);
    $query->bindParam(':actionDate', $actionDate, PDO::PARAM_STR);
    $query->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
    $query->execute();
}

// ============================
// Fetch Supervisor Department
// ============================
$sql = "SELECT Department FROM tblemployees WHERE id = :supid";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$supervisor = $query->fetch(PDO::FETCH_OBJ);
$department = $supervisor ? $supervisor->Department : null;

// ============================
// Fetch Statistics
// ============================

// Team Members
$sql = "SELECT COUNT(*) AS total_employees FROM tblemployees WHERE supervisor_id = :supid";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$total_employees = $query->fetch(PDO::FETCH_OBJ)->total_employees ?? 0;

// ✅ Approved Leaves (using `Status` field)
$sql = "SELECT COUNT(*) AS approved_leaves
        FROM tblleaves l
        JOIN tblemployees e ON l.empid = e.id
        WHERE e.supervisor_id = :supid AND l.Status = 'Approved'";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$approved_leaves = $query->fetch(PDO::FETCH_OBJ)->approved_leaves ?? 0;

// ✅ Pending Leaves (using `Status` field)
$sql = "SELECT COUNT(*) AS pending_leaves 
        FROM tblleaves l
        JOIN tblemployees e ON l.empid = e.id
        WHERE e.supervisor_id = :supid AND (l.Status IS NULL OR l.Status = '' OR l.Status = 'Pending')";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$pending_leaves = $query->fetch(PDO::FETCH_OBJ)->pending_leaves ?? 0;

// ✅ Rejected Leaves (using `Status` field)
$sql = "SELECT COUNT(*) AS rejected_leaves 
        FROM tblleaves l
        JOIN tblemployees e ON l.empid = e.id
        WHERE e.supervisor_id = :supid AND l.Status = 'Rejected'";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$rejected_leaves = $query->fetch(PDO::FETCH_OBJ)->rejected_leaves ?? 0;

// Store stats safely
$stats = (object)[
    'total_employees' => $total_employees,
    'approved_leaves' => $approved_leaves,
    'pending_leaves' => $pending_leaves,
    'rejected_leaves' => $rejected_leaves
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard | ELMS</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom Styles -->
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>
    <div class="dashboard-container d-flex">
        <!-- Sidebar -->
        <nav class="sidebar bg-dark text-white" style="width: 260px; min-height: 100vh;">
            <div class="sidebar-sticky pt-3">
                <div class="px-3 py-3 border-bottom border-secondary">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-tie fa-2x text-warning me-2"></i>
                        <div>
                            <h5 class="mb-0 text-white">ELMS</h5>
                            <small class="text-muted">Supervisor Panel</small>
                        </div>
                    </div>
                </div>

                <ul class="nav flex-column mt-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white active d-flex align-items-center py-3 px-3 rounded" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-3"></i> Dashboard
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="leaves.php">
                            <i class="fas fa-clipboard-list me-3"></i> Leave Requests
                            <span class="badge bg-warning ms-auto"><?= $stats->pending_leaves; ?></span>
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="approvedleave-history.php">
                            <i class="fas fa-check-circle me-3"></i> Approved Leaves
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="notapproved-leaves.php">
                            <i class="fas fa-times-circle me-3"></i> Rejected Leaves
                        </a>
                    </li>

                    <li class="nav-divider my-3 border-secondary"></li>

                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="myprofile.php">
                            <i class="fas fa-user me-3"></i> My Profile
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="emp-changepassword.php">
                            <i class="fas fa-key me-3"></i> Change Password
                        </a>
                    </li>
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="logout.php">
                            <i class="fas fa-sign-out-alt me-3"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 mb-1 fw-semibold">Supervisor Dashboard</h1>
                    <p class="text-muted mb-0">Overview of team activities and leave requests</p>
                </div>
                <span class="badge bg-warning text-dark p-2">
                    <i class="fas fa-user-tie"></i> Supervisor
                </span>
            </div>

            <!-- Dashboard Cards -->
            <div class="row g-4 mb-4">
                <div class="col-md-3">
                    <div class="card text-center shadow-sm p-3 border-0">
                        <i class="fas fa-users fa-2x text-primary mb-2"></i>
                        <h3><?= $stats->total_employees; ?></h3>
                        <p class="text-muted mb-0">Team Members</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm p-3 border-0">
                        <i class="fas fa-check-circle fa-2x text-success mb-2"></i>
                        <h3><?= $stats->approved_leaves; ?></h3>
                        <p class="text-muted mb-0">Approved Leaves</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm p-3 border-0">
                        <i class="fas fa-clock fa-2x text-warning mb-2"></i>
                        <h3><?= $stats->pending_leaves; ?></h3>
                        <p class="text-muted mb-0">Pending Requests</p>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card text-center shadow-sm p-3 border-0">
                        <i class="fas fa-times-circle fa-2x text-danger mb-2"></i>
                        <h3><?= $stats->rejected_leaves; ?></h3>
                        <p class="text-muted mb-0">Rejected Leaves</p>
                    </div>
                </div>
            </div>

            <!-- Pending Leave Requests (Recent Activity) -->
            <div class="card shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0"><i class="fas fa-bell"></i> Pending Leave Requests</h5>
                </div>
                <div class="card-body">
                    <?php
                    $sql = "SELECT l.id, l.LeaveType, l.FromDate, l.ToDate, l.PostingDate, 
                                   e.FirstName, e.LastName, e.EmpId
                            FROM tblleaves l 
                            JOIN tblemployees e ON l.empid = e.id 
                            WHERE e.supervisor_id = :supid 
                              AND (l.Status IS NULL OR l.Status = '' OR l.Status = 'Pending')
                            ORDER BY l.PostingDate DESC 
                            LIMIT 5";
                    $query = $dbh->prepare($sql);
                    $query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
                    $query->execute();
                    $requests = $query->fetchAll(PDO::FETCH_OBJ);

                    if ($query->rowCount() > 0) {
                        foreach ($requests as $req) { ?>
                            <div class="d-flex align-items-start mb-3 border-bottom pb-2">
                                <div class="flex-shrink-0">
                                    <div class="bg-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fas fa-user text-dark" style="font-size: 0.9rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1 fw-bold"><?= htmlentities($req->FirstName) . ' ' . htmlentities($req->LastName); ?></p>
                                    <small class="text-muted">
                                        Applied for <strong><?= htmlentities($req->LeaveType); ?></strong> 
                                        (<?= htmlentities($req->FromDate) ?> to <?= htmlentities($req->ToDate) ?>)
                                    </small>
                                </div>
                            </div>
                    <?php }
                    } else { ?>
                        <p class="text-muted text-center mb-0">No pending leave requests</p>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
