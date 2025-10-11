<?php
session_start();
include('includes/config.php');
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
} else {
    if (isset($_POST['add'])) {
        $leavetype = $_POST['leavetype'];
        $description = $_POST['description'];
        $sql = "INSERT INTO tblleavetype(LeaveType,Description) VALUES(:leavetype,:description)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();
        if ($lastInsertId) {
            $msg = "Leave type added Successfully";
        } else {
            $error = "Something went wrong. Please try again";
        }

    }

    ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Leave Type | Employee Leave Management System</title>
    
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
        <div class="main-content">
            <!-- Header -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Add Leave Type</h1>
                    <p class="text-muted mb-0">Create a new type of leave for employees</p>
                </div>
                <div>
                    <a href="manageleavetype.php" class="btn-enhanced btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Leave Types
                    </a>
                </div>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="enhanced-card">
                        <div class="card-header">
                            <h5><i class="fas fa-calendar-plus"></i> Leave Type Information</h5>
                        </div>
                        <div class="card-body">
                            <?php if(isset($error)) { ?>
                                <div class="alert-modern alert-error">
                                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?>
                                </div>
                            <?php } ?>
                            
                            <?php if(isset($msg)) { ?>
                                <div class="alert-modern alert-success">
                                    <i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?>
                                </div>
                            <?php } ?>

                            <form method="post" class="form-modern">
                                <div class="mb-4">
                                    <label class="form-label">Leave Type Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="leavetype" 
                                           placeholder="e.g., Annual Leave, Sick Leave, Emergency Leave" required>
                                    <div class="form-text">Use clear and descriptive names that employees will understand.</div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label">Description</label>
                                    <textarea class="form-control" name="description" rows="3" 
                                              placeholder="Describe the purpose and conditions of this leave type"></textarea>
                                    <div class="form-text">Explain when and how this leave type should be used.</div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="form-label">Default Days Per Year</label>
                                            <input type="number" class="form-control" name="defaultdays" 
                                                   min="0" max="365" value="0"
                                                   placeholder="Leave 0 for unlimited">
                                            <div class="form-text">Set 0 for unlimited or specify the maximum days allowed per year.</div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-4">
                                            <label class="form-label">Status</label>
                                            <select class="form-control" name="status">
                                                <option value="1" selected>Active</option>
                                                <option value="0">Inactive</option>
                                            </select>
                                            <div class="form-text">Inactive leave types won't be available for new applications.</div>
                                        </div>
                                    </div>
                                </div>

                                <div class="d-flex gap-2 justify-content-end">
                                    <a href="manageleavetype.php" class="btn-enhanced btn-secondary">
                                        <i class="fas fa-times"></i> Cancel
                                    </a>
                                    <button type="submit" name="submit" class="btn-enhanced btn-primary">
                                        <i class="fas fa-save"></i> Create Leave Type
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Leave Type Examples -->
                    <div class="enhanced-card mt-4">
                        <div class="card-header">
                            <h6><i class="fas fa-lightbulb"></i> Common Leave Types</h6>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-sun text-warning me-2"></i>
                                            <strong>Annual Leave</strong>
                                            <small class="text-muted d-block">Paid time off for vacation</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-heartbeat text-danger me-2"></i>
                                            <strong>Sick Leave</strong>
                                            <small class="text-muted d-block">For illness or medical appointments</small>
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-baby text-info me-2"></i>
                                            <strong>Maternity Leave</strong>
                                            <small class="text-muted d-block">For childbirth and childcare</small>
                                        </li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li class="mb-2">
                                            <i class="fas fa-umbrella-beach text-success me-2"></i>
                                            <strong>Casual Leave</strong>
                                            <small class="text-muted d-block">Short notice personal time off</small>
                                        </li>
                                        <li class="mb-2">
                                            <i class="fas fa-star text-primary me-2"></i>
                                            <strong>Public Holiday</strong>
                                            <small class="text-muted d-block">National or religious holidays</small>
                                        </li>
                                        <li class="mb-0">
                                            <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                            <strong>Emergency Leave</strong>
                                            <small class="text-muted d-block">Unforeseen urgent matters</small>
                                        </li>
                                    </ul>
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
    <script src="../assets/js/modern.js"></script>
</body>
</html>
<?php } ?>