<?php
session_start();
include('includes/config.php');
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
} else {
    // Fetch supervisors for dropdown
    $sql = "SELECT id, FirstName, LastName FROM tblemployees WHERE role='Supervisor'";
    $query = $dbh->prepare($sql);
    $query->execute();
    $supervisors = $query->fetchAll(PDO::FETCH_OBJ);

    // Auto-generate next EmpId
    $sql = "SELECT MAX(EmpId) AS maxid FROM tblemployees";
    $query = $dbh->prepare($sql);
    $query->execute();
    $row = $query->fetch(PDO::FETCH_OBJ);
    $nextEmpId = $row && $row->maxid ? intval($row->maxid) + 1 : 1001;

    if (isset($_POST['add'])) {
        $empid = !empty($_POST['empcode']) ? $_POST['empcode'] : $nextEmpId;
        $fname = $_POST['firstName'];
        $lname = $_POST['lastName'];
        $email = $_POST['email'];
        $password = md5($_POST['password']);
        $gender = $_POST['gender'];
        $dob = !empty($_POST['dob']) ? $_POST['dob'] : date('Y-m-d'); // Default to today if missing
        $department = $_POST['department'];
        $address = $_POST['address'];
        $city = !empty($_POST['city']) ? $_POST['city'] : 'N/A';
        $country = !empty($_POST['country']) ? $_POST['country'] : 'N/A';
        $mobileno = $_POST['mobileno'];
        $status = 1;
        $role = isset($_POST['role']) ? $_POST['role'] : 'Employee';
        $supervisor_id = !empty($_POST['supervisor_id']) ? intval($_POST['supervisor_id']) : null;

        $sql = "INSERT INTO tblemployees (EmpId,FirstName,LastName,EmailId,Password,Gender,Dob,Department,Address,City,Country,Phonenumber,Status,role,supervisor_id)
                VALUES(:empid,:fname,:lname,:email,:password,:gender,:dob,:department,:address,:city,:country,:mobileno,:status,:role,:supervisor_id)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->bindParam(':fname', $fname, PDO::PARAM_STR);
        $query->bindParam(':lname', $lname, PDO::PARAM_STR);
        $query->bindParam(':email', $email, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->bindParam(':gender', $gender, PDO::PARAM_STR);
        $query->bindParam(':dob', $dob, PDO::PARAM_STR);
        $query->bindParam(':department', $department, PDO::PARAM_STR);
        $query->bindParam(':address', $address, PDO::PARAM_STR);
        $query->bindParam(':city', $city, PDO::PARAM_STR);
        $query->bindParam(':country', $country, PDO::PARAM_STR);
        $query->bindParam(':mobileno', $mobileno, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':role', $role, PDO::PARAM_STR);
        if ($supervisor_id === null) {
            $query->bindValue(':supervisor_id', null, PDO::PARAM_NULL);
        } else {
            $query->bindValue(':supervisor_id', $supervisor_id, PDO::PARAM_INT);
        }
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            $msg = "✅ Employee record added successfully!";
        } else {
            $error = "⚠️ Something went wrong. Please try again.";
        }
    }
    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Employee | ELMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>
<div class="dashboard-container">
    <?php include('includes/sidebar.php'); ?>

    <div class="main-content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Add New Employee</h1>
                <p class="text-muted mb-0">Register a new employee in the system</p>
            </div>
            <div>
                <a href="manageemployee.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card shadow-sm p-4">
                    <?php if(isset($error)) { ?>
                        <div class="alert alert-danger"><?php echo htmlentities($error); ?></div>
                    <?php } elseif(isset($msg)) { ?>
                        <div class="alert alert-success"><?php echo htmlentities($msg); ?></div>
                    <?php } ?>

                    <form method="post" name="addemp">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">First Name *</label>
                                <input type="text" class="form-control" name="firstName" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Last Name *</label>
                                <input type="text" class="form-control" name="lastName" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Email *</label>
                                <input type="email" class="form-control" name="email" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Password *</label>
                                <input type="password" class="form-control" name="password" required>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Gender</label>
                                <select class="form-select" name="gender">
                                    <option value="Male">Male</option>
                                    <option value="Female">Female</option>
                                    <option value="Other">Other</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Date of Birth</label>
                                <input type="date" class="form-control" name="dob">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Department *</label>
                            <select class="form-select" name="department" required>
                                <option value="">Select Department</option>
                                <?php
                                $sql = "SELECT DepartmentName FROM tbldepartments";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $departments = $query->fetchAll(PDO::FETCH_OBJ);
                                foreach ($departments as $department) { ?>
                                    <option value="<?php echo htmlentities($department->DepartmentName); ?>">
                                        <?php echo htmlentities($department->DepartmentName); ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">City</label>
                                <input type="text" class="form-control" name="city">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Country</label>
                                <input type="text" class="form-control" name="country">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Phone Number</label>
                                <input type="text" class="form-control" name="mobileno">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Address</label>
                            <textarea class="form-control" name="address" rows="3"></textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Role</label>
                                <select class="form-select" name="role">
                                    <option value="Employee">Employee</option>
                                    <option value="Supervisor">Supervisor</option>
                                    <option value="Admin">Admin</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Assign Supervisor (optional)</label>
                                <select class="form-select" name="supervisor_id">
                                    <option value="">-- None --</option>
                                    <?php foreach($supervisors as $sup) { ?>
                                        <option value="<?php echo $sup->id; ?>">
                                            <?php echo htmlentities($sup->FirstName . " " . $sup->LastName); ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="manageemployee.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="add" class="btn btn-primary">
                                <i class="fas fa-user-plus"></i> Add Employee
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>
