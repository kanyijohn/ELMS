<?php
session_start();
include('includes/config.php');
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {
    $msg = "";
    $error = "";

    // ✅ Handle Department Creation
    if (isset($_POST['add'])) {
        $deptname = trim($_POST['departmentname']);
        $deptshortname = trim($_POST['departmentshortname']);
        $deptcode = trim($_POST['deptcode']);

        // Check if department already exists (by name or code)
        $checkSql = "SELECT * FROM tbldepartments WHERE DepartmentName = :deptname OR DepartmentCode = :deptcode";
        $checkQuery = $dbh->prepare($checkSql);
        $checkQuery->bindParam(':deptname', $deptname, PDO::PARAM_STR);
        $checkQuery->bindParam(':deptcode', $deptcode, PDO::PARAM_STR);
        $checkQuery->execute();

        if ($checkQuery->rowCount() > 0) {
            $error = "Department already exists!";
        } else {
            $creationDate = date("Y-m-d H:i:s");
            $sql = "INSERT INTO tbldepartments (DepartmentName, DepartmentCode, DepartmentShortName, CreationDate)
                    VALUES (:deptname, :deptcode, :deptshortname, :creationDate)";
            $query = $dbh->prepare($sql);
            $query->bindParam(':deptname', $deptname, PDO::PARAM_STR);
            $query->bindParam(':deptcode', $deptcode, PDO::PARAM_STR);
            $query->bindParam(':deptshortname', $deptshortname, PDO::PARAM_STR);
            $query->bindParam(':creationDate', $creationDate, PDO::PARAM_STR);
            $query->execute();

            if ($query->rowCount() > 0) {
                $msg = "Department Created Successfully";
            } else {
                $error = "Something went wrong. Please try again!";
            }
        }
    }

    // ✅ Fetch all departments to display
    $sql = "SELECT * FROM tbldepartments ORDER BY id DESC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $departments = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments | Employee Leave Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>
    <div class="dashboard-container">
        <?php include('includes/sidebar.php'); ?>

        <div class="main-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Manage Departments</h1>
                    <p class="text-muted mb-0">Create and view all departments</p>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-5">
                    <div class="card shadow-sm mb-4">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-building"></i> Create Department</h5>
                        </div>
                        <div class="card-body">
                            <?php if ($error) { ?>
                                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?></div>
                            <?php } elseif ($msg) { ?>
                                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?></div>
                            <?php } ?>

                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label">Department Code</label>
                                    <input type="text" name="deptcode" class="form-control" placeholder="Enter department code" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Department Name</label>
                                    <input type="text" name="departmentname" class="form-control" placeholder="Enter department name" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">Department Short Name</label>
                                    <input type="text" name="departmentshortname" class="form-control" placeholder="Enter short name" required>
                                </div>

                                <!-- ✅ Fixed Create Department Button -->
                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="managedepartments.php" class="btn btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" name="add" class="btn-enhanced btn-primary">
                                        <i class="fas fa-save"></i> Create Department
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <h5 class="mb-0"><i class="fas fa-list"></i> Department List</h5>
                        </div>
                        <div class="card-body">
                            <table class="table table-striped table-hover align-middle">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Department Code</th>
                                        <th>Department Name</th>
                                        <th>Short Name</th>
                                        <th>Creation Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (count($departments) > 0) {
                                        foreach ($departments as $row) { ?>
                                            <tr>
                                                <td><?php echo htmlentities($row->id); ?></td>
                                                <td><?php echo htmlentities($row->DepartmentCode); ?></td>
                                                <td><?php echo htmlentities($row->DepartmentName); ?></td>
                                                <td><?php echo htmlentities($row->DepartmentShortName); ?></td>
                                                <td><?php echo htmlentities($row->CreationDate); ?></td>
                                            </tr>
                                    <?php } } else { ?>
                                        <tr><td colspan="5" class="text-center text-muted">No departments found</td></tr>
                                    <?php } ?>
                                </tbody>
                            </table>
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
<?php } ?>
