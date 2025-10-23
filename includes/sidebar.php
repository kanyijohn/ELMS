<?php
// Enhanced Employee Sidebar with Modern Design
if (!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) {
    header('location:index.php');
    exit();
} else {
    $empid = $_SESSION['eid'];
    
    // Get employee details and statistics for sidebar
    $sql = "SELECT * FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $employee = $query->fetch(PDO::FETCH_OBJ);
    
    // Get leave statistics
    $sql = "SELECT 
            SUM(CASE WHEN Status = 1 THEN 1 ELSE 0 END) as approved,
            SUM(CASE WHEN Status = 0 THEN 1 ELSE 0 END) as pending,
            SUM(CASE WHEN Status = 2 THEN 1 ELSE 0 END) as rejected
            FROM tblleaves WHERE empid=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $empid, PDO::PARAM_STR);
    $query->execute();
    $stats = $query->fetch(PDO::FETCH_OBJ);
?>
    <!-- Enhanced Employee Sidebar -->
    <div class="dashboard-container">
        <nav id="sidebarMenu" class="sidebar bg-dark text-white collapse d-lg-block">
            <div class="sidebar-sticky">
                <!-- User Profile Section -->
                <div class="sidebar-profile">
                    <div class="profile-content text-center p-4">
                        <div class="profile-avatar mx-auto mb-3">
                            <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center mx-auto" 
                                 style="width: 80px; height: 80px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                                <span class="text-white fw-bold fs-3">
                                    <?php echo strtoupper(substr(htmlentities($employee->FirstName), 0, 1)); ?>
                                </span>
                            </div>
                        </div>
                        <h6 class="profile-name text-white mb-1"><?php echo htmlentities($employee->FirstName) . ' ' . htmlentities($employee->LastName); ?></h6>
                        <p class="profile-department text-muted mb-2"><?php echo htmlentities($employee->Department); ?></p>
                        <div class="profile-badges">
                            <span class="badge-enhanced bg-success" style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); padding: 0.25rem 0.75rem; border-radius: 1rem; font-size: 0.75rem;">
                                <i class="fas fa-user-check"></i> Employee
                            </span>
                        </div>
                    </div>
                </div>

                <!-- Quick Stats -->
                <div class="sidebar-stats px-3 mb-4">
                    <div class="stats-grid">
                        <div class="stat-item text-center">
                            <div class="stat-number text-success"><?php echo $stats->approved ?? 0; ?></div>
                            <div class="stat-label text-muted">Approved</div>
                        </div>
                        <div class="stat-item text-center">
                            <div class="stat-number text-warning"><?php echo $stats->pending ?? 0; ?></div>
                            <div class="stat-label text-muted">Pending</div>
                        </div>
                        <div class="stat-item text-center">
                            <div class="stat-number text-danger"><?php echo $stats->rejected ?? 0; ?></div>
                            <div class="stat-label text-muted">Rejected</div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Menu -->
                <ul class="nav flex-column sidebar-menu">
                    <!-- Main Section -->
                    <li class="nav-section">
                        <span class="section-label">MAIN NAVIGATION</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : ''; ?>" href="dashboard.php">
                            <div class="nav-icon">
                                <i class="fas fa-tachometer-alt"></i>
                            </div>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>

                    <!-- Leave Management Section -->
                    <li class="nav-section">
                        <span class="section-label">LEAVE MANAGEMENT</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'apply-leave.php' ? 'active' : ''; ?>" href="apply-leave.php">
                            <div class="nav-icon">
                                <i class="fas fa-plus-circle"></i>
                            </div>
                            <span class="nav-text">Apply for Leave</span>
                            <span class="nav-badge bg-primary">New</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'leavehistory.php' ? 'active' : ''; ?>" href="leavehistory.php">
                            <div class="nav-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <span class="nav-text">Leave History</span>
                            <?php if (($stats->pending ?? 0) > 0): ?>
                                <span class="nav-badge bg-warning"><?php echo $stats->pending; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <!-- Communication Section -->
                    <li class="nav-section">
                        <span class="section-label">COMMUNICATION</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'chatwith-admin.php' ? 'active' : ''; ?>" href="chatwith-admin.php">
                            <div class="nav-icon">
                                <i class="fas fa-comments"></i>
                            </div>
                            <span class="nav-text">Chat with Admin</span>
                            <span class="nav-badge bg-info">Live</span>
                        </a>
                    </li>

                    <!-- Account Section -->
                    <li class="nav-section">
                        <span class="section-label">ACCOUNT SETTINGS</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'myprofile.php' ? 'active' : ''; ?>" href="myprofile.php">
                            <div class="nav-icon">
                                <i class="fas fa-user-cog"></i>
                            </div>
                            <span class="nav-text">My Profile</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'emp-changepassword.php' ? 'active' : ''; ?>" href="emp-changepassword.php">
                            <div class="nav-icon">
                                <i class="fas fa-key"></i>
                            </div>
                            <span class="nav-text">Change Password</span>
                        </a>
                    </li>

                    <!-- Support Section -->
                    <li class="nav-section">
                        <span class="section-label">SUPPORT</span>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link" href="employee/chatwith-admin.php">
                            <div class="nav-icon">
                                <i class="fas fa-life-ring"></i>
                            </div>
                            <span class="nav-text">Help & Support</span>
                        </a>
                    </li>
                    
                    <li class="nav-item">
                        <a class="nav-link text-danger" href="logout.php">
                            <div class="nav-icon">
                                <i class="fas fa-sign-out-alt"></i>
                            </div>
                            <span class="nav-text">Logout</span>
                        </a>
                    </li>
                </ul>

                <!-- Sidebar Footer -->
                <div class="sidebar-footer mt-auto p-3">
                    <div class="system-info text-center">
                        <div class="system-status mb-2">
                            <span class="status-indicator online"></span>
                            <small class="text-muted">System Online</small>
                        </div>
                        <small class="text-muted">ELMS v2.0</small>
                    </div>
                </div>
            </div>
        </nav>

        <style>
            .sidebar {
                width: 280px;
                min-height: 100vh;
                background: linear-gradient(180deg, #1f2937 0%, #111827 100%);
                border-right: 1px solid #374151;
                transition: all 0.3s ease;
            }
            
            .sidebar.collapse:not(.show) {
                display: none;
            }
            
            .sidebar-sticky {
                position: sticky;
                top: 0;
                height: 100vh;
                overflow-y: auto;
                display: flex;
                flex-direction: column;
            }
            
            /* Sidebar Profile */
            .sidebar-profile {
                border-bottom: 1px solid #374151;
                background: rgba(255, 255, 255, 0.05);
            }
            
            .profile-avatar {
                position: relative;
            }
            
            .profile-avatar::after {
                content: '';
                position: absolute;
                bottom: 2px;
                right: 2px;
                width: 16px;
                height: 16px;
                background: #10b981;
                border: 2px solid #1f2937;
                border-radius: 50%;
            }
            
            .profile-name {
                font-weight: 600;
                font-size: 1.1rem;
            }
            
            .profile-department {
                font-size: 0.875rem;
            }
            
            /* Sidebar Stats */
            .sidebar-stats {
                background: rgba(255, 255, 255, 0.05);
                border-radius: 1rem;
                padding: 1rem;
            }
            
            .stats-grid {
                display: grid;
                grid-template-columns: repeat(3, 1fr);
                gap: 0.5rem;
            }
            
            .stat-item {
                padding: 0.5rem;
            }
            
            .stat-number {
                font-size: 1.25rem;
                font-weight: 700;
                line-height: 1;
                margin-bottom: 0.25rem;
            }
            
            .stat-label {
                font-size: 0.75rem;
                font-weight: 500;
            }
            
            /* Navigation Menu */
            .sidebar-menu {
                padding: 1rem 0;
                list-style: none;
            }
            
            .nav-section {
                padding: 1rem 1.5rem 0.5rem;
            }
            
            .section-label {
                font-size: 0.75rem;
                font-weight: 600;
                color: #6b7280;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }
            
            .nav-item {
                margin: 0.25rem 0.75rem;
            }
            
            .nav-link {
                display: flex;
                align-items: center;
                padding: 0.75rem 1rem;
                color: #d1d5db;
                text-decoration: none;
                border-radius: 0.75rem;
                transition: all 0.3s ease;
                position: relative;
            }
            
            .nav-link:hover {
                background: rgba(255, 255, 255, 0.1);
                color: white;
                transform: translateX(5px);
            }
            
            .nav-link.active {
                background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%);
                color: white;
                box-shadow: 0 4px 15px rgba(79, 70, 229, 0.3);
            }
            
            .nav-link.active::before {
                content: '';
                position: absolute;
                left: 0;
                top: 50%;
                transform: translateY(-50%);
                width: 3px;
                height: 60%;
                background: white;
                border-radius: 0 2px 2px 0;
            }
            
            .nav-icon {
                width: 20px;
                height: 20px;
                display: flex;
                align-items: center;
                justify-content: center;
                margin-right: 0.75rem;
                font-size: 0.9rem;
            }
            
            .nav-text {
                flex: 1;
                font-weight: 500;
                font-size: 0.9rem;
            }
            
            .nav-badge {
                padding: 0.25rem 0.5rem;
                border-radius: 0.5rem;
                font-size: 0.7rem;
                font-weight: 600;
                margin-left: 0.5rem;
            }
            
            /* Sidebar Footer */
            .sidebar-footer {
                border-top: 1px solid #374151;
                background: rgba(255, 255, 255, 0.05);
            }
            
            .system-status {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 0.5rem;
            }
            
            .status-indicator {
                width: 8px;
                height: 8px;
                border-radius: 50%;
                animation: pulse 2s infinite;
            }
            
            .status-indicator.online {
                background: #10b981;
            }
            
            @keyframes pulse {
                0%, 100% { opacity: 1; }
                50% { opacity: 0.5; }
            }
            
            /* Scrollbar Styling */
            .sidebar-sticky::-webkit-scrollbar {
                width: 4px;
            }
            
            .sidebar-sticky::-webkit-scrollbar-track {
                background: #1f2937;
            }
            
            .sidebar-sticky::-webkit-scrollbar-thumb {
                background: #4b5563;
                border-radius: 2px;
            }
            
            .sidebar-sticky::-webkit-scrollbar-thumb:hover {
                background: #6b7280;
            }
            
            /* Mobile Responsive */
            @media (max-width: 991.98px) {
                .sidebar {
                    position: fixed;
                    z-index: 1040;
                    height: 100vh;
                    transform: translateX(-100%);
                    transition: transform 0.3s ease;
                }
                
                .sidebar.show {
                    transform: translateX(0);
                }
                
                .sidebar-backdrop {
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 100%;
                    height: 100%;
                    background: rgba(0, 0, 0, 0.5);
                    z-index: 1039;
                    display: none;
                }
                
                .sidebar-backdrop.show {
                    display: block;
                }
            }
            
            @media (min-width: 992px) {
                .sidebar {
                    display: block !important;
                }
            }
        </style>

        <!-- Mobile Backdrop -->
        <div class="sidebar-backdrop"></div>

        <script>
            // Enhanced sidebar functionality
            $(document).ready(function() {
                // Mobile sidebar toggle
                $('.mobile-menu-btn').on('click', function() {
                    $('#sidebarMenu').toggleClass('show');
                    $('.sidebar-backdrop').toggleClass('show');
                });
                
                // Close sidebar when clicking on backdrop
                $('.sidebar-backdrop').on('click', function() {
                    $('#sidebarMenu').removeClass('show');
                    $(this).removeClass('show');
                });
                
                // Auto-close sidebar on mobile when clicking a link
                $('.sidebar .nav-link').on('click', function() {
                    if ($(window).width() < 992) {
                        $('#sidebarMenu').removeClass('show');
                        $('.sidebar-backdrop').removeClass('show');
                    }
                });
                
                // Add active state animations
                $('.nav-link').on('click', function() {
                    $('.nav-link').removeClass('active');
                    $(this).addClass('active');
                });
                
                // Initialize tooltips for sidebar items
                $('[data-bs-toggle="tooltip"]').tooltip();
            });
            
            // Handle window resize
            $(window).on('resize', function() {
                if ($(window).width() >= 992) {
                    $('#sidebarMenu').addClass('show');
                    $('.sidebar-backdrop').removeClass('show');
                } else {
                    $('#sidebarMenu').removeClass('show');
                }
            });
        </script>
    </div>
<?php } ?>