<?php
session_start();
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['eid'];
$error = "";
$msg = "";
$supervisor = null;

// Get supervisor details
$sql = "SELECT * FROM tblemployees WHERE id=:supid";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$supervisor = $query->fetch(PDO::FETCH_OBJ);

if (!$supervisor) {
    $error = "Supervisor information not found. Please contact administrator.";
} else {
    // Handle profile update
    if(isset($_POST['update'])) {
        $fname = trim($_POST['fname']);
        $lname = trim($_POST['lname']);
        $mobileno = trim($_POST['mobileno']);
        $address = trim($_POST['address']);
        $city = trim($_POST['city']);
        $country = trim($_POST['country']);
        
        if(empty($fname) || empty($lname)) {
            $error = "First name and last name are required.";
        } else {
            // Check if updationDate column exists, otherwise don't include it
            $update_sql = "UPDATE tblemployees SET FirstName = :fname, LastName = :lname, 
                          Phonenumber = :mobileno, Address = :address, City = :city, 
                          Country = :country WHERE id = :supid";
            $update_query = $dbh->prepare($update_sql);
            $update_query->bindParam(':fname', $fname, PDO::PARAM_STR);
            $update_query->bindParam(':lname', $lname, PDO::PARAM_STR);
            $update_query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
            $update_query->bindParam(':address', $address, PDO::PARAM_STR);
            $update_query->bindParam(':city', $city, PDO::PARAM_STR);
            $update_query->bindParam(':country', $country, PDO::PARAM_STR);
            $update_query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
            
            if($update_query->execute()) {
                $msg = "Profile updated successfully!";
                // Refresh supervisor data
                $query->execute();
                $supervisor = $query->fetch(PDO::FETCH_OBJ);
            } else {
                $error = "Failed to update profile. Please try again.";
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
    <title>My Profile | Employee Leave Management System</title>
    
    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
    
    <style>
        .btn-enhanced {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-weight: 500;
            transition: all 0.2s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: none;
            cursor: pointer;
        }
        .btn-enhanced:hover {
            transform: translateY(-1px);
        }
        .btn-primary { background: #0d6efd; color: white; }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-warning { background: #ffc107; color: #000; }
        .btn-danger { background: #dc3545; color: white; }
        .btn-success { background: #198754; color: white; }
        .btn-info { background: #0dcaf0; color: #000; }
        .btn-sm { padding: 0.375rem 0.75rem; font-size: 0.875rem; }
        
        .enhanced-card {
            background: #fff;
            border-radius: 0.5rem;
            box-shadow: 0 0.125rem 0.25rem rgba(0, 0, 0, 0.075);
            margin-bottom: 1.5rem;
            border: 1px solid rgba(0, 0, 0, 0.125);
        }
        .enhanced-card .card-header {
            padding: 1rem 1.5rem;
            background: #f8f9fa;
            border-bottom: 1px solid rgba(0, 0, 0, 0.125);
        }
        .enhanced-card .card-body {
            padding: 1.5rem;
        }
        
        .badge-enhanced {
            padding: 0.375rem 0.75rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
        }
        .badge-active {
            background: #d1e7dd;
            color: #0f5132;
            border: 1px solid #badbcc;
        }
        .badge-inactive {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f1aeb5;
        }
        .bg-success { background: #198754 !important; }
        .bg-info { background: #0dcaf0 !important; }
        .bg-warning { background: #ffc107 !important; color: #000; }
        
        .alert-modern {
            padding: 0.75rem 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
            border: 1px solid transparent;
        }
        .alert-error {
            color: #721c24;
            background-color: #f8d7da;
            border-color: #f5c6cb;
        }
        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }
        
        .avatar-xl {
            width: 100px;
            height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            font-size: 2rem;
        }
    </style>
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
                <a href="dashboard.php" class="btn-enhanced btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <!-- Page Header -->
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">My Profile</h1>
                        <p class="text-muted mb-0">Manage your personal information and account details</p>
                    </div>
                    <div>
                        <a href="emp-changepassword.php" class="btn-enhanced btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                    </div>
                </div>

                <?php if(!empty($error)): ?>
                    <div class="alert-modern alert-error">
                        <i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?>
                    </div>
                <?php endif; ?>
                
                <?php if(!empty($msg)): ?>
                    <div class="alert-modern alert-success">
                        <i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?>
                    </div>
                <?php endif; ?>

                <?php if(!$supervisor): ?>
                    <div class="text-center mt-4">
                        <a href="dashboard.php" class="btn-enhanced btn-primary">Return to Dashboard</a>
                    </div>
                <?php else: ?>
                <div class="row">
                    <!-- Profile Summary -->
                    <div class="col-lg-4">
                        <div class="enhanced-card">
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <div class="avatar-xl bg-warning rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3">
                                        <span class="text-white fw-bold">
                                            <?php echo substr(htmlentities($supervisor->FirstName), 0, 1); ?>
                                        </span>
                                    </div>
                                    <h4 class="mb-1"><?php echo htmlentities($supervisor->FirstName).' '.htmlentities($supervisor->LastName); ?></h4>
                                    <p class="text-muted mb-2"><?php echo htmlentities($supervisor->Department); ?> Supervisor</p>
                                    <span class="badge-enhanced bg-warning">
                                        <i class="fas fa-user-tie"></i> Supervisor
                                    </span>
                                </div>
                                
                                <div class="text-start">
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Employee ID</small>
                                        <strong><?php echo htmlentities($supervisor->EmpId); ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Email</small>
                                        <strong><?php echo htmlentities($supervisor->EmailId); ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Phone</small>
                                        <strong><?php echo htmlentities($supervisor->Phonenumber) ?: 'Not provided'; ?></strong>
                                    </div>
                                    <div class="mb-3">
                                        <small class="text-muted d-block">Registration Date</small>
                                        <strong><?php echo htmlentities($supervisor->RegDate); ?></strong>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Department Info -->
                        <div class="enhanced-card mt-4">
                            <div class="card-header">
                                <h6><i class="fas fa-building"></i> Department Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="mb-3">
                                    <small class="text-muted d-block">Department</small>
                                    <strong><?php echo htmlentities($supervisor->Department); ?></strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Role</small>
                                    <strong>Supervisor</strong>
                                </div>
                                <div class="mb-3">
                                    <small class="text-muted d-block">Account Status</small>
                                    <?php
                                    // Check if status property exists, otherwise assume active
                                    $status = property_exists($supervisor, 'status') ? $supervisor->status : 1;
                                    $isActive = property_exists($supervisor, 'IsActive') ? $supervisor->IsActive : 1;
                                    // Use either status or IsActive, default to active
                                    $accountStatus = ($status == 1 || $isActive == 1) ? 1 : 0;
                                    ?>
                                    <span class="badge-enhanced <?php echo ($accountStatus == 1) ? 'badge-active' : 'badge-inactive'; ?>">
                                        <?php echo ($accountStatus == 1) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile Form -->
                    <div class="col-lg-8">
                        <div class="enhanced-card">
                            <div class="card-header">
                                <h5><i class="fas fa-user-edit"></i> Edit Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <form method="post">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">First Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="fname" 
                                                       value="<?php echo htmlentities($supervisor->FirstName); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Last Name <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" name="lname" 
                                                       value="<?php echo htmlentities($supervisor->LastName); ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email Address</label>
                                                <input type="email" class="form-control" 
                                                       value="<?php echo htmlentities($supervisor->EmailId); ?>" readonly>
                                                <div class="form-text">Email cannot be changed. Contact admin for email updates.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Gender</label>
                                                <input type="text" class="form-control" 
                                                       value="<?php echo htmlentities($supervisor->Gender); ?>" readonly>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Department</label>
                                                <input type="text" class="form-control" 
                                                       value="<?php echo htmlentities($supervisor->Department); ?>" readonly>
                                                <div class="form-text">Department changes require admin approval.</div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Mobile Number</label>
                                                <input type="text" class="form-control" name="mobileno" 
                                                       value="<?php echo htmlentities($supervisor->Phonenumber); ?>" 
                                                       placeholder="Enter your mobile number">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="3" 
                                                  placeholder="Enter your complete address"><?php echo htmlentities($supervisor->Address); ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" name="city" 
                                                       value="<?php echo htmlentities($supervisor->City); ?>" 
                                                       placeholder="Enter your city">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Country</label>
                                                <input type="text" class="form-control" name="country" 
                                                       value="<?php echo htmlentities($supervisor->Country); ?>" 
                                                       placeholder="Enter your country">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex gap-2 justify-content-end">
                                        <a href="dashboard.php" class="btn-enhanced btn-secondary">
                                            <i class="fas fa-times"></i> Cancel
                                        </a>
                                        <button type="submit" name="update" class="btn-enhanced btn-primary">
                                            <i class="fas fa-save"></i> Update Profile
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Account Information -->
                        <div class="enhanced-card mt-4">
                            <div class="card-header">
                                <h6><i class="fas fa-info-circle"></i> Account Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Employee ID</small>
                                            <strong><?php echo htmlentities($supervisor->EmpId); ?></strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Registration Date</small>
                                            <strong><?php echo htmlentities($supervisor->RegDate); ?></strong>
                                        </div>
                                    </div>
                                
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <small class="text-muted d-block">Account Status</small>
                                            <?php
                                            // Check if status property exists, otherwise assume active
                                            $status = property_exists($supervisor, 'status') ? $supervisor->status : 1;
                                            $isActive = property_exists($supervisor, 'IsActive') ? $supervisor->IsActive : 1;
                                            // Use either status or IsActive, default to active
                                            $accountStatus = ($status == 1 || $isActive == 1) ? 1 : 0;
                                            ?>
                                            <span class="badge-enhanced <?php echo ($accountStatus == 1) ? 'badge-active' : 'badge-inactive'; ?>">
                                                <?php echo ($accountStatus == 1) ? 'Active' : 'Inactive'; ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>