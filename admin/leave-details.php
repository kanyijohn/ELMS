<?php 
session_start();
error_reporting(0);
include '../includes/config.php';

// ✅ Direct PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {

    // function to send email
    function sendMail($to, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            // Server settings
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mrkanyi8@gmail.com'; // ✅ Gmail
            $mail->Password = 'unqchhgsycymtspk'; // ✅ Gmail App Password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;

            // Recipients
            $mail->setFrom('mrkanyi8@gmail.com', 'Admin Team');
            $mail->addAddress($to);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $body;

            $mail->send();
        } catch (Exception $e) {
            error_log("Mail Error: " . $mail->ErrorInfo);
        }
    }

    // Mark leave as read
    $isread = 1;
    $did = intval($_GET['leaveid']);
    $sql = "UPDATE tblleaves SET IsRead=:isread WHERE id=:did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':isread', $isread, PDO::PARAM_INT);
    $query->bindParam(':did', $did, PDO::PARAM_INT);
    $query->execute();

    // Admin approval/rejection
    if (isset($_POST['update'])) {
        $did = intval($_GET['leaveid']);
        $description = $_POST['description'];
        $status = $_POST['status'];
        date_default_timezone_set('Asia/Kolkata');
        $admremarkdate = date('Y-m-d H:i:s');

        $sql = "UPDATE tblleaves 
                SET AdminRemark=:description,
                    Status=:status,
                    AdminRemarkDate=:admremarkdate 
                WHERE id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->bindParam(':admremarkdate', $admremarkdate, PDO::PARAM_STR);
        $query->bindParam(':did', $did, PDO::PARAM_INT);
        $query->execute();

        // ✅ Send email
        $empSql = "SELECT EmailId, FirstName FROM tblemployees 
                   WHERE id=(SELECT empid FROM tblleaves WHERE id=:did)";
        $empQ = $dbh->prepare($empSql);
        $empQ->bindParam(':did', $did, PDO::PARAM_INT);
        $empQ->execute();
        $emp = $empQ->fetch(PDO::FETCH_OBJ);

        if ($emp) {
            $statusText = ($status == 1) ? "Approved" : "Rejected";
            $subject = "Leave Application Update";
            $body = "Dear {$emp->FirstName},<br><br>
                     Your leave request has been <b>{$statusText}</b> by Admin.<br>
                     Remark: {$description}<br><br>
                     Regards,<br>Admin Team";
            sendMail($emp->EmailId, $subject, $body);
        }

        $msg = "Leave status updated successfully";
    }

    // Admin issue leave
    if (isset($_POST['issue'])) {
        $did = intval($_GET['leaveid']);
        $sql = "UPDATE tblleaves SET Issued=1 WHERE id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':did', $did, PDO::PARAM_INT);
        $query->execute();

        // ✅ Send email
        $empSql = "SELECT EmailId, FirstName FROM tblemployees 
                   WHERE id=(SELECT empid FROM tblleaves WHERE id=:did)";
        $empQ = $dbh->prepare($empSql);
        $empQ->bindParam(':did', $did, PDO::PARAM_INT);
        $empQ->execute();
        $emp = $empQ->fetch(PDO::FETCH_OBJ);

        if ($emp) {
            $subject = "Leave Issued";
            $body = "Dear {$emp->FirstName},<br><br>
                     Your leave request has been <b>ISSUED</b> by Admin.<br><br>
                     Regards,<br>Admin Team";
            sendMail($emp->EmailId, $subject, $body);
        }

        $msg = "Leave issued successfully";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- ✅ Prevent Quirks -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>Admin | Leave Details</title>

    <!-- ✅ Materialize CSS -->
    <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
</head>
<body>
<?php include '../includes/header.php';?>
<?php include '../includes/sidebar.php';?>

<main class="mn-inner">
    <div class="row">
        <div class="col s12">
            <div class="page-title">Leave Details</div>
        </div>

        <div class="col s12 m12 l12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Leave Details</span>
                    <?php if (!empty($msg)) { ?>
                        <div class="card-panel green lighten-4"><strong>SUCCESS</strong>: <?php echo htmlentities($msg); ?></div>
                    <?php } ?>

                    <table class="highlight responsive-table">
                        <tbody>
                        <?php
                        $lid = intval($_GET['leaveid']);
                        $sql = "SELECT tblleaves.*, 
                                       tblemployees.FirstName, tblemployees.LastName, 
                                       tblemployees.EmpId, tblemployees.Gender,
                                       tblemployees.Phonenumber, tblemployees.EmailId 
                                FROM tblleaves 
                                JOIN tblemployees ON tblleaves.empid=tblemployees.id 
                                WHERE tblleaves.id=:lid";
                        $query = $dbh->prepare($sql);
                        $query->bindParam(':lid', $lid, PDO::PARAM_INT);
                        $query->execute();
                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                        if ($query->rowCount() > 0) {
                            foreach ($results as $result) {
                        ?>
                        <tr>
                            <td><b>Employee Name:</b></td>
                            <td><?php echo htmlentities($result->FirstName . " " . $result->LastName); ?></td>
                            <td><b>Emp ID:</b></td>
                            <td><?php echo htmlentities($result->EmpId); ?></td>
                        </tr>
                        <tr>
                            <td><b>Email:</b></td>
                            <td><?php echo htmlentities($result->EmailId); ?></td>
                            <td><b>Contact:</b></td>
                            <td><?php echo htmlentities($result->Phonenumber); ?></td>
                        </tr>
                        <tr>
                            <td><b>Leave Type:</b></td>
                            <td><?php echo htmlentities($result->LeaveType); ?></td>
                            <td><b>Leave Dates:</b></td>
                            <td>From <?php echo htmlentities($result->FromDate); ?> to <?php echo htmlentities($result->ToDate); ?></td>
                        </tr>
                        <tr>
                            <td><b>Description:</b></td>
                            <td colspan="3"><?php echo htmlentities($result->Description); ?></td>
                        </tr>
                        <tr>
                            <td><b>Supervisor Status:</b></td>
                            <td colspan="3">
                                <?php if ($result->Status == 1) echo '<span class="green-text">Approved</span>';
                                elseif ($result->Status == 2) echo '<span class="red-text">Rejected</span>';
                                else echo '<span class="blue-text">Pending</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Admin Remark:</b></td>
                            <td colspan="3"><?php echo $result->AdminRemark ? htmlentities($result->AdminRemark) : "None"; ?></td>
                        </tr>
                        <tr>
                            <td><b>Admin Action Date:</b></td>
                            <td colspan="3"><?php echo $result->AdminRemarkDate ? htmlentities($result->AdminRemarkDate) : "NA"; ?></td>
                        </tr>
                        <tr>
                            <td><b>Issued:</b></td>
                            <td colspan="3"><?php echo $result->Issued ? '<span class="green-text">Yes</span>' : '<span class="red-text">No</span>'; ?></td>
                        </tr>

                        <?php if ($result->Status == 0) { ?>
                        <tr>
                            <td colspan="4">
                                <a class="modal-trigger btn blue" href="#actionModal">Take Action</a>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php if ($result->Issued == 0) { ?>
                        <tr>
                            <td colspan="4">
                                <form method="post">
                                    <button type="submit" name="issue" class="btn green">Issue Leave</button>
                                </form>
                            </td>
                        </tr>
                        <?php } ?>

                        <?php } } ?>
                        </tbody>
                    </table>

                    <!-- ✅ Modal moved outside table -->
                    <form method="post">
                        <div id="actionModal" class="modal">
                            <div class="modal-content">
                                <h4>Admin Action</h4>
                                <div class="input-field">
                                    <select name="status" required>
                                        <option value="">Choose</option>
                                        <option value="1">Approve</option>
                                        <option value="2">Reject</option>
                                    </select>
                                </div>
                                <div class="input-field">
                                    <textarea name="description" class="materialize-textarea" placeholder="Remark" required></textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <input type="submit" name="update" class="modal-close btn blue" value="Submit">
                            </div>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</main>
<!-- JS Scripts -->
<script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
<script src="../assets/plugins/materialize/js/materialize.min.js"></script>
<script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
<script src="../assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="../assets/js/alpha.min.js"></script>
<script src="../assets/js/pages/table-data.js"></script>
<script>
    $(document).ready(function(){
        $('.modal').modal();
        $('select').formSelect();
    });
</script>
</body>
</html>
<?php } ?>
