<?php
session_start();
error_reporting(0);
include 'includes/config.php';

// Redirect if not logged in
if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
    exit();
}

$eid = $_SESSION['emplogin'];
$msg = "";
$error = "";

// ✅ Fetch employee details first
$sql = "SELECT * FROM tblemployees WHERE EmailId = :eid";
$query = $dbh->prepare($sql);
$query->bindParam(':eid', $eid, PDO::PARAM_STR);
$query->execute();
$result = $query->fetch(PDO::FETCH_OBJ);

if (!$result) {
    $error = "Unable to fetch employee details.";
}

// ✅ Update logic when form submitted
if (isset($_POST['update'])) {
    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $gender = $_POST['gender'];
    $address = $_POST['address'];
    $city = $_POST['city'];
    $country = $_POST['country'];
    $mobileno = $_POST['mobileno'];

    $sql = "UPDATE tblemployees 
            SET FirstName=:fname, LastName=:lname, Gender=:gender, 
                Address=:address, City=:city, Country=:country, 
                Phonenumber=:mobileno 
            WHERE EmailId=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':fname', $fname, PDO::PARAM_STR);
    $query->bindParam(':lname', $lname, PDO::PARAM_STR);
    $query->bindParam(':gender', $gender, PDO::PARAM_STR);
    $query->bindParam(':address', $address, PDO::PARAM_STR);
    $query->bindParam(':city', $city, PDO::PARAM_STR);
    $query->bindParam(':country', $country, PDO::PARAM_STR);
    $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
    $query->bindParam(':eid', $eid, PDO::PARAM_STR);
    
    if ($query->execute()) {
        $msg = "Profile updated successfully!";
        // Refresh the data to show updated info
        $query = $dbh->prepare("SELECT * FROM tblemployees WHERE EmailId = :eid");
        $query->bindParam(':eid', $eid, PDO::PARAM_STR);
        $query->execute();
        $result = $query->fetch(PDO::FETCH_OBJ);
    } else {
        $error = "Failed to update profile. Please try again.";
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
    <link rel="stylesheet" href="assets/css/modern.css">
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="index.php">
                <i class="fas fa-calendar-alt me-2"></i> ELMS - Employee Portal
            </a>
            <div class="d-flex align-items-center">
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <div>
                        <h1 class="h3 mb-1">My Profile</h1>
                        <p class="text-muted mb-0">Manage your personal information and account details</p>
                    </div>
                    <div>
                        <a href="emp-changepassword.php" class="btn btn-warning">
                            <i class="fas fa-key"></i> Change Password
                        </a>
                    </div>
                </div>

                <div class="row">
                    <!-- Profile Summary -->
                    <div class="col-lg-4">
                        <div class="card shadow-sm border-0">
                            <div class="card-body text-center">
                                <div class="mb-4">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" 
                                         style="width: 100px; height: 100px; font-size: 36px;">
                                        <?php echo substr(htmlentities($result->FirstName ?? 'U'), 0, 1); ?>
                                    </div>
                                    <h4 class="mb-1"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?></h4>
                                    <p class="text-muted mb-2"><?php echo htmlentities($result->Department); ?></p>
                                    <span class="badge <?php echo ($result->Status == 1) ? 'bg-success' : 'bg-danger'; ?>">
                                        <?php echo ($result->Status == 1) ? 'Active' : 'Inactive'; ?>
                                    </span>
                                </div>
                                <div class="text-start">
                                    <p><strong>Employee ID:</strong> <?php echo htmlentities($result->EmpId); ?></p>
                                    <p><strong>Email:</strong> <?php echo htmlentities($result->EmailId); ?></p>
                                    <p><strong>Phone:</strong> <?php echo htmlentities($result->Phonenumber ?? 'Not provided'); ?></p>
                                    <p><strong>Reg Date:</strong> <?php echo htmlentities($result->RegDate); ?></p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Edit Profile -->
                    <div class="col-lg-8">
                        <div class="card shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h5><i class="fas fa-user-edit"></i> Edit Profile Information</h5>
                            </div>
                            <div class="card-body">
                                <?php if($error) { ?>
                                    <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                                <?php } ?>
                                <?php if($msg) { ?>
                                    <div class="alert alert-success"><?php echo htmlentities($msg); ?></div>
                                <?php } ?>

                                <form method="post">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">First Name</label>
                                                <input type="text" class="form-control" name="fname" value="<?php echo htmlentities($result->FirstName); ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Last Name</label>
                                                <input type="text" class="form-control" name="lname" value="<?php echo htmlentities($result->LastName); ?>" required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" class="form-control" value="<?php echo htmlentities($result->EmailId); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Gender</label>
                                                <select class="form-control" name="gender">
                                                    <option value="Male" <?php if($result->Gender=="Male") echo "selected"; ?>>Male</option>
                                                    <option value="Female" <?php if($result->Gender=="Female") echo "selected"; ?>>Female</option>
                                                    <option value="Other" <?php if($result->Gender=="Other") echo "selected"; ?>>Other</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Department</label>
                                                <input type="text" class="form-control" value="<?php echo htmlentities($result->Department); ?>" readonly>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Mobile Number</label>
                                                <input type="text" class="form-control" name="mobileno" value="<?php echo htmlentities($result->Phonenumber); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Address</label>
                                        <textarea class="form-control" name="address" rows="3"><?php echo htmlentities($result->Address); ?></textarea>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">City</label>
                                                <input type="text" class="form-control" name="city" value="<?php echo htmlentities($result->City); ?>">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="mb-3">
                                                <label class="form-label">Country</label>
                                                <input type="text" class="form-control" name="country" value="<?php echo htmlentities($result->Country); ?>">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="index.php" class="btn btn-secondary"><i class="fas fa-times"></i> Cancel</a>
                                        <button type="submit" name="update" class="btn btn-primary"><i class="fas fa-save"></i> Update Profile</button>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- Account Info -->
                        <div class="card mt-4 shadow-sm border-0">
                            <div class="card-header bg-light">
                                <h6><i class="fas fa-info-circle"></i> Account Information</h6>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Employee ID</small>
                                        <strong><?php echo htmlentities($result->EmpId); ?></strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Registered On</small>
                                        <strong><?php echo htmlentities($result->RegDate); ?></strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Last Updated</small>
                                        <strong><?php echo htmlentities($result->UpdationDate ?? 'Never'); ?></strong>
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <small class="text-muted d-block">Status</small>
                                        <span class="badge <?php echo ($result->Status == 1) ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo ($result->Status == 1) ? 'Active' : 'Inactive'; ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
