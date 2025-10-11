<?php
session_start();
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {

    // Enable error visibility for debugging (recommended during development)
    error_reporting(E_ALL);
    ini_set('display_errors', 1);

    // DELETE DEPARTMENT
    if (isset($_GET['del'])) {
        $id = intval($_GET['del']);
        $sql = "DELETE FROM tbldepartments WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        if ($query->execute()) {
            $msg = "Department record deleted successfully";
        } else {
            $error = "Error deleting department.";
        }
    }

    // ✅ FETCH ALL DEPARTMENTS
    $sql = "SELECT id, DepartmentCode, DepartmentName, DepartmentShortName, CreationDate 
            FROM tbldepartments 
            ORDER BY CreationDate DESC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $deptCount = $query->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Departments | Employee Leave Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f8f9fa;
        }
        .card {
            border-radius: 12px;
        }
        .table thead {
            background-color: #0d6efd;
            color: #fff;
        }
        .table td, .table th {
            vertical-align: middle !important;
        }
        .btn-action {
            border-radius: 6px;
            padding: 5px 10px;
            font-size: 14px;
        }
        
        .alert {
            border-radius: 8px;
        }
    </style>
</head>
<body>
<div class="dashboard-container d-flex">
    <!-- Sidebar -->
    <?php include('includes/sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content p-4 flex-grow-1">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1 text-primary">Manage Departments</h1>
                <p class="text-muted mb-0">View and manage all departments in the organization</p>
            </div>
            <div>
                <a href="adddepartment.php" class="btn btn-primary">
                    <i class="fas fa-plus-circle"></i> Add New Department
                </a>
            </div>
        </div>

        <!-- Alerts -->
        <?php if (isset($msg)) { ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i> <?php echo htmlentities($msg); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } elseif (isset($error)) { ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-triangle me-2"></i> <?php echo htmlentities($error); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php } ?>

        <!-- Departments Table -->
        <div class="card shadow-sm border-0">
            <div class="card-header d-flex justify-content-between align-items-center bg-light">
                <h5 class="mb-0"><i class="fas fa-building"></i> Department List</h5>
                <span class="badge bg-primary"><?php echo $deptCount; ?> Departments</span>
            </div>
            <div class="card-body">
                <?php if ($deptCount > 0) { ?>
                <div class="table-responsive">
                    <table class="table table-striped align-middle">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Department ID</th>
                                <th>Department Code</th>
                                <th>Department Name</th>
                                <th>Department Short Name</th>
                                <th>Creation Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        <?php
                        $cnt = 1;
                        foreach ($results as $result) {
                        ?>
                        <tr>
                            <td><?php echo htmlentities($cnt); ?></td>
                            <td>DEP<?php echo htmlentities($result->id); ?></td>
                            <td><?php echo htmlentities($result->DepartmentCode); ?></td>
                            <td><?php echo htmlentities($result->DepartmentName); ?></td>
                            <td><?php echo htmlentities($result->DepartmentShortName); ?></td>
                            <td><?php echo htmlentities($result->CreationDate); ?></td>
                            <td>
                                <div class="d-flex gap-2">
                                    <a href="editdepartment.php?deptid=<?php echo htmlentities($result->id); ?>" 
                                       class="btn btn-sm btn-warning btn-action" title="Edit Department">
                                       <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="managedepartments.php?del=<?php echo htmlentities($result->id); ?>" 
                                       class="btn btn-sm btn-danger btn-action" 
                                       onclick="return confirm('Are you sure you want to delete this department?');" 
                                       title="Delete Department">
                                       <i class="fas fa-trash"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php $cnt++; } ?>
                        </tbody>
                    </table>
                </div>
                <?php } else { ?>
                    <div class="text-center py-5">
                        <i class="fas fa-building fa-3x text-muted mb-3"></i>
                        <h4 class="text-muted">No Departments Found</h4>
                        <p class="text-muted">Get started by creating your first department.</p>
                        <a href="adddepartment.php" class="btn btn-primary">
                            <i class="fas fa-plus-circle"></i> Add Department
                        </a>
                    </div>
                <?php } ?>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>
