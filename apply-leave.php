<!DOCTYPE html>
<html lang="en">
<?php
session_start();
error_reporting(0);
include 'includes/config.php';

// Include PHPMailer classes
require 'includes/PHPMailer/PHPMailer.php';
require 'includes/PHPMailer/SMTP.php';
require 'includes/PHPMailer/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
    exit();
}

$msg = "";
$error = "";

if (isset($_POST['apply'])) {
    $empid = $_SESSION['eid'];
    $leavetype = $_POST['leavetype'];
    $fromdate = $_POST['fromdate'];
    $todate = $_POST['todate'];
    $description = $_POST['description'];
    $status = 0;
    $isread = 0;

    if ($fromdate > $todate) {
        $error = "ToDate should be greater than FromDate";
    } else {
        $sql = "INSERT INTO tblleaves(LeaveType,ToDate,FromDate,Description,Status,IsRead,empid) 
                VALUES(:leavetype,:todate,:fromdate,:description,:status,:isread,:empid)";
        $query = $dbh->prepare($sql);
        $query->bindParam(':leavetype', $leavetype, PDO::PARAM_STR);
        $query->bindParam(':fromdate', $fromdate, PDO::PARAM_STR);
        $query->bindParam(':todate', $todate, PDO::PARAM_STR);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':isread', $isread, PDO::PARAM_STR);
        $query->bindParam(':empid', $empid, PDO::PARAM_STR);
        $query->execute();
        $lastInsertId = $dbh->lastInsertId();

        if ($lastInsertId) {
            // Fetch supervisor email
            $sql = "SELECT s.EmailId, s.FirstName, s.LastName 
                    FROM tblemployees e 
                    JOIN tblemployees s ON e.supervisor_id = s.id 
                    WHERE e.id = :empid";
            $query = $dbh->prepare($sql);
            $query->bindParam(':empid', $empid, PDO::PARAM_INT);
            $query->execute();
            $supervisor = $query->fetch(PDO::FETCH_OBJ);

            if ($supervisor) {
                $mail = new PHPMailer(true);
                try {
                    $mail->isSMTP();
                    $mail->Host = 'smtp.gmail.com';
                    $mail->SMTPAuth = true;
                    $mail->Username = 'mrkanyi8@gmail.com'; // Gmail
                    $mail->Password = 'kanyi726_';   // Gmail App Password
                    $mail->SMTPSecure = 'tls';
                    $mail->Port = 587;

                    $mail->setFrom('mrkanyi8@gmail.com', 'ELMS System');
                    $mail->addAddress($supervisor->EmailId, $supervisor->FirstName . ' ' . $supervisor->LastName);

                    $mail->isHTML(false);
                    $mail->Subject = "Leave Request Notification";
                    $mail->Body = "Dear " . $supervisor->FirstName . " " . $supervisor->LastName . ",\n\n"
                                . "A new leave request has been submitted by your employee.\n"
                                . "Leave Type: $leavetype\n"
                                . "From: $fromdate\n"
                                . "To: $todate\n"
                                . "Reason: $description\n\n"
                                . "Please login to your dashboard to review and take action.\n\n"
                                . "Regards,\nELMS System";

                    $mail->send();
                    $msg = "Leave applied successfully. Supervisor notified!";
                } catch (Exception $e) {
                    $msg = "Leave applied, but email could not be sent. Mailer Error: {$mail->ErrorInfo}";
                }
            } else {
                $msg = "Leave applied successfully, but supervisor not found.";
            }
        } else {
            $error = "Something went wrong. Please try again";
        }
    }
}
?>
<head>
    <title>Employee | Apply Leave</title>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.min.css"/>
    <link type="text/css" rel="stylesheet" href="assets/plugins/materialize/css/materialize.css"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
    <link href="assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="assets/css/style.css" rel="stylesheet" type="text/css"/>
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
    </style>
</head>
<body>
    <?php include 'includes/header.php'; ?>
    <?php include 'includes/sidebar.php'; ?>

    <main class="mn-inner">
        <div class="row">
            <div class="col s12">
                <div class="page-title" style="color: green;">Apply for Leave</div>
            </div>
            <div class="col s12 m12 l12">
                <div class="card">
                    <div class="card-content">
                        <form id="example-form" method="post">
                            <div class="row">
                                <?php if ($error) { ?>
                                    <div class="errorWrap"><strong>ERROR </strong>:<?php echo htmlentities($error); ?> </div>
                                <?php } else if ($msg) { ?>
                                    <div class="succWrap"><strong>SUCCESS </strong>:<?php echo htmlentities($msg); ?> </div>
                                <?php } ?>

                                <div class="input-field col m6 s12">
                                    <label for="fromdate">From Date</label>
                                    <input type="text" name="fromdate" class="datepicker" required>
                                </div>

                                <div class="input-field col m6 s12">
                                    <label for="todate">To Date</label>
                                    <input type="text" name="todate" class="datepicker" required>
                                </div>

                                <div class="input-field col s12">
                                    <select name="leavetype" required>
                                        <option value="">Select leave type...</option>
                                        <?php
                                        $sql = "SELECT LeaveType FROM tblleavetype";
                                        $query = $dbh->prepare($sql);
                                        $query->execute();
                                        $results = $query->fetchAll(PDO::FETCH_OBJ);
                                        if ($query->rowCount() > 0) {
                                            foreach ($results as $result) { ?>
                                                <option value="<?php echo htmlentities($result->LeaveType); ?>">
                                                    <?php echo htmlentities($result->LeaveType); ?>
                                                </option>
                                        <?php } } ?>
                                    </select>
                                </div>

                                <div class="input-field col m12 s12">
                                    <label for="description">Description</label>
                                    <textarea id="textarea1" name="description" class="materialize-textarea" required></textarea>
                                </div>

                                <div align="center">
                                    <button type="submit" name="apply" id="apply" class="waves-effect waves-light btn indigo m-b-xs">
                                        Apply
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <div class="left-sidebar-hover"></div>

    <!-- ✅ Correct JS order -->
    <script src="assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="assets/plugins/materialize/js/materialize.min.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // ✅ Initialize Materialize datepickers
        var elems = document.querySelectorAll('.datepicker');
        if (M && M.Datepicker) {
            M.Datepicker.init(elems, { format: 'yyyy-mm-dd' });
        } else {
            console.error("Materialize Datepicker not loaded.");
        }

        // Initialize select dropdown
        var selects = document.querySelectorAll('select');
        if (M && M.FormSelect) {
            M.FormSelect.init(selects);
        }
    });
    </script>
    <script src="assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="assets/plugins/jquery-blockui/jquery.blockui.js"></script>
    <script src="assets/js/alpha.min.js"></script>
</body>
</html>
