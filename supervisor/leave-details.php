<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}
$supervisor_id = $_SESSION['eid'];

// Handle supervisor action (approve/decline)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_id'], $_POST['action'])) {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    $remark = trim($_POST['SupervisorRemark']);
    $status = $action === 'Approve' ? 'Approved' : 'Declined';
    $actionDate = date('Y-m-d H:i:s');
    $sql = "UPDATE tblleaves SET SupervisorStatus=:status, SupervisorRemark=:remark, SupervisorActionDate=:actionDate WHERE id=:leave_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->bindParam(':remark', $remark, PDO::PARAM_STR);
    $query->bindParam(':actionDate', $actionDate, PDO::PARAM_STR);
    $query->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
    $query->execute();
}

// Fetch leave requests for this supervisor
$sql = "SELECT l.*, e.FirstName, e.LastName FROM tblleaves l
        JOIN tblemployees e ON l.empid = e.id
        WHERE e.supervisor_id = :supid";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$leaves = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Supervisor | Dashboard</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.css"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
<?php include '../includes/header.php';?>
<?php include '../includes/sidebar.php';?>
<main class="mn-inner mt-5">
    <div class="container">
        <h3 class="text-center mb-4">Supervisor Dashboard</h3>
        <ul class="nav nav-tabs mb-3" id="leaveTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="pending-tab" data-toggle="tab" href="#pending" role="tab">Pending</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="approved-tab" data-toggle="tab" href="#approved" role="tab">Approved</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="declined-tab" data-toggle="tab" href="#declined" role="tab">Declined</a>
            </li>
        </ul>
        <div class="tab-content" id="leaveTabsContent">
            <!-- Pending Tab -->
            <div class="tab-pane fade show active" id="pending" role="tabpanel">
                <h5>Pending Leave Requests</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Posting Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <?php if ($leave->SupervisorStatus == 'Pending'): ?>
                        <tr>
                            <td><?= htmlentities($leave->FirstName . ' ' . $leave->LastName) ?></td>
                            <td><?= htmlentities($leave->LeaveType) ?></td>
                            <td><?= htmlentities($leave->FromDate) ?></td>
                            <td><?= htmlentities($leave->ToDate) ?></td>
                            <td><?= htmlentities($leave->Description) ?></td>
                            <td><?= htmlentities($leave->PostingDate) ?></td>
                            <td>
                                <form method="post" style="display:inline;">
                                    <input type="hidden" name="leave_id" value="<?= $leave->id ?>">
                                    <input type="text" name="SupervisorRemark" placeholder="Supervisor Remark" required>
                                    <button type="submit" name="action" value="Approve" class="btn btn-success btn-sm">Approve</button>
                                    <button type="submit" name="action" value="Declined" class="btn btn-danger btn-sm">Decline</button>
                                </form>
                                <a href="leave-details.php?leaveid=<?= $leave->id ?>" class="btn btn-info btn-sm mt-1">View Details</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Approved Tab -->
            <div class="tab-pane fade" id="approved" role="tabpanel">
                <h5>Approved Leave Requests</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Supervisor Remark</th>
                            <th>Supervisor Action Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <?php if ($leave->SupervisorStatus == 'Approved'): ?>
                        <tr>
                            <td><?= htmlentities($leave->FirstName . ' ' . $leave->LastName) ?></td>
                            <td><?= htmlentities($leave->LeaveType) ?></td>
                            <td><?= htmlentities($leave->FromDate) ?></td>
                            <td><?= htmlentities($leave->ToDate) ?></td>
                            <td><?= htmlentities($leave->Description) ?></td>
                            <td><?= htmlentities($leave->SupervisorRemark) ?></td>
                            <td><?= htmlentities($leave->SupervisorActionDate) ?></td>
                            <td>
                                <a href="leave-details.php?leaveid=<?= $leave->id ?>" class="btn btn-info btn-sm mt-1">View Details</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <!-- Declined Tab -->
            <div class="tab-pane fade" id="declined" role="tabpanel">
                <h5>Declined Leave Requests</h5>
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Employee</th>
                            <th>Leave Type</th>
                            <th>From</th>
                            <th>To</th>
                            <th>Reason</th>
                            <th>Supervisor Remark</th>
                            <th>Supervisor Action Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($leaves as $leave): ?>
                        <?php if ($leave->SupervisorStatus == 'Declined'): ?>
                        <tr>
                            <td><?= htmlentities($leave->FirstName . ' ' . $leave->LastName) ?></td>
                            <td><?= htmlentities($leave->LeaveType) ?></td>
                            <td><?= htmlentities($leave->FromDate) ?></td>
                            <td><?= htmlentities($leave->ToDate) ?></td>
                            <td><?= htmlentities($leave->Description) ?></td>
                            <td><?= htmlentities($leave->SupervisorRemark) ?></td>
                            <td><?= htmlentities($leave->SupervisorActionDate) ?></td>
                            <td>
                                <a href="leave-details.php?leaveid=<?= $leave->id ?>" class="btn btn-info btn-sm mt-1">View Details</a>
                            </td>
                        </tr>
                        <?php endif; ?>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.5.4/dist/umd/popper.min.js"></script>
</body>
</html>
<?php ?>