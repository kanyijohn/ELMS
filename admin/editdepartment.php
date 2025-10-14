<?php
session_start();
error_reporting(0);
include 'includes/config.php';

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {

    $msg = "";
    $error = "";

    // Get department ID
    $did = intval($_GET['deptid'] ?? 0);
    if ($did <= 0) {
        die("Invalid Department ID.");
    }

    // Fetch existing department details
    $sql = "SELECT * FROM tbldepartments WHERE id = :did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':did', $did, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        die("Department not found.");
    }

    // Handle form submission
    if (isset($_POST['update'])) {
        $deptname = trim($_POST['departmentname']);
        $deptshortname = trim($_POST['departmentshortname']);
        $deptcode = trim($_POST['departmentcode']);

        if ($deptname == "" || $deptshortname == "" || $deptcode == "") {
            $error = "All fields are required.";
        } else {
            $update_sql = "UPDATE tbldepartments 
                           SET DepartmentName = :deptname,
                               DepartmentShortName = :deptshortname,
                               DepartmentCode = :deptcode
                           WHERE id = :did";
            $update_query = $dbh->prepare($update_sql);
            $update_query->bindParam(':deptname', $deptname, PDO::PARAM_STR);
            $update_query->bindParam(':deptshortname', $deptshortname, PDO::PARAM_STR);
            $update_query->bindParam(':deptcode', $deptcode, PDO::PARAM_STR);
            $update_query->bindParam(':did', $did, PDO::PARAM_INT);

            if ($update_query->execute()) {
                $msg = "Department updated successfully!";
                // Refresh data
                $query->execute();
                $result = $query->fetch(PDO::FETCH_OBJ);
            } else {
                $error = "Update failed. Please try again.";
            }
        }
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Department | ELMS Admin</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include('includes/sidebar.php'); ?>

        <div class="main-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="h4 mb-1"><i class="fas fa-edit"></i> Edit Department</h2>
                    <p class="text-muted mb-0">Update department information</p>
                </div>
                <a href="managedepartments.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Departments
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body">
                    <?php if ($error) { ?>
                        <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?= htmlentities($error); ?></div>
                    <?php } ?>
                    <?php if ($msg) { ?>
                        <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?= htmlentities($msg); ?></div>
                    <?php } ?>

                    <form method="POST">
                        <div class="mb-3">
                            <label for="departmentname" class="form-label">Department Name *</label>
                            <input type="text" id="departmentname" name="departmentname" class="form-control"
                                   value="<?= htmlentities($result->DepartmentName); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="departmentshortname" class="form-label">Department Short Name *</label>
                            <input type="text" id="departmentshortname" name="departmentshortname" class="form-control"
                                   value="<?= htmlentities($result->DepartmentShortName); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="departmentcode" class="form-label">Department Code *</label>
                            <input type="text" id="departmentcode" name="departmentcode" class="form-control"
                                   value="<?= htmlentities($result->DepartmentCode); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="creationdate" class="form-label">Creation Date</label>
                            <input type="text" id="creationdate" name="creationdate" class="form-control"
                                   value="<?= htmlentities($result->CreationDate); ?>" readonly>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="managedepartments.php" class="btn btn-outline-secondary">
                                <i class="fas fa-times"></i> Cancel
                            </a>
                            <button type="submit" name="update" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Department
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card shadow-sm mt-4">
                <div class="card-header bg-light">
                    <h6 class="mb-0"><i class="fas fa-chart-bar"></i> Department Statistics</h6>
                </div>
                <div class="card-body text-center">
                    <?php
                    // Count employees in this department
                    $sql_emp = "SELECT COUNT(*) AS emp_count FROM tblemployees WHERE Department = :deptname";
                    $query_emp = $dbh->prepare($sql_emp);
                    $query_emp->bindParam(':deptname', $result->DepartmentName, PDO::PARAM_STR);
                    $query_emp->execute();
                    $emp_count = $query_emp->fetch(PDO::FETCH_OBJ)->emp_count;
                    ?>
                    <h5 class="text-primary"><?= htmlentities($emp_count); ?></h5>
                    <small class="text-muted">Total Employees</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>
