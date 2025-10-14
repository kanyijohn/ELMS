<?php
session_start();
error_reporting(0);
include 'includes/config.php';

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
} else {
    $eid = intval($_GET['empid']);

    // Fetch existing employee record
    $sql = "SELECT * FROM tblemployees WHERE id=:eid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':eid', $eid, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if (isset($_POST['update'])) {
        $fname = $_POST['firstName'];
        $lname = $_POST['lastName'];
        $gender = $_POST['gender'];
        $dob = $_POST['dob'];
        $department = $_POST['department'];
        $address = $_POST['address'];
        $city = $_POST['city'];
        $country = $_POST['country'];
        $mobileno = $_POST['mobileno'];

        $sql = "UPDATE tblemployees 
                SET FirstName=:fname, LastName=:lname, Gender=:gender, Dob=:dob, Department=:department, 
                    Address=:address, City=:city, Country=:country, Phonenumber=:mobileno 
                WHERE id=:eid";
        $query = $dbh->prepare($sql);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':department', $department, PDO::PARAM_STR);
        $query->bindParam(':address', $address, PDO::PARAM_STR);
        $query->bindParam(':city', $city, PDO::PARAM_STR);
        $query->bindParam(':country', $country, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
        $query->bindParam(':eid', $eid, PDO::PARAM_INT);
        $query->execute();

        $msg = "Employee record updated successfully!";
        // Refresh record
        $query->execute();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Employee | Employee Leave Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/styles.css">
</head>
<body>
    <div class="dashboard-container d-flex">
        <!-- Sidebar -->
        <?php include('includes/sidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Edit Employee</h1>
                    <p class="text-muted">Update employee details below.</p>
                </div>
                <div>
                    <a href="manageemployee.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <?php if (isset($msg)) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="fas fa-user-edit"></i> Employee Information</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="firstName" class="form-label">First Name</label>
                                <input type="text" id="firstName" name="firstName" class="form-control"
                                       value="<?php echo htmlentities($result->FirstName); ?>" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="lastName" class="form-label">Last Name</label>
                                <input type="text" id="lastName" name="lastName" class="form-control"
                                       value="<?php echo htmlentities($result->LastName); ?>" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="gender" class="form-label">Gender</label>
                                <select id="gender" name="gender" class="form-select">
                                    <option value="Male" <?php if($result->Gender == 'Male') echo 'selected'; ?>>Male</option>
                                    <option value="Female" <?php if($result->Gender == 'Female') echo 'selected'; ?>>Female</option>
                                    <option value="Other" <?php if($result->Gender == 'Other') echo 'selected'; ?>>Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="dob" class="form-label">Date of Birth</label>
                                <input type="date" id="dob" name="dob" class="form-control"
                                       value="<?php echo htmlentities($result->Dob); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="department" class="form-label">Department</label>
                            <select id="department" name="department" class="form-select" required>
                                <option value="">Select Department</option>
                                <?php
                                $sql = "SELECT DepartmentName FROM tbldepartments";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $departments = $query->fetchAll(PDO::FETCH_OBJ);
                                foreach ($departments as $dept) { ?>
                                    <option value="<?php echo htmlentities($dept->DepartmentName); ?>"
                                        <?php if($result->Department == $dept->DepartmentName) echo 'selected'; ?>>
                                        <?php echo htmlentities($dept->DepartmentName); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="mobileno" class="form-label">Mobile Number</label>
                                <input type="text" id="mobileno" name="mobileno" class="form-control"
                                       value="<?php echo htmlentities($result->Phonenumber); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="country" class="form-label">Country</label>
                                <input type="text" id="country" name="country" class="form-control"
                                       value="<?php echo htmlentities($result->Country); ?>">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="address" class="form-label">Address</label>
                            <textarea id="address" name="address" class="form-control" rows="3"><?php echo htmlentities($result->Address); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label for="city" class="form-label">City</label>
                            <input type="text" id="city" name="city" class="form-control"
                                   value="<?php echo htmlentities($result->City); ?>">
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>

        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
