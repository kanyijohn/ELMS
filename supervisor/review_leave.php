<?php
session_start();
include('../includes/config.php');
if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    $remark = $action == 'Approve' ? 'Approved by Supervisor' : 'Declined by Supervisor';
    $status = $action == 'Approve' ? 'Approved' : 'Declined';
    $date = date('Y-m-d H:i:s');
    // Update leave status
    $sql = "UPDATE tblleaves SET SupervisorStatus=:status, SupervisorRemark=:remark, SupervisorActionDate=:date WHERE id=:leave_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->bindParam(':remark', $remark, PDO::PARAM_STR);
    $query->bindParam(':date', $date, PDO::PARAM_STR);
    $query->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
    $query->execute();
    // TODO: Send email notifications here
    header('location:dashboard.php');
    exit();
}
?>