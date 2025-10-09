<?php
session_start();
include('includes/config.php'); // admin/ includes/config.php

// PHPMailer (adjust path if your structure differs)
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once __DIR__ . '/../includes/PHPMailer/Exception.php';
require_once __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require_once __DIR__ . '/../includes/PHPMailer/SMTP.php';

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
}

// Helper: send email using PHPMailer
function sendMail($to, $subject, $body)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'mrkanyi8@gmail.com';           // <-- change if needed
        $mail->Password = 'unqchhgsycymtspk';             // <-- change if needed / use app password
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587;

        $mail->setFrom('mrkanyi8@gmail.com', 'Admin Team');
        $mail->addAddress($to);

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body = $body;

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Mail Error: " . $mail->ErrorInfo);
        return false;
    }
}

// GET leave id
$did = isset($_GET['leaveid']) ? intval($_GET['leaveid']) : 0;
if ($did <= 0) {
    // invalid request
    $errorMsg = "Invalid leave id.";
} else {
    // Mark leave as read (safe update)
    try {
        $isread = 1;
        $upd = $dbh->prepare("UPDATE tblleaves SET IsRead = :isread WHERE id = :did");
        $upd->bindParam(':isread', $isread, PDO::PARAM_INT);
        $upd->bindParam(':did', $did, PDO::PARAM_INT);
        $upd->execute();
    } catch (Exception $e) {
        error_log("Mark read error: " . $e->getMessage());
    }

    // Fetch leave + employee details
    $sql = "SELECT tblleaves.*, tblemployees.FirstName, tblemployees.LastName,
                   tblemployees.EmpId, tblemployees.EmailId, tblemployees.Phonenumber,
                   tblemployees.Department
            FROM tblleaves
            JOIN tblemployees ON tblleaves.empid = tblemployees.id
            WHERE tblleaves.id = :did
            LIMIT 1";
    $stmt = $dbh->prepare($sql);
    $stmt->bindParam(':did', $did, PDO::PARAM_INT);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        $errorMsg = "No leave record found.";
    }
}

// Handle POST actions: approve, reject, issue
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($result) && $result) {
    // Approve or Reject
    if (isset($_POST['approve']) || isset($_POST['reject'])) {
        $status = isset($_POST['approve']) ? 1 : 2; // 1 = Approved, 2 = Rejected
        $adminremark = isset($_POST['adminremark']) ? trim($_POST['adminremark']) : '';
        date_default_timezone_set('Asia/Kolkata');
        $admremarkdate = date('Y-m-d H:i:s');

        $updateSql = "UPDATE tblleaves
                      SET AdminRemark = :adminremark,
                          Status = :status,
                          AdminRemarkDate = :admremarkdate
                      WHERE id = :did";
        $u = $dbh->prepare($updateSql);
        $u->bindParam(':adminremark', $adminremark, PDO::PARAM_STR);
        $u->bindParam(':status', $status, PDO::PARAM_INT);
        $u->bindParam(':admremarkdate', $admremarkdate, PDO::PARAM_STR);
        $u->bindParam(':did', $did, PDO::PARAM_INT);
        $u->execute();

        // Email notification to employee
        if (!empty($result->EmailId)) {
            $statusText = ($status == 1) ? "Approved" : "Rejected";
            $subject = "Leave Application {$statusText}";
            $body = "Dear {$result->FirstName},<br><br>
                     Your leave application has been <b>{$statusText}</b> by the Admin.<br>
                     <b>Remark:</b> " . nl2br(htmlentities($adminremark)) . "<br><br>
                     Regards,<br/>Admin Team";
            sendMail($result->EmailId, $subject, $body);
        }

        // Redirect to same page to refresh data & avoid form resubmission
        header("Location: leave-details.php?leaveid={$did}&msg=" . urlencode("Leave {$statusText} successfully"));
        exit();
    }

    // Issue leave (admin can issue regardless of supervisor status)
    if (isset($_POST['issue'])) {
        // Set Issued = 1 (assumes tblleaves.Issued column exists)
        $issueSql = "UPDATE tblleaves SET Issued = 1 WHERE id = :did";
        $q = $dbh->prepare($issueSql);
        $q->bindParam(':did', $did, PDO::PARAM_INT);
        $q->execute();

        // Email to employee informing issued
        if (!empty($result->EmailId)) {
            $subject = "Leave Issued";
            $body = "Dear {$result->FirstName},<br><br>
                     Your leave request has been <b>ISSUED</b> by the Admin.<br><br>
                     Regards,<br/>Admin Team";
            sendMail($result->EmailId, $subject, $body);
        }

        header("Location: leave-details.php?leaveid={$did}&msg=" . urlencode("Leave issued successfully"));
        exit();
    }
}

// Get optional message from redirect
$msg = isset($_GET['msg']) ? trim($_GET['msg']) : '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <title>Leave Details | Admin</title>
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet"/>
    <link href="../assets/css/modern.css" rel="stylesheet"/>
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
            <a href="leaves.php" class="btn btn-secondary btn-sm"><i class="fas fa-arrow-left"></i> Back</a>
        </div>

        <?php if (!empty($msg)) { ?>
            <div class="alert alert-success"><?php echo htmlentities($msg); ?></div>
        <?php } ?>

        <?php if (isset($errorMsg)) { ?>
            <div class="alert alert-danger"><?php echo htmlentities($errorMsg); ?></div>
        <?php } elseif (isset($result) && $result) { 
            // normalize status and issued
            $status = intval($result->Status); // 0 / 1 / 2 (pending->0)
            $isPending = ($status === 0);
            $issued = intval($result->Issued); // assume 0/1
        ?>
        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow-sm mb-4">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0"><i class="fas fa-file-alt me-2"></i> Application Information</h5>
                        <div>
                            <span class="badge <?php
                                if ($status === 1) echo 'bg-success';
                                elseif ($status === 2) echo 'bg-danger';
                                else echo 'bg-warning text-dark';
                            ?>">
                                <?php
                                    if ($status === 1) echo 'Approved';
                                    elseif ($status === 2) echo 'Rejected';
                                    else echo 'Pending';
                                ?>
                            </span>
                            &nbsp;
                            <span class="badge <?php echo ($issued === 1) ? 'bg-info' : 'bg-secondary'; ?>">
                                <?php echo ($issued === 1) ? 'Issued' : 'Not Issued'; ?>
                            </span>
                        </div>
                    </div>
                    <div class="card-body">
                        <div class="row mb-2">
                            <div class="col-md-6"><b>Employee:</b> <?php echo htmlentities($result->FirstName . ' ' . $result->LastName); ?></div>
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
                        <div class="mb-2"><b>Description:</b><br/><?php echo nl2br(htmlentities($result->Description)); ?></div>
                        <div class="mb-2"><b>Applied On:</b> <?php echo htmlentities($result->PostingDate); ?></div>
                        <?php if (!empty($result->AdminRemark)) { ?>
                            <div class="mb-2"><b>Admin Remark:</b> <?php echo nl2br(htmlentities($result->AdminRemark)); ?></div>
                            <div class="mb-2"><b>Admin Action Date:</b> <?php echo htmlentities($result->AdminRemarkDate); ?></div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <!-- Actions column -->
            <div class="col-lg-4">
                <!-- Contact -->
                <div class="card shadow-sm mb-3">
                    <div class="card-header"><b>Employee Contact</b></div>
                    <div class="card-body">
                        <p class="mb-1"><strong><?php echo htmlentities($result->FirstName . ' ' . $result->LastName); ?></strong></p>
                        <p class="mb-1"><i class="fas fa-envelope me-2"></i><?php echo htmlentities($result->EmailId); ?></p>
                        <p class="mb-0"><i class="fas fa-phone me-2"></i><?php echo htmlentities($result->Phonenumber); ?></p>
                    </div>
                </div>

                <!-- Approve/Reject UI (only if pending) -->
                <?php if ($isPending) { ?>
                <div class="card shadow-sm mb-3">
                    <div class="card-header"><b>Take Action</b></div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label for="adminremark" class="form-label">Add Remark</label>
                                <textarea name="adminremark" id="adminremark" class="form-control" rows="3" required></textarea>
                            </div>
                            <div class="d-grid gap-2">
                                <button type="submit" name="approve" class="btn btn-success"><i class="fas fa-check"></i> Approve</button>
                                <button type="submit" name="reject" class="btn btn-danger"><i class="fas fa-times"></i> Reject</button>
                            </div>
                        </form>
                    </div>
                </div>
                <?php } ?>

                <!-- Issue Leave (admin may issue irrespective of supervisor status) -->
                <div class="card shadow-sm">
                    <div class="card-header"><b>Issue Leave</b></div>
                    <div class="card-body">
                        <?php if ($issued === 1) { ?>
                            <div class="alert alert-info mb-2 text-center">
                                <i class="fas fa-check-circle"></i> This leave has already been issued.
                            </div>
                        <?php } else { ?>
                            <form method="post" onsubmit="return confirm('Issue this leave to the employee? This action cannot be undone.');">
                                <div class="mb-3">
                                    <small class="text-muted">You can issue this leave regardless of supervisor approval.</small>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="issue" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Issue Leave</button>
                                </div>
                            </form>
                        <?php } ?>
                    </div>
                </div>

                <!-- Quick status box -->
                <div class="card shadow-sm mt-3 text-center">
                    <div class="card-body">
                        <p class="mb-1"><strong>Status</strong></p>
                        <p class="mb-1"><?php
                            if ($status === 1) echo '<span class="text-success">Approved</span>';
                            elseif ($status === 2) echo '<span class="text-danger">Rejected</span>';
                            else echo '<span class="text-warning">Pending</span>';
                        ?></p>
                        <p class="mb-0"><?php echo ($issued === 1) ? '<span class="text-info">Issued</span>' : '<span class="text-muted">Not Issued</span>'; ?></p>
                    </div>
                </div>
            </div>
        </div>
        <?php } ?>
    </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
