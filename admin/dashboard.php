<?php
session_start();
include('includes/config.php');
if(!isset($_SESSION['alogin'])){
    header('location:index.php');
    exit();
}

// Fetch statistics
$sql = "SELECT id FROM tblemployees";
$query = $dbh->prepare($sql);
$query->execute();
$totalEmployees = $query->rowCount();

$sql = "SELECT id FROM tblleaves WHERE Status=1";
$query = $dbh->prepare($sql);
$query->execute();
$approvedLeaves = $query->rowCount();

$sql = "SELECT id FROM tblleaves WHERE Status=0";
$query = $dbh->prepare($sql);
$query->execute();
$pendingLeaves = $query->rowCount();

$sql = "SELECT id FROM tblleaves WHERE Status=2";
$query = $dbh->prepare($sql);
$query->execute();
$rejectedLeaves = $query->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard | Employee Leave Management System</title>
    
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
                    <h1 class="h3 mb-1">Admin Dashboard</h1>
                    <p class="text-muted mb-0">Welcome back, <?php echo htmlentities($_SESSION['alogin']); ?>!</p>
                </div>
                <div class="d-flex gap-2">
                    <span class="badge-enhanced bg-primary">
                        <i class="fas fa-user-shield"></i> Admin
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
                        <h3><?php echo $totalEmployees; ?></h3>
                        <p>Total Employees</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon approved">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $approvedLeaves; ?></h3>
                        <p>Approved Leaves</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon pending">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $pendingLeaves; ?></h3>
                        <p>Pending Leaves</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon rejected">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-content">
                        <h3><?php echo $rejectedLeaves; ?></h3>
                        <p>Rejected Leaves</p>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="row">
                <div class="col-md-8">
                    <div class="enhanced-card">
                        <div class="card-header">
                            <h5><i class="fas fa-tachometer-alt"></i> Quick Actions</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <a href="manageemployee.php" class="btn-enhanced btn-primary w-100">
                                        <i class="fas fa-user-plus"></i> Manage Employees
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="leaves.php" class="btn-enhanced btn-warning w-100">
                                        <i class="fas fa-clipboard-list"></i> View Leave Requests
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="managedepartments.php" class="btn-enhanced btn-success w-100">
                                        <i class="fas fa-building"></i> Manage Departments
                                    </a>
                                </div>
                                <div class="col-md-6">
                                    <a href="manageleavetype.php" class="btn-enhanced btn-info w-100">
                                        <i class="fas fa-calendar-alt"></i> Manage Leave Types
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
                                    ORDER BY l.PostingDate DESC LIMIT 5";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                            
                            if($query->rowCount() > 0) {
                                foreach($results as $result) {
                            ?>
                            <div class="d-flex align-items-start mb-3">
                                <div class="flex-shrink-0">
                                    <div class="bg-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                        <i class="fas fa-calendar text-white" style="font-size: 0.8rem;"></i>
                                    </div>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <p class="mb-1"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?> applied for <?php echo htmlentities($result->LeaveType); ?></p>
                                    <small class="text-muted"><?php echo htmlentities($result->PostingDate); ?></small>
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