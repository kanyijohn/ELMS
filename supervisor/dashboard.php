<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}
$supervisor_id = $_SESSION['eid'];
// Fetch pending leave requests assigned to this supervisor
$sql = "SELECT l.*, e.FirstName, e.LastName FROM tblleaves l
        JOIN tblemployees e ON l.empid = e.id
        WHERE e.supervisor_id = :supid AND l.SupervisorStatus = 'Pending'";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$leaves = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supervisor Dashboard</title>
</head>
<body>
    <h2>Pending Leave Requests</h2>
    <table border="1">
        <tr>
            <th>Employee</th>
            <th>Leave Type</th>
            <th>From</th>
            <th>To</th>
            <th>Reason</th>
            <th>Action</th>
        </tr>
        <?php foreach ($leaves as $leave): ?>
        <tr>
            <td><?= htmlspecialchars($leave->FirstName . ' ' . $leave->LastName) ?></td>
            <td><?= htmlspecialchars($leave->LeaveType) ?></td>
            <td><?= htmlspecialchars($leave->FromDate) ?></td>
            <td><?= htmlspecialchars($leave->ToDate) ?></td>
            <td><?= htmlspecialchars($leave->Description) ?></td>
            <td>
                <form method="post" action="review_leave.php">
                    <input type="hidden" name="leave_id" value="<?= $leave->id ?>">
                    <input type="submit" name="action" value="Approve">
                    <input type="submit" name="action" value="Decline">
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>