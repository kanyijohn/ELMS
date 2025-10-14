<?php
// admin/manageemployee.php
session_start();

// show errors while debugging — remove or tone down in production
error_reporting(E_ALL);
ini_set('display_errors', 1);

// include config (make path robust)
require_once __DIR__ . '/../includes/config.php';

// ensure admin is logged in (adjust session key if your project uses a different one)
if (!isset($_SESSION['alogin']) || empty($_SESSION['alogin'])) {
    // redirect to the root login page (adjust if your login is in admin/index.php)
    header('Location: ../index.php');
    exit();
}

// ====================
// Handle actions: activate / deactivate / delete
// ====================
try {
    if (isset($_GET['inid'])) { // deactivate
        $id = intval($_GET['inid']);
        $sql = "UPDATE tblemployees SET Status = 0 WHERE id = :id";
        $q = $dbh->prepare($sql);
        $q->bindParam(':id', $id, PDO::PARAM_INT);
        $q->execute();
        header('Location: manageemployee.php');
        exit();
    }

    if (isset($_GET['id'])) { // activate
        $id = intval($_GET['id']);
        $sql = "UPDATE tblemployees SET Status = 1 WHERE id = :id";
        $q = $dbh->prepare($sql);
        $q->bindParam(':id', $id, PDO::PARAM_INT);
        $q->execute();
        header('Location: manageemployee.php');
        exit();
    }

    if (isset($_GET['delid'])) { // delete
        $id = intval($_GET['delid']);
        $sql = "DELETE FROM tblemployees WHERE id = :id";
        $q = $dbh->prepare($sql);
        $q->bindParam(':id', $id, PDO::PARAM_INT);
        $q->execute();
        header('Location: manageemployee.php');
        exit();
    }
} catch (Exception $e) {
    // log error and continue — show user-friendly message later
    error_log("Employee action error: " . $e->getMessage());
}

// ====================
// Search & filter
// ====================
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';

// Build SQL
$sql = "SELECT id, EmpId, FirstName, LastName, EmailId, Department, RegDate, Status
        FROM tblemployees
        WHERE 1 = 1";
$params = [];

if ($search !== '') {
    $sql .= " AND (FirstName LIKE :s OR LastName LIKE :s OR EmailId LIKE :s OR Department LIKE :s)";
    $params[':s'] = '%' . $search . '%';
}

if ($status_filter === '0' || $status_filter === '1') {
    $sql .= " AND Status = :status_filter";
    $params[':status_filter'] = (int)$status_filter;
}

$sql .= " ORDER BY RegDate DESC";

try {
    $stmt = $dbh->prepare($sql);
    $stmt->execute($params);
    $results = $stmt->fetchAll(PDO::FETCH_OBJ);
    $rowCount = $stmt->rowCount();
} catch (Exception $e) {
    // fatal DB error
    $results = [];
    $rowCount = 0;
    $dbError = $e->getMessage();
    error_log("ManageEmployee DB error: " . $dbError);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Admin — Manage Employees</title>

    <!-- Bootstrap 5 (CDN) & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet"/>

    <!-- Optional: your custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css" />
    <style>
        .table-modern { font-size: .95rem; }
        .badge-active { background: #28a745; color: #fff; }
        .badge-inactive { background: #dc3545; color: #fff; }
        .alert-debug { background: #fff3cd; border-color: #ffeeba; color:#856404; }
    </style>
</head>
<body>
      <div class="dashboard-container">
        <?php include('includes/sidebar.php'); ?>
<div class="container-fluid my-4">
    
    <div class="row">
        <div class="col-12 mb-3 d-flex justify-content-between align-items-center">
            <div>
                <h3 class="mb-0">Manage Employees</h3>
                <small class="text-muted">Search, filter and administer employees</small>
            </div>
            <div>
                <a href="addemployee.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Add Employee</a>
            </div>
        </div>
    </div>

    <!-- Search & Filter -->
    <form method="get" class="row g-2 mb-4">
        <div class="col-md-5">
            <input type="text" name="search" value="<?php echo htmlentities($search); ?>" class="form-control" placeholder="Search by name, email or department">
        </div>
        <div class="col-md-3">
            <select name="status" class="form-select">
                <option value="">All statuses</option>
                <option value="1" <?php if ($status_filter === '1') echo 'selected'; ?>>Active</option>
                <option value="0" <?php if ($status_filter === '0') echo 'selected'; ?>>Inactive</option>
            </select>
        </div>
        <div class="col-md-4">
            <button class="btn btn-outline-primary" type="submit"><i class="fas fa-search"></i> Search</button>
            <a href="manageemployee.php" class="btn btn-outline-secondary ms-2"><i class="fas fa-undo"></i> Reset</a>
        </div>
    </form>

    <?php if (!empty($dbError)) { ?>
        <div class="alert alert-danger">Database error: <?php echo htmlentities($dbError); ?></div>
    <?php } ?>

    <?php if ($rowCount > 0) { ?>
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-modern table-hover mb-0">
                        <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Employee ID</th>
                            <th>Full Name</th>
                            <th>Email</th>
                            <th>Department</th>
                            <th>Registered</th>
                            <th>Status</th>
                            <th class="text-center">Actions</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $cnt = 1;
                        foreach ($results as $row) {
                            $statusLabel = ($row->Status == 1)
                                ? '<span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Active</span>'
                                : '<span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i>Inactive</span>';
                            ?>
                            <tr>
                                <td><?php echo $cnt++; ?></td>
                                <td><?php echo htmlentities($row->EmpId); ?></td>
                                <td><?php echo htmlentities($row->FirstName . ' ' . $row->LastName); ?></td>
                                <td><?php echo htmlentities($row->EmailId); ?></td>
                                <td><?php echo htmlentities($row->Department); ?></td>
                                <td><?php echo htmlentities($row->RegDate); ?></td>
                                <td><?php echo $statusLabel; ?></td>
                                <td class="text-center">
                                    <div class="btn-group" role="group">
                                        <a href="editemployee.php?empid=<?php echo urlencode($row->id); ?>" class="btn btn-sm btn-warning" title="Edit"><i class="fas fa-edit"></i></a>

                                        <?php if ($row->Status == 1) { ?>
                                            <a href="manageemployee.php?inid=<?php echo urlencode($row->id); ?>" class="btn btn-sm btn-secondary" onclick="return confirm('Deactivate this employee?');" title="Deactivate"><i class="fas fa-user-slash"></i></a>
                                        <?php } else { ?>
                                            <a href="manageemployee.php?id=<?php echo urlencode($row->id); ?>" class="btn btn-sm btn-success" onclick="return confirm('Activate this employee?');" title="Activate"><i class="fas fa-user-check"></i></a>
                                        <?php } ?>

                                        <a href="manageemployee.php?delid=<?php echo urlencode($row->id); ?>" class="btn btn-sm btn-danger" onclick="return confirm('Permanently delete this employee?');" title="Delete"><i class="fas fa-trash-alt"></i></a>
                                    </div>
                                </td>
                            </tr>
                        <?php } // foreach ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php } else { ?>
        <div class="card shadow-sm">
            <div class="card-body text-center">
                <p class="mb-1"><strong>No employees found</strong></p>
                <p class="text-muted mb-3">Try clearing the search/filter or add new employees.</p>
                <a href="addemployee.php" class="btn btn-primary"><i class="fas fa-user-plus me-1"></i> Add Employee</a>
            </div>
        </div>
    <?php } ?>

    <div class="mt-3">
        <small class="text-muted">Tip: if you expect employees but none appear, check the database connection (includes/config.php) and confirm `tblemployees` contains rows.</small>
    </div>
</div>

<!-- JS (Bootstrap) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
