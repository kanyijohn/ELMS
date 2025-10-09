<?php
session_start();

// Always use absolute paths for includes
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['eid'];

// Handle supervisor action (approve/decline)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_id'], $_POST['action'])) {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    $remark = trim($_POST['SupervisorRemark']);
    $status = $action === 'Approve' ? 'Approved' : 'Declined';
    $actionDate = date('Y-m-d H:i:s');

    $sql = "UPDATE tblleaves 
            SET SupervisorStatus=:status, 
                SupervisorRemark=:remark, 
                SupervisorActionDate=:actionDate 
            WHERE id=:leave_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->bindParam(':remark', $remark, PDO::PARAM_STR);
    $query->bindParam(':actionDate', $actionDate, PDO::PARAM_STR);
    $query->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
    $query->execute();
}

// Fetch leave requests for this supervisor
$sql = "SELECT l.*, e.FirstName, e.LastName 
        FROM tblleaves l
        JOIN tblemployees e ON l.empid = e.id
        WHERE e.supervisor_id = :supid";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$leaves = $query->fetchAll(PDO::FETCH_OBJ);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supervisor Dashboard | Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Supervisor Sidebar -->
        <nav class="sidebar bg-dark text-white" style="width: 280px; min-height: 100vh;">
            <div class="sidebar-sticky pt-3">
                <!-- Brand -->
                <div class="px-3 py-4 border-bottom border-secondary">
                    <div class="d-flex align-items-center">
                        <i class="fas fa-user-tie fa-2x text-warning me-2"></i>
                        <div>
                            <h5 class="mb-0 text-white">ELMS</h5>
                            <small class="text-muted">Supervisor Portal</small>
                        </div>
                    </div>
                </div>

                <!-- Navigation -->
                <ul class="nav flex-column mt-3">
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded active" href="dashboard.php">
                            <i class="fas fa-tachometer-alt me-3"></i>
                            Dashboard
                        </a>
                    </li>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="leaves.php">
                            <i class="fas fa-clipboard-list me-3"></i>
                            Leave Requests
                            <span class="badge bg-warning ms-auto"><?php echo $stats->pending_leaves ?? 0; ?></span>
                        </a>
                    </li>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="approvedleave-history.php">
                            <i class="fas fa-check-circle me-3"></i>
                            Approved Leaves
                        </a>
                    </li>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="notapproved-leaves.php">
                            <i class="fas fa-times-circle me-3"></i>
                            Rejected Leaves
                        </a>
                    </li>
                    
                    <li class="nav-divider my-3 border-secondary"></li>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="myprofile.php">
                            <i class="fas fa-user me-3"></i>
                            My Profile
                        </a>
                    </li>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="emp-changepassword.php">
                            <i class="fas fa-key me-3"></i>
                            Change Password
                        </a>
                    </li>
                    
                    <li class="nav-item mb-1">
                        <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="logout.php">
                            <i class="fas fa-sign-out-alt me-3"></i>
                            Logout
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Supervisor Dashboard</h1>
                    <p class="text-muted mb-0">Manage your team's leave requests and approvals</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge-enhanced bg-warning">
                        <i class="fas fa-user-tie"></i> Supervisor
                    </span>
                    <a href="logout.php" class="btn-enhanced btn-secondary">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon employees">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats->total_employees ?? 0; ?></h3>
                        <p>Team Members</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats->approved_leaves ?? 0; ?></h3>
                        <p>Approved Leaves</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats->pending_leaves ?? 0; ?></h3>
                        <p>Pending Requests</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon rejected">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $stats->rejected_leaves ?? 0; ?></h3>
                        <p>Rejected Leaves</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-8">
                    <div class="enhanced-card">
                        <div class="card-header">
                            <h5><i class="fas fa-rocket"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="leaves.php" class="btn-enhanced btn-warning w-100 h-100 py-4">
                                        <div class="text-center">
                                            <i class="fas fa-clipboard-list fa-2x mb-2"></i>
                                            <h6>Review Requests</h6>
                                            <small class="text-muted"><?php echo $stats->pending_leaves ?? 0; ?> pending</small>
                                        </div>
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="approvedleave-history.php" class="btn-enhanced btn-success w-100 h-100 py-4">
                                        <div class="text-center">
                                            <i class="fas fa-check-circle fa-2x mb-2"></i>
                                            <h6>Approved Leaves</h6>
                                            <small class="text-muted">View approved applications</small>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-4">
                    <div class="enhanced-card">
                        <div class="card-header">
                            <h5><i class="fas fa-bell"></i> Recent Activity</h5>
                        </div>
                        <div class="card-body">
                            <?php
                            $sql = "SELECT l.LeaveType, l.PostingDate, e.FirstName, e.LastName 
                                    FROM tblleaves l 
                                    JOIN tblemployees e ON l.empid = e.id 
                                    WHERE l.department = (SELECT Department FROM tblemployees WHERE id = :supid)
                                    ORDER BY l.PostingDate DESC LIMIT 5";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':supid', $supid, PDO::PARAM_STR);
                            $query->execute();
                            $activities = $query->fetchAll(PDO::FETCH_OBJ);
                            
                            if($query->rowCount() > 0) {
                                foreach($activities as $activity) {
                            ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-user text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1"><?php echo htmlentities($activity->FirstName).' '.htmlentities($activity->LastName); ?></p>
                                    <small class="text-muted">Applied for <?php echo htmlentities($activity->LeaveType); ?></small>
                                </div>
                            </div>
                            <?php }
                            } else { ?>
                                <p class="text-muted text-center mb-0">No recent activity</p>
                            <?php } ?>
                        </div>
                    </div>
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