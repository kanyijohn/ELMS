<?php
session_start();
error_reporting(0);
include 'includes/config.php';

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {
    // DELETE leave type
    if (isset($_GET['del'])) {
        $id = intval($_GET['del']);
        $sql = "DELETE FROM tblleavetype WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
        $msg = "Leave type deleted successfully.";
    }

    // ACTIVATE leave type
    if (isset($_GET['activate'])) {
        $id = intval($_GET['activate']);
        $sql = "UPDATE tblleavetype SET IsActive = 1 WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
    }

    // DEACTIVATE leave type
    if (isset($_GET['deactivate'])) {
        $id = intval($_GET['deactivate']);
        $sql = "UPDATE tblleavetype SET IsActive = 0 WHERE id = :id";
        $query = $dbh->prepare($sql);
        $query->bindParam(':id', $id, PDO::PARAM_INT);
        $query->execute();
    }

    // FETCH all leave types
    $sql = "SELECT * FROM tblleavetype ORDER BY id DESC";
    $query = $dbh->prepare($sql);
    $query->execute();
    $results = $query->fetchAll(PDO::FETCH_OBJ);
    $rowcount = $query->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Leave Types | Employee Leave Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include('includes/sidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content container py-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Manage Leave Types</h1>
                    <p class="text-muted mb-0">View, activate, deactivate, or delete available leave types.</p>
                </div>
                <div>
                    <a href="addleavetype.php" class="btn btn-primary">
                        <i class="fas fa-plus-circle"></i> Add New Leave Type
                    </a>
                </div>
            </div>

            <!-- Alert messages -->
            <?php if (!empty($msg)) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <!-- Leave Types Table -->
            <div class="card shadow-sm mb-4">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0"><i class="fas fa-calendar-alt me-2"></i>Leave Types</h5>
                    <span class="badge bg-light text-dark"><?php echo $rowcount; ?> total</span>
                </div>
                <div class="card-body">
                    <?php if ($rowcount > 0) { ?>
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>#</th>
                                    <th>Leave Type</th>
                                    <th>Description</th>
                                    <th>Creation Date</th>
                                    <th>Status</th>
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
                                    <td><?php echo htmlentities($result->LeaveType); ?></td>
                                    <td><?php echo htmlentities($result->Description ? $result->Description : '-'); ?></td>
                                    <td><?php echo htmlentities($result->CreationDate); ?></td>
                                    <td>
                                        <?php if ($result->IsActive == 1) { ?>
                                            <span class="badge bg-success">Active</span>
                                        <?php } else { ?>
                                            <span class="badge bg-secondary">Inactive</span>
                                        <?php } ?>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-1">
                                            <a href="editleavetype.php?ltid=<?php echo htmlentities($result->id); ?>" class="btn btn-warning btn-sm" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($result->IsActive == 1) { ?>
                                                <a href="manageleavetype.php?deactivate=<?php echo htmlentities($result->id); ?>" class="btn btn-danger btn-sm" title="Deactivate" onclick="return confirm('Are you sure you want to deactivate this leave type?');">
                                                    <i class="fas fa-ban"></i>
                                                </a>
                                            <?php } else { ?>
                                                <a href="manageleavetype.php?activate=<?php echo htmlentities($result->id); ?>" class="btn btn-success btn-sm" title="Activate">
                                                    <i class="fas fa-check"></i>
                                                </a>
                                            <?php } ?>
                                            <a href="manageleavetype.php?del=<?php echo htmlentities($result->id); ?>" class="btn btn-outline-danger btn-sm" title="Delete" onclick="return confirm('Are you sure you want to delete this record?');">
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
                            <i class="fas fa-calendar-alt fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">No Leave Types Found</h5>
                            <p class="text-muted">Click below to create your first leave type.</p>
                            <a href="addleavetype.php" class="btn btn-primary">
                                <i class="fas fa-plus-circle"></i> Add Leave Type
                            </a>
                        </div>
                    <?php } ?>
                </div>
            </div>

            <!-- Leave Type Statistics -->
            <?php if ($rowcount > 0) {
                $active = 0; $inactive = 0;
                foreach ($results as $r) {
                    if ($r->IsActive == 1) $active++; else $inactive++;
                }
            ?>
            <div class="row text-center">
                <div class="col-md-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h3 class="text-primary"><?php echo $rowcount; ?></h3>
                            <p class="mb-0">Total Leave Types</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h3 class="text-success"><?php echo $active; ?></h3>
                            <p class="mb-0">Active Types</p>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card shadow-sm mb-3">
                        <div class="card-body">
                            <h3 class="text-danger"><?php echo $inactive; ?></h3>
                            <p class="mb-0">Inactive Types</p>
                        </div>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php } ?>
