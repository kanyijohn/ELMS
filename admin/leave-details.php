<?php
session_start();
include('includes/config.php');

// ✅ PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require '../includes/PHPMailer/Exception.php';
require '../includes/PHPMailer/PHPMailer.php';
require '../includes/PHPMailer/SMTP.php';

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {

    // ✅ Function to send email directly
    function sendMail($to, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host = 'smtp.gmail.com';
            $mail->SMTPAuth = true;
            $mail->Username = 'mrkanyi8@gmail.com'; // Gmail
            $mail->Password = 'unqchhgsycymtspk';   // App password
            $mail->SMTPSecure = 'tls';
            $mail->Port = 587;
            $mail->setFrom('mrkanyi8@gmail.com', 'Admin Team');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;
            $mail->send();
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }

    $did = intval($_GET['leaveid']);

    // ✅ Mark leave as read
    $isread = 1;
    $update = $dbh->prepare("UPDATE tblleaves SET IsRead=:isread WHERE id=:did");
    $update->bindParam(':isread', $isread, PDO::PARAM_INT);
    $update->bindParam(':did', $did, PDO::PARAM_INT);
    $update->execute();

    // ✅ Fetch leave record joined with employee data
    $sql = "SELECT tblleaves.*, tblemployees.FirstName, tblemployees.LastName, 
                   tblemployees.EmpId, tblemployees.EmailId, tblemployees.Phonenumber, 
                   tblemployees.Department 
            FROM tblleaves 
            JOIN tblemployees ON tblleaves.empid = tblemployees.id 
            WHERE tblleaves.id = :did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':did', $did, PDO::PARAM_INT);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        $errorMsg = "No leave record found.";
    }

    // ✅ Approve or reject leave
    if (isset($_POST['approve']) || isset($_POST['reject'])) {
        $status = isset($_POST['approve']) ? 1 : 2;
        $remark = $_POST['adminremark'] ?? '';
        $admremarkdate = date('Y-m-d H:i:s');

        $update = $dbh->prepare("UPDATE tblleaves 
                                 SET AdminRemark=:remark, Status=:status, AdminRemarkDate=:date 
                                 WHERE id=:did");
        $update->bindParam(':remark', $remark, PDO::PARAM_STR);
        $update->bindParam(':status', $status, PDO::PARAM_INT);
        $update->bindParam(':date', $admremarkdate, PDO::PARAM_STR);
        $update->bindParam(':did', $did, PDO::PARAM_INT);
        $update->execute();

        // ✅ Send email notification
        if ($result) {
            $statusText = ($status == 1) ? "Approved" : "Rejected";
            $subject = "Leave Application {$statusText}";
            $body = "Dear {$result->FirstName},<br><br>
                     Your leave application has been <b>{$statusText}</b> by Admin.<br>
                     <b>Remark:</b> {$remark}<br><br>
                     Regards,<br>Admin Team";
            sendMail($result->EmailId, $subject, $body);
        }

        $msg = "Leave has been successfully {$statusText}.";
        header("Refresh:0");
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Details | Admin</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- ✅ Bootstrap & Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link href="../assets/css/modern.css" rel="stylesheet">
</head>
<body>
<div class="dashboard-container d-flex">
    <!-- Sidebar -->
    <?php include('includes/sidebar.php'); ?>

    <!-- Main Content -->
    <div class="main-content flex-grow-1 p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h4 mb-0">Leave Application Details</h1>
                <small class="text-muted">Review and take action on the leave request</small>
            </div>
            <a href="leaves.php" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back
            </a>
        </div>

        <?php if (isset($errorMsg)) { ?>
            <div class="alert alert-danger text-center"><?php echo $errorMsg; ?></div>
        <?php } elseif ($result) { ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm">
                    <div class="card-header bg-light d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i> Application Information</h5>
                        <span class="badge bg-<?php 
                            echo ($result->Status == 1) ? 'success' : 
                                 (($result->Status == 2) ? 'danger' : 'warning'); ?>">
                            <?php 
                                echo ($result->Status == 1) ? 'Approved' : 
                                     (($result->Status == 2) ? 'Rejected' : 'Pending'); 
                            ?>
                        </span>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6"><b>Employee:</b> <?php echo htmlentities($result->FirstName.' '.$result->LastName); ?></div>
                            <div class="col-md-6"><b>Employee ID:</b> <?php echo htmlentities($result->EmpId); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><b>Department:</b> <?php echo htmlentities($result->Department); ?></div>
                            <div class="col-md-6"><b>Leave Type:</b> <?php echo htmlentities($result->LeaveType); ?></div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-6"><b>From:</b> <?php echo htmlentities($result->FromDate); ?></div>
                            <div class="col-md-6"><b>To:</b> <?php echo htmlentities($result->ToDate); ?></div>
                        </div>
                        <div class="mb-2"><b>Description:</b> <?php echo htmlentities($result->Description); ?></div>
                        <div class="mb-2"><b>Applied On:</b> <?php echo htmlentities($result->PostingDate); ?></div>
                        <?php if (!empty($result->AdminRemark)) { ?>
                            <div class="mb-2"><b>Admin Remark:</b> <?php echo htmlentities($result->AdminRemark); ?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <?php if ($result->Status == 0) { ?>
                    <div class="card shadow-sm">
                        <div class="card-header bg-light"><b>Take Action</b></div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label for="adminremark" class="form-label">Add Remark</label>
                                    <textarea name="adminremark" id="adminremark" class="form-control" rows="3" required></textarea>
                                </div>
                                <button type="submit" name="approve" class="btn btn-success w-100 mb-2">
                                    <i class="fas fa-check"></i> Approve
                                </button>
                                <button type="submit" name="reject" class="btn btn-danger w-100">
                                    <i class="fas fa-times"></i> Reject
                                </button>
                            </form>
                        </div>
                    </div>
                <?php } else { ?>
                    <div class="alert alert-<?php echo ($result->Status == 1) ? 'success' : 'danger'; ?> text-center">
                        <i class="fas fa-<?php echo ($result->Status == 1) ? 'check-circle' : 'times-circle'; ?> fa-2x mb-2"></i><br>
                        This leave has been <?php echo ($result->Status == 1) ? 'Approved' : 'Rejected'; ?>.
                    </div>
                <?php } ?>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!-- ✅ Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="../assets/js/modern.js"></script>
</body>
</html>
<?php } ?>
