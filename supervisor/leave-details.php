<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);

include '../includes/config.php';

// ✅ Include PHPMailer classes
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';
require '../includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// ✅ Reusable email sender function
function sendMail($to, $subject, $body)
{
    $mail = new PHPMailer(true);
    try {
        //Server settings
        $mail->isSMTP();
        $mail->Host       = "smtp.gmail.com";
        $mail->SMTPAuth   = true;
        $mail->Username   = "mrkanyi8@gmail.com"; // ✅ Replace with your Gmail
        $mail->Password   = "unqchhgsycymtspk"; // ✅ Use Gmail App Password (NOT normal password)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        //Recipients
        $mail->setFrom("mrkanyi8@gmail.com", "ELMS Supervisor");
        $mail->addAddress($to);

        //Content
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail error: {$mail->ErrorInfo}");
        return false;
    }
}

// ✅ Require supervisor login
if (strlen($_SESSION['emplogin']) == 0) {
    header('location: ../index.php');
    exit();
} else {
    // ✅ Check if logged-in user is a Supervisor
    $email = $_SESSION['emplogin'];
    $sql = "SELECT Role FROM tblemployees WHERE EmailId = :email LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_OBJ);

    if (!$user || strtolower($user->Role) !== 'supervisor') {
        header('location: ../index.php');
        exit();
    }

    // ✅ Mark leave as read
    $did = intval($_GET['leaveid']);
    if ($did > 0) {
        $isread = 1;
        $sql = "UPDATE tblleaves SET IsRead=:isread WHERE id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':isread', $isread, PDO::PARAM_INT);
        $query->bindParam(':did', $did, PDO::PARAM_INT);
        $query->execute();
    }

    // ✅ Supervisor action (Approve / Reject)
    if (isset($_POST['update'])) {
        $description = $_POST['description'];
        $status = $_POST['status'];
        date_default_timezone_set('Africa/Nairobi');
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

        // ✅ Send email to employee
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
                     Your leave request has been <b>{$statusText}</b> by Supervisor.<br>
                     Remark: {$description}<br><br>
                     Regards,<br>Supervisor Team";
            sendMail($emp->EmailId, $subject, $body);
        }

        $msg = "Leave updated successfully";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Supervisor | Leave Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!-- Styles -->
    <link rel="stylesheet" href="/elms/assets/plugins/materialize/css/materialize.min.css"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/elms/assets/plugins/material-preloader/css/materialPreloader.min.css"/>
    <link rel="stylesheet" href="/elms/assets/plugins/datatables/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="/elms/assets/css/alpha.min.css"/>
    <link rel="stylesheet" href="/elms/assets/css/custom.css"/>
    <link rel="stylesheet" href="/elms/assets/css/style.css"/>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    <?php include '../includes/sidebar.php'; ?>

    <main class="mn-inner">
        <div class="row">
            <div class="col s12">
                <div class="page-title" style="font-size:24px;">Leave Details</div>
            </div>
            <div class="col s12 m12 l12">
                <div class="card">
                    <div class="card-content">
                        <span class="card-title">Leave Details</span>
                        <?php if (!empty($msg)) { ?>
                            <div class="succWrap"><strong>SUCCESS</strong> : <?php echo htmlentities($msg); ?> </div>
                        <?php } ?>

                        <table class="display responsive-table">
                            <tbody>
                            <?php
                            $sql = "SELECT tblleaves.id as lid,
                                           tblemployees.FirstName, tblemployees.LastName,
                                           tblemployees.EmpId, tblemployees.Gender,
                                           tblemployees.Phonenumber, tblemployees.EmailId,
                                           tblleaves.LeaveType, tblleaves.ToDate, tblleaves.FromDate,
                                           tblleaves.Description, tblleaves.PostingDate,
                                           tblleaves.Status, tblleaves.AdminRemark, tblleaves.AdminRemarkDate
                                    FROM tblleaves
                                    JOIN tblemployees ON tblleaves.empid=tblemployees.id
                                    WHERE tblleaves.id=:lid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':lid', $did, PDO::PARAM_INT);
                            $query->execute();
                            $results = $query->fetchAll(PDO::FETCH_OBJ);

                            if ($query->rowCount() > 0) {
                                foreach ($results as $result) {
                                    $stats = $result->Status;
                            ?>
                                <tr>
                                    <td><b>Employee Name :</b></td>
                                    <td><?php echo htmlentities($result->FirstName . " " . $result->LastName); ?></td>
                                    <td><b>Emp Id :</b></td>
                                    <td><?php echo htmlentities($result->EmpId); ?></td>
                                    <td><b>Gender :</b></td>
                                    <td><?php echo htmlentities($result->Gender); ?></td>
                                </tr>
                                <tr>
                                    <td><b>Email :</b></td>
                                    <td><?php echo htmlentities($result->EmailId); ?></td>
                                    <td><b>Contact :</b></td>
                                    <td><?php echo htmlentities($result->Phonenumber); ?></td>
                                </tr>
                                <tr>
                                    <td><b>Leave Type :</b></td>
                                    <td><?php echo htmlentities($result->LeaveType); ?></td>
                                    <td><b>Leave Date :</b></td>
                                    <td>From <?php echo htmlentities($result->FromDate); ?> to <?php echo htmlentities($result->ToDate); ?></td>
                                    <td><b>Posting Date</b></td>
                                    <td><?php echo htmlentities($result->PostingDate); ?></td>
                                </tr>
                                <tr>
                                    <td><b>Description :</b></td>
                                    <td colspan="5"><?php echo htmlentities($result->Description); ?></td>
                                </tr>
                                <tr>
                                    <td><b>Status :</b></td>
                                    <td colspan="5">
                                        <?php if ($stats == 1) { ?>
                                            <span style="color: green">Approved</span>
                                        <?php } elseif ($stats == 2) { ?>
                                            <span style="color: red">Rejected</span>
                                        <?php } else { ?>
                                            <span style="color: blue">Waiting for Supervisor approval</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Supervisor Remark:</b></td>
                                    <td colspan="5"><?php echo $result->AdminRemark ? htmlentities($result->AdminRemark) : "None"; ?></td>
                                </tr>
                                <tr>
                                    <td><b>Supervisor Action Date:</b></td>
                                    <td colspan="5"><?php echo $result->AdminRemarkDate ? htmlentities($result->AdminRemarkDate) : "NA"; ?></td>
                                </tr>

                                <?php if ($stats == 0) { ?>
                                <tr>
                                    <td colspan="6">
                                        <a class="modal-trigger btn" href="#actionModal">Take Action</a>
                                        <form method="post">
                                            <div id="actionModal" class="modal">
                                                <div class="modal-content">
                                                    <h4>Supervisor Action</h4>
                                                    <select class="browser-default" name="status" required>
                                                        <option value="">Choose</option>
                                                        <option value="1">Approve</option>
                                                        <option value="2">Reject</option>
                                                    </select>
                                                    <textarea name="description" class="materialize-textarea" placeholder="Remark" required></textarea>
                                                </div>
                                                <div class="modal-footer">
                                                    <input type="submit" name="update" class="btn blue" value="Submit">
                                                </div>
                                            </div>
                                        </form>
                                    </td>
                                </tr>
                                <?php } ?>
                            <?php } } ?>
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="left-sidebar-hover"></div>

    <!-- Scripts -->
    <script src="/elms/assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="/elms/assets/plugins/materialize/js/materialize.min.js"></script>
    <script src="/elms/assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="/elms/assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <script src="/elms/assets/js/alpha.min.js"></script>
    <script src="/elms/assets/js/pages/table-data.js"></script>
    <script>
        $(document).ready(function(){
            $('.modal').modal();
            $('select').formSelect();
        });
    </script>
</body>
</html>
<?php } ?>
