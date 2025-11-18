<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

include __DIR__ . '/../includes/config.php';

// Require PHPMailer from the same paths you selected (old code)
require __DIR__ . '/../includes/PHPMailer/PHPMailer.php';
require __DIR__ . '/../includes/PHPMailer/SMTP.php';
require __DIR__ . '/../includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// --- Security: Supervisor session check (keep as requested) ---
if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['eid'];
$error = "";
$msg = "";
$result = null;
$leaveid = 0;

// helper: send email using PHPMailer (returns boolean)
function sendMailToEmployee($toEmail, $toName, $subject, $htmlBody, &$debug = null) {
    $debug = "";
    try {
        $mail = new PHPMailer(true);

        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'mrkanyi8@gmail.com';
        $mail->Password   = 'unqchhgsycymtspk';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        $mail->CharSet    = 'UTF-8';
        $mail->Timeout    = 30;

        // relaxed SSL for dev (remove in production if not necessary)
        $mail->SMTPOptions = [
            'ssl' => [
                'verify_peer'       => false,
                'verify_peer_name'  => false,
                'allow_self_signed' => true
            ]
        ];

        // recipients & content
        $mail->setFrom('mrkanyi8@gmail.com', 'ELMS System');
        $mail->addAddress($toEmail, $toName);
        $mail->addReplyTo('mrkanyi8@gmail.com', 'ELMS System');

        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $htmlBody;
        $mail->AltBody = strip_tags(str_replace(["<br>", "<br/>", "<br />"], "\n", $htmlBody));

        if ($mail->send()) {
            $debug = "Mail sent to {$toEmail}";
            return true;
        } else {
            $debug = "PHPMailer failed to send to {$toEmail}: " . $mail->ErrorInfo;
            return false;
        }
    } catch (Exception $e) {
        $debug = "PHPMailer exception for {$toEmail}: " . $e->getMessage();
        return false;
    }
}

// --- Get supervisor's department ---
try {
    $sup_sql = "SELECT Department FROM tblemployees WHERE id = :supid";
    $sup_query = $dbh->prepare($sup_sql);
    $sup_query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
    $sup_query->execute();
    $supervisor = $sup_query->fetch(PDO::FETCH_OBJ);

    if (!$supervisor) {
        $error = "Supervisor information not found.";
    }
} catch (Exception $e) {
    $error = "Failed to load supervisor info.";
}

// --- Load leave details if leaveid provided ---
if (empty($error) && isset($_GET['leaveid'])) {
    $leaveid = intval($_GET['leaveid']);

    $sql = "SELECT tblleaves.*, tblemployees.FirstName, tblemployees.LastName, 
                   tblemployees.EmpId, tblemployees.Phonenumber, tblemployees.EmailId,
                   tblemployees.Department
            FROM tblleaves 
            JOIN tblemployees ON tblleaves.empid = tblemployees.id 
            WHERE tblleaves.id = :leaveid 
              AND tblemployees.Department = :department
            LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':leaveid', $leaveid, PDO::PARAM_INT);
    $query->bindParam(':department', $supervisor->Department, PDO::PARAM_STR);
    $query->execute();
    $result = $query->fetch(PDO::FETCH_OBJ);

    if (!$result) {
        $error = "Leave application not found or you don't have permission to view it.";
    }
} elseif (empty($error) && !isset($_GET['leaveid'])) {
    $error = "No leave application specified.";
}

// --- Handle actions: support both UI variants used in your repo ---
// 1) Buttons named approve/reject with adminremark field
// 2) Modal form using 'update' with fields 'status' and 'description'
if (empty($error) && ($result) && (isset($_POST['approve']) || isset($_POST['reject']) || isset($_POST['update']))) {
    // Determine status and remark
    $status = null;
    $adminremark = "";

    if (isset($_POST['approve']) || isset($_POST['reject'])) {
        // old UI: we expect an 'adminremark' textarea name
        $adminremark = isset($_POST['adminremark']) ? trim($_POST['adminremark']) : '';
        $status = isset($_POST['approve']) ? 1 : 2;
    } elseif (isset($_POST['update'])) {
        // modal UI: status and description fields
        $adminremark = isset($_POST['description']) ? trim($_POST['description']) : '';
        $status = isset($_POST['status']) ? intval($_POST['status']) : null;
    }

    // Validate
    if ($status === null || ($status !== 1 && $status !== 2)) {
        $error = "Invalid action or status.";
    } elseif ($adminremark === '') {
        $error = "Please provide remarks for your decision.";
    } else {
        // Update DB
        try {
            $update_sql = "UPDATE tblleaves 
                           SET Status = :status, AdminRemark = :adminremark, AdminRemarkDate = NOW() 
                           WHERE id = :leaveid";
            $update_query = $dbh->prepare($update_sql);
            $update_query->bindParam(':status', $status, PDO::PARAM_INT);
            $update_query->bindParam(':adminremark', $adminremark, PDO::PARAM_STR);
            $update_query->bindParam(':leaveid', $leaveid, PDO::PARAM_INT);
            $update_query->execute();

            // Refresh result
            $query->execute();
            $result = $query->fetch(PDO::FETCH_OBJ);

            // If approved (status == 1) send email to the employee
            if ($status == 1) {
                $empEmail = $result->EmailId;
                $empName  = trim($result->FirstName . ' ' . $result->LastName);

                if (!empty($empEmail)) {
                    // Build email content
                    $subject = "Your Leave Application Has Been Approved";
                    $htmlBody = "
                        <html>
                        <body style='font-family:Arial, sans-serif; line-height:1.6'>
                            <h2 style='color:#0d6efd'>Leave Approved</h2>
                            <p>Dear " . htmlspecialchars($empName, ENT_QUOTES, 'UTF-8') . ",</p>
                            <p>Your leave request for <strong>" . htmlspecialchars($result->LeaveType, ENT_QUOTES, 'UTF-8') . "</strong>
                            (from <strong>" . htmlspecialchars($result->FromDate, ENT_QUOTES, 'UTF-8') . "</strong> to
                            <strong>" . htmlspecialchars($result->ToDate, ENT_QUOTES, 'UTF-8') . "</strong>) has been <strong>approved</strong>
                            by your supervisor.</p>
                            <p><strong>Remark:</strong> " . nl2br(htmlspecialchars($adminremark, ENT_QUOTES, 'UTF-8')) . "</p>
                            <p>You can view your leave history here:<br>
                            <a href='http://localhost/elms/leavehistory.php'>http://localhost/elms/leavehistory.php</a></p>
                            <p>Regards,<br>ELMS System</p>
                        </body>
                        </html>
                    ";

                    $debugInfo = "";
                    $sent = sendMailToEmployee($empEmail, $empName, $subject, $htmlBody, $debugInfo);

                    // Log debug (file in parent logs folder)
                    $logDir = __DIR__ . '/../logs';
                    if (!is_dir($logDir)) {
                        @mkdir($logDir, 0777, true);
                    }
                    $logFile = $logDir . '/email_debug.log';
                    $logLine = "[" . date('Y-m-d H:i:s') . "] To: {$empEmail}; Sent: " . ($sent ? 'YES' : 'NO') . "; Info: {$debugInfo}\n";
                    @file_put_contents($logFile, $logLine, FILE_APPEND);
                } else {
                    // no employee email found
                    $logDir = __DIR__ . '/../logs';
                    if (!is_dir($logDir)) { @mkdir($logDir, 0777, true); }
                    @file_put_contents(__DIR__ . '/../logs/email_debug.log', "[".date('Y-m-d H:i:s')."] No employee email to notify for leave id {$leaveid}\n", FILE_APPEND);
                }
            }

            // set message then redirect back to list
            $msg = "Leave application " . ($status == 1 ? "approved" : "rejected") . " successfully!";
            // Optional: flash message could be stored in session to show on leaves.php
            $_SESSION['flash_msg'] = $msg;
            header('Location: leaves.php');
            exit();
        } catch (Exception $e) {
            $error = "Failed to update leave application. Please try again.";
            // log exception
            @file_put_contents(__DIR__ . '/../logs/email_debug.log', "[".date('Y-m-d H:i:s')."] Update error for leave {$leaveid}: ".$e->getMessage()."\n", FILE_APPEND);
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Leave Application Details | Employee Leave Management System</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>

    <!-- Keep UI, header and sidebar intact (same CSS/JS as your current codebase) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../assets/css/modern.css">

    <style>
        /* Styles copied from your existing file so UI remains unchanged */
        .btn-enhanced { padding: 0.5rem 1rem; border-radius: .375rem; font-weight:500; }
        .enhanced-card { background:#fff; border-radius:.5rem; box-shadow:0 .125rem .25rem rgba(0,0,0,.075); margin-bottom:1.5rem; border:1px solid rgba(0,0,0,.08); }
        .enhanced-card .card-header { padding:1rem 1.5rem; background:#f8f9fa; border-bottom:1px solid rgba(0,0,0,.08); display:flex; justify-content:space-between; align-items:center; }
        .alert-modern { padding:.75rem 1rem; border-radius:.375rem; margin-bottom:1rem; }
        .alert-error { color:#721c24; background-color:#f8d7da; border-color:#f5c6cb; }
        .alert-success { color:#155724; background-color:#d4edda; border-color:#c3e6cb; }
    </style>
</head>
<body>
    <!-- Supervisor Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">
                <i class="fas fa-user-tie me-2"></i>
                ELMS - Supervisor Portal
            </a>
            <div class="d-flex align-items-center">
                <a href="leaves.php" class="btn-enhanced btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>
            </div>
        </div>
    </nav>

    <div class="container-fluid py-4">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="h3 mb-1">Leave Application Details</h1>
                <p class="text-muted mb-0">Review and take action on this leave request</p>
            </div>
            <div>
                <a href="leaves.php" class="btn-enhanced btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Requests
                </a>
            </div>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert-modern alert-error">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?>
            </div>
            <div class="text-center mt-4">
                <a href="leaves.php" class="btn-enhanced btn-primary">Return to Leave Requests</a>
            </div>
        <?php elseif ($result): ?>

            <?php if (!empty($msg)): ?>
                <div class="alert-modern alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Leave Details (kept identical to your UI) -->
                <div class="col-lg-8">
                    <div class="enhanced-card">
                        <div class="card-header">
                            <h5><i class="fas fa-file-alt"></i> Application Information</h5>
                            <span class="badge-enhanced <?php 
                                echo ($result->Status == 1) ? 'badge-approved' : 
                                     (($result->Status == 2) ? 'badge-rejected' : 'badge-pending'); 
                            ?>">
                                <i class="fas fa-<?php 
                                    echo ($result->Status == 1) ? 'check' : 
                                         (($result->Status == 2) ? 'times' : 'clock'); 
                                ?>"></i>
                                <?php 
                                    echo ($result->Status == 1) ? 'Approved' : 
                                         (($result->Status == 2) ? 'Rejected' : 'Pending'); 
                                ?>
                            </span>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Employee Name</label>
                                        <p class="form-control-static"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Employee ID</label>
                                        <p class="form-control-static"><?php echo htmlentities($result->EmpId); ?></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Department</label>
                                        <p class="form-control-static"><?php echo htmlentities($result->Department); ?></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Leave Type</label>
                                        <p class="form-control-static">
                                            <span class="badge-enhanced bg-info">
                                                <?php echo htmlentities($result->LeaveType); ?>
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">From Date</label>
                                        <p class="form-control-static">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            <?php echo htmlentities($result->FromDate); ?>
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">To Date</label>
                                        <p class="form-control-static">
                                            <i class="fas fa-calendar-alt text-primary me-2"></i>
                                            <?php echo htmlentities($result->ToDate); ?>
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Duration</label>
                                <p class="form-control-static">
                                    <i class="fas fa-clock text-warning me-2"></i>
                                    <?php 
                                    $from = new DateTime($result->FromDate);
                                    $to = new DateTime($result->ToDate);
                                    $duration = $to->diff($from)->days + 1;
                                    echo $duration . ' day(s)';
                                    ?>
                                </p>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-semibold">Description</label>
                                <div class="border rounded p-3 bg-light">
                                    <?php echo htmlentities($result->Description); ?>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">Application Date</label>
                                        <p class="form-control-static"><?php echo htmlentities($result->PostingDate); ?></p>
                                    </div>
                                </div>
                                <?php if($result->Status != 0) { ?>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold">
                                            <?php echo ($result->Status == 1) ? 'Approval' : 'Rejection'; ?> Date
                                        </label>
                                        <p class="form-control-static"><?php echo htmlentities($result->AdminRemarkDate); ?></p>
                                    </div>
                                </div>
                                <?php } ?>
                            </div>

                            <?php if(!empty($result->AdminRemark)) { ?>
                            <div class="mb-3">
                                <label class="form-label fw-semibold">Admin Remarks</label>
                                <div class="border rounded p-3 bg-light">
                                    <?php echo htmlentities($result->AdminRemark); ?>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>

                <!-- Action Panel -->
                <div class="col-lg-4">
                    <!-- Employee Contact -->
                    <div class="enhanced-card mb-4">
                        <div class="card-header">
                            <h6><i class="fas fa-user-circle"></i> Employee Contact</h6>
                        </div>
                        <div class="card-body">
                            <div class="d-flex align-items-center mb-3">
                                <div class="avatar-lg bg-primary rounded-circle d-flex align-items-center justify-content-center me-3">
                                    <span class="text-white fw-bold">
                                        <?php echo substr(htmlentities($result->FirstName), 0, 1); ?>
                                    </span>
                                </div>
                                <div>
                                    <h6 class="mb-1"><?php echo htmlentities($result->FirstName).' '.htmlentities($result->LastName); ?></h6>
                                    <small class="text-muted"><?php echo htmlentities($result->Department); ?></small>
                                </div>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-envelope text-muted me-2"></i>
                                <small><?php echo htmlentities($result->EmailId); ?></small>
                            </div>
                            <div class="mb-2">
                                <i class="fas fa-phone text-muted me-2"></i>
                                <small><?php echo htmlentities($result->Phonenumber) ?: 'Not provided'; ?></small>
                            </div>
                        </div>
                    </div>

                    <!-- Approval Actions -->
                    <?php if($result->Status == 0) { ?>
                    <div class="enhanced-card">
                        <div class="card-header">
                            <h6><i class="fas fa-tasks"></i> Take Action</h6>
                        </div>
                        <div class="card-body">
                            <form method="post">
                                <div class="mb-3">
                                    <label class="form-label fw-semibold">Add Remarks <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="adminremark" rows="3" 
                                              placeholder="Provide your remarks and decision reason..." required></textarea>
                                    <div class="form-text">Your remarks will be visible to the employee.</div>
                                </div>
                                <div class="d-grid gap-2">
                                    <button type="submit" name="approve" class="btn-enhanced btn-success">
                                        <i class="fas fa-check"></i> Approve Leave
                                    </button>
                                    <button type="submit" name="reject" class="btn-enhanced btn-danger">
                                        <i class="fas fa-times"></i> Reject Leave
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                    <?php } else { ?>
                    <div class="enhanced-card">
                        <div class="card-header">
                            <h6><i class="fas fa-info-circle"></i> Application Status</h6>
                        </div>
                        <div class="card-body text-center">
                            <div class="mb-3">
                                <i class="fas fa-<?php echo ($result->Status == 1) ? 'check-circle text-success' : 'times-circle text-danger'; ?> fa-3x"></i>
                            </div>
                            <h5 class="<?php echo ($result->Status == 1) ? 'text-success' : 'text-danger'; ?>">
                                <?php echo ($result->Status == 1) ? 'Approved' : 'Rejected'; ?>
                            </h5>
                            <p class="text-muted small">This application has been processed.</p>
                            <?php if(!empty($result->AdminRemark)) { ?>
                            <div class="mt-3 p-2 bg-light rounded">
                                <small class="text-muted">Remarks: <?php echo htmlentities($result->AdminRemark); ?></small>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                    <?php } ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- Scripts (keep unchanged) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
