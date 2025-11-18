<?php
session_start();
include('includes/config.php');

if(!isset($_SESSION['eid']) || !isset($_SESSION['empemail'])) { 
    header('location:index.php');
    exit();
}

$msg = "";
$error = "";

// Include PHPMailer classes
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
require 'includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Email Debug Log File
$debugLog = __DIR__ . '/logs/email_debug.log';
if (!file_exists(dirname($debugLog))) {
    mkdir(dirname($debugLog), 0777, true);
}

function log_email_debug($message) {
    global $debugLog;
    file_put_contents($debugLog, "[" . date('Y-m-d H:i:s') . "] " . $message . "\n", FILE_APPEND);
}

if (isset($_POST['apply'])) {
    $empid = $_SESSION['eid'];
    $leave_type = trim($_POST['leavetype']);
    $fromdate = trim($_POST['fromdate']);
    $todate = trim($_POST['todate']);
    $description = trim($_POST['description']);

    // Input validation
    if (empty($leave_type) || empty($fromdate) || empty($todate) || empty($description)) {
        $error = "⚠️ All fields are required.";
    } elseif (strtotime($fromdate) > strtotime($todate)) {
        $error = "⚠️ 'From Date' cannot be later than 'To Date'.";
    } else {
        try {
            // Check for overlapping leave dates using PDO (assuming you're using PDO based on your config)
            $checkOverlap = $dbh->prepare("SELECT * FROM tblleaves WHERE empid = :empid 
                AND ((FromDate BETWEEN :fromdate AND :todate) 
                OR (ToDate BETWEEN :fromdate AND :todate))");
            $checkOverlap->bindParam(':empid', $empid, PDO::PARAM_INT);
            $checkOverlap->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
            $checkOverlap->bindParam(':todate', $todate, PDO::PARAM_STR);
            $checkOverlap->execute();
            
            if ($checkOverlap->rowCount() > 0) {
                $error = "⚠️ You already have a leave within the selected date range.";
            } else {
                // Insert leave using PDO
                $query = "INSERT INTO tblleaves(LeaveType, FromDate, ToDate, Description, PostingDate, empid, Status) 
                          VALUES(:leavetype, :fromdate, :todate, :description, NOW(), :empid, 0)";
                $stmt = $dbh->prepare($query);
                $stmt->bindParam(':leavetype', $leave_type, PDO::PARAM_STR);
                $stmt->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
                $stmt->bindParam(':todate', $todate, PDO::PARAM_STR);
                $stmt->bindParam(':description', $description, PDO::PARAM_STR);
                $stmt->bindParam(':empid', $empid, PDO::PARAM_INT);
                
                if ($stmt->execute()) {
                    $msg = "✅ Leave applied successfully and awaiting approval.";

                    // Fetch supervisor email from DB using PDO
                    $supervisorQuery = "SELECT EmailId FROM tblemployees WHERE role='Supervisor' OR role='Admin' LIMIT 1";
                    $supervisorStmt = $dbh->prepare($supervisorQuery);
                    $supervisorStmt->execute();
                    $supervisor = $supervisorStmt->fetch(PDO::FETCH_ASSOC);
                    $supervisorEmail = $supervisor ? $supervisor['EmailId'] : null;

                    if ($supervisorEmail) {
                        try {
                            $mail = new PHPMailer(true);
                            $mail->isSMTP();
                            $mail->Host = 'smtp.gmail.com';
                            $mail->SMTPAuth = true;
                            $mail->Username = 'mrkanyi8@gmail.com';
                            $mail->Password = 'unqchhgsycymtspk';
                            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                            $mail->Port = 587;
                            $mail->Timeout = 30;

                            $mail->setFrom('mrkanyi8@gmail.com', 'ELMS System');
                            $mail->addAddress($supervisorEmail);

                            $mail->isHTML(true);
                            $mail->Subject = "New Leave Application Submitted";
                            $mail->Body = "
                                <html>
                                <head>
                                    <style>
                                        body { font-family: Arial, sans-serif; }
                                        .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                                        .header { background: #667eea; color: white; padding: 20px; text-align: center; }
                                        .content { padding: 20px; background: #f9f9f9; }
                                        .details { background: white; padding: 15px; border-radius: 5px; margin: 10px 0; }
                                        .button { display: inline-block; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 5px; }
                                    </style>
                                </head>
                                <body>
                                    <div class='container'>
                                        <div class='header'><h2>Leave Application Notification</h2></div>
                                        <div class='content'>
                                            <p>An employee has submitted a new leave request.</p>
                                            <div class='details'>
                                                <p><strong>Leave Type:</strong> {$leave_type}</p>
                                                <p><strong>From Date:</strong> {$fromdate}</p>
                                                <p><strong>To Date:</strong> {$todate}</p>
                                                <p><strong>Description:</strong> {$description}</p>
                                            </div>
                                            <p style='text-align: center;'>
                                                <a href='http://localhost/ELMS/leaves.php' class='button'>
                                                    Review Leave Request
                                                </a>
                                            </p>
                                        </div>
                                    </div>
                                </body>
                                </html>
                            ";

                            // Plain text version
                            $mail->AltBody = "New Leave Application:\n\nLeave Type: {$leave_type}\nFrom: {$fromdate}\nTo: {$todate}\nDescription: {$description}\n\nReview at: http://localhost/ELMS/leaves.php";

                            if ($mail->send()) {
                                $msg .= " Supervisor has been notified.";
                                log_email_debug("Email sent successfully to Supervisor: {$supervisorEmail}");
                            } else {
                                log_email_debug("Email sending failed: {$mail->ErrorInfo}");
                            }
                        } catch (Exception $e) {
                            log_email_debug("Mailer Exception: " . $e->getMessage());
                        }
                    } else {
                        log_email_debug("No Supervisor email found in database.");
                    }
                } else {
                    $error = "❌ Failed to apply for leave. Please try again.";
                    log_email_debug("Database insert error");
                }
            }
        } catch (PDOException $e) {
            $error = "❌ Database error. Please try again.";
            log_email_debug("PDO Error: " . $e->getMessage());
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Apply Leave | Employee Leave Management System</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- Using CDN for Bootstrap to avoid 404 errors -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        /* Main layout styles */
        .main-container {
            display: flex;
            min-height: 100vh;
        }
        .sidebar-container {
            width: 250px;
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            z-index: 1000;
            background: #f8f9fa;
            border-right: 1px solid #dee2e6;
            overflow-y: auto;
        }
        .content-container {
            flex: 1;
            margin-left: 250px;
            min-height: 100vh;
            background: #ffffff;
        }
        .content-area {
            padding: 20px;
            min-height: 100%;
        }
        /* Header styles */
        .header-container {
            position: fixed;
            top: 0;
            left: 250px;
            right: 0;
            z-index: 1030;
            height: 60px;
            background: white;
            border-bottom: 1px solid #dee2e6;
        }
        /* Adjust content to account for fixed header */
        .main-content {
            margin-top: 60px;
            padding: 20px;
        }
        /* Mobile responsive */
        @media (max-width: 768px) {
            .sidebar-container {
                position: relative;
                width: 100%;
                height: auto;
            }
            .content-container {
                margin-left: 0;
            }
            .header-container {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Main Container -->
    <div class="main-container">
        <!-- Sidebar Container -->
        <div class="sidebar-container">
            <?php include('includes/sidebar.php'); ?>
        </div>

        <!-- Content Container -->
        <div class="content-container">
            <!-- Header Container -->
            <div class="header-container">
                <?php include('includes/header.php'); ?>
            </div>

            <!-- Main Content Area -->
            <div class="main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Apply for Leave</h1>
                </div>

                <?php if ($error): ?>
                    <div class="errorWrap"><?php echo htmlentities($error); ?></div>
                <?php elseif ($msg): ?>
                    <div class="succWrap"><?php echo htmlentities($msg); ?></div>
                <?php endif; ?>

                <div class="card">
                    <div class="card-body">
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="leavetype" class="form-label">Leave Type</label>
                                    <select name="leavetype" class="form-select" required>
                                        <option value="">Select Leave Type</option>
                                        <?php 
                                        try {
                                            $sql = "SELECT LeaveType FROM tblleavetype";
                                            $query = $dbh->prepare($sql);
                                            $query->execute();
                                            $results = $query->fetchAll(PDO::FETCH_OBJ);
                                            if ($query->rowCount() > 0) {
                                                foreach ($results as $result) {
                                                    echo '<option value="'.htmlentities($result->LeaveType).'">'.htmlentities($result->LeaveType).'</option>';
                                                }
                                            }
                                        } catch (PDOException $e) {
                                            echo '<option value="">Error loading leave types</option>';
                                        }
                                        ?>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="fromdate" class="form-label">From Date</label>
                                    <input type="date" name="fromdate" class="form-control" required>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label for="todate" class="form-label">To Date</label>
                                    <input type="date" name="todate" class="form-control" required>
                                </div>
                                
                                <div class="col-12 mb-3">
                                    <label for="description" class="form-label">Reason / Description</label>
                                    <textarea name="description" class="form-control" rows="4" required placeholder="Please provide a reason for your leave application"></textarea>
                                </div>
                                
                                <div class="col-12">
                                    <button type="submit" name="apply" class="btn btn-primary">Apply Leave</button>
                                    <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Using CDN for Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>