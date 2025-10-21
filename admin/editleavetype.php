<?php
session_start();

include 'includes/config.php';

// Redirect if not logged in
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

$error = "";
$msg = "";
$leavetype = null;

// Get leave type details
if (isset($_GET['lid']) && is_numeric($_GET['lid'])) {
    $lid = intval($_GET['lid']);
    $sql = "SELECT * FROM tblleavetype WHERE id = :lid";
    $query = $dbh->prepare($sql);
    $query->bindParam(':lid', $lid, PDO::PARAM_INT);
    $query->execute();
    $leavetype = $query->fetch(PDO::FETCH_OBJ);

    if (!$leavetype) {
        $error = "Leave type not found or invalid ID.";
    }
} else {
    $error = "Invalid request. Leave type ID missing.";
}

// Update leave type
if (isset($_POST['update'])) {
    if (!$leavetype) {
        $error = "Cannot update. Leave type not found.";
    } else {
        $leavetypeName = trim($_POST['leavetype']);
        $description = trim($_POST['description']);

        if ($leavetypeName == "") {
            $error = "Leave type name cannot be empty.";
        } else {
            $sql = "UPDATE tblleavetype 
                    SET LeaveType = :leavetype, 
                        Description = :description
                    WHERE id = :lid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':leavetype', $leavetypeName, PDO::PARAM_STR);
            $query->bindParam(':description', $description, PDO::PARAM_STR);
            $query->bindParam(':lid', $lid, PDO::PARAM_INT);

            if ($query->execute()) {
                $msg = "Leave type updated successfully.";
                // Refresh leave type info
                $sql = "SELECT * FROM tblleavetype WHERE id = :lid";
                $query = $dbh->prepare($sql);
                $query->bindParam(':lid', $lid, PDO::PARAM_INT);
                $query->execute();
                $leavetype = $query->fetch(PDO::FETCH_OBJ);
            } else {
                $error = "Something went wrong. Please try again.";
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
    <title>Edit Leave Type | Employee Leave Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f6fa;
        }
        .dashboard-container {
            display: flex;
        }
        .main-content {
            flex: 1;
            padding: 30px;
        }
        .enhanced-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
            margin-bottom: 30px;
            overflow: hidden;
        }
        .enhanced-card .card-header {
            background: #f9fafb;
            padding: 16px 24px;
            border-bottom: 1px solid #e0e0e0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .enhanced-card .card-body {
            padding: 24px;
        }
        .btn-enhanced {
            border-radius: 8px;
            padding: 10px 16px;
        }
        .btn-primary {
            background-color: #007bff;
            border: none;
        }
        .alert-modern {
            padding: 12px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background-color: #e6ffed;
            color: #256029;
        }
        .alert-error {
            background-color: #ffe6e6;
            color: #8b0000;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include('includes/sidebar.php'); ?>

        <!-- Main Content -->
        <div class="main-content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Edit Leave Type</h1>
                    <p class="text-muted mb-0">Update leave type details and status</p>
                </div>
                <div>
                    <a href="manageleavetype.php" class="btn-enhanced btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back
                    </a>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert-modern alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?>
                </div>
            <?php endif; ?>

            <?php if ($msg): ?>
                <div class="alert-modern alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?>
                </div>
            <?php endif; ?>

            <?php if ($leavetype): ?>
            <div class="enhanced-card">
                <div class="card-header">
                    <h5><i class="fas fa-calendar-edit"></i> Edit Leave Type Information</h5>
                </div>
                <div class="card-body">
                    <form method="post">
                        <div class="mb-3">
                            <label class="form-label">Leave Type Name</label>
                            <input type="text" class="form-control" name="leavetype"
                                   value="<?php echo htmlentities($leavetype->LeaveType); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea class="form-control" name="description" rows="4"><?php echo htmlentities($leavetype->Description); ?></textarea>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="update" class="btn-enhanced btn-primary">
                                <i class="fas fa-save"></i> Update Leave Type
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
