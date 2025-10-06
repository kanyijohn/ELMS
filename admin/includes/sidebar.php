<?php
// admin/includes/sidebar.php
?>
<!-- Sidebar -->
<nav class="sidebar bg-dark text-white" style="width: 280px; min-height: 100vh;">
    <div class="sidebar-sticky pt-3">
        <!-- Brand -->
        <div class="px-3 py-4 border-bottom border-secondary">
            <div class="d-flex align-items-center">
                <i class="fas fa-calendar-alt fa-2x text-primary me-2"></i>
                <div>
                    <h5 class="mb-0 text-white">ELMS</h5>
                    <small class="text-muted">Admin Portal</small>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <ul class="nav flex-column mt-3">
            <li class="nav-item mb-1">
                <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="dashboard.php">
                    <i class="fas fa-tachometer-alt me-3"></i>
                    Dashboard
                </a>
            </li>
            
            <li class="nav-item mb-1">
                <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="manageemployee.php">
                    <i class="fas fa-users me-3"></i>
                    Manage Employees
                </a>
            </li>
            
            <li class="nav-item mb-1">
                <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="managedepartments.php">
                    <i class="fas fa-building me-3"></i>
                    Departments
                </a>
            </li>
            
            <li class="nav-item mb-1">
                <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="manageleavetype.php">
                    <i class="fas fa-calendar-alt me-3"></i>
                    Leave Types
                </a>
            </li>
            
            <li class="nav-item mb-1">
                <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="leaves.php">
                    <i class="fas fa-clipboard-list me-3"></i>
                    Leave Requests
                    <span class="badge bg-warning ms-auto">
                        <?php
                        $sql = "SELECT id FROM tblleaves WHERE Status=0";
                        $query = $dbh->prepare($sql);
                        $query->execute();
                        echo $query->rowCount();
                        ?>
                    </span>
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
                <a class="nav-link text-white d-flex align-items-center py-3 px-3 rounded" href="changepassword.php">
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