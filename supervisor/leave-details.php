<?php
session_start();
error_reporting(0);
include '../includes/config.php';   // supervisor includes path

// ✅ Require employee login
if (strlen($_SESSION['emplogin']) == 0) {
    header('location: ../index.php');
    exit();
} else {
    // ✅ Check if logged-in employee is a Supervisor
    $email = $_SESSION['emplogin'];
    $sql = "SELECT Role FROM tblemployees WHERE EmailId = :email LIMIT 1";
    $query = $dbh->prepare($sql);
    $query->bindParam(':email', $email, PDO::PARAM_STR);
    $query->execute();
    $user = $query->fetch(PDO::FETCH_OBJ);

    if (!$user || strtolower($user->Role) !== 'supervisor') {
        // Not a supervisor → block access
        header('location: ../index.php');
        exit();
    }

    // ✅ Mark notification as read
    $isread = 1;
    $did = intval($_GET['leaveid']);
    date_default_timezone_set('Asia/Kolkata');
    $admremarkdate = date('Y-m-d G:i:s');
    $sql = "UPDATE tblleaves SET IsRead=:isread WHERE id=:did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':isread', $isread, PDO::PARAM_INT);
    $query->bindParam(':did', $did, PDO::PARAM_INT);
    $query->execute();

    // ✅ Action taken on leave
    if (isset($_POST['update'])) {
        $did = intval($_GET['leaveid']);
        $description = $_POST['description'];
        $status = $_POST['status'];
        $admremarkdate = date('Y-m-d G:i:s');

        $sql = "UPDATE tblleaves 
                SET AdminRemark=:description, Status=:status, AdminRemarkDate=:admremarkdate 
                WHERE id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':description', $description, PDO::PARAM_STR);
        $query->bindParam(':status', $status, PDO::PARAM_INT);
        $query->bindParam(':admremarkdate', $admremarkdate, PDO::PARAM_STR);
        $query->bindParam(':did', $did, PDO::PARAM_INT);
        $query->execute();

        $msg = "Leave updated Successfully";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Supervisor | Leave Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <meta charset="UTF-8">

    <!-- Styles (absolute paths) -->
    <link rel="stylesheet" href="/elms/assets/plugins/materialize/css/materialize.min.css"/>
    <link rel="stylesheet" href="/elms/assets/plugins/materialize/css/materialize.css"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="/elms/assets/plugins/material-preloader/css/materialPreloader.min.css"/>
    <link rel="stylesheet" href="/elms/assets/plugins/datatables/css/jquery.dataTables.min.css"/>
    <link rel="stylesheet" href="/elms/assets/plugins/google-code-prettify/prettify.css"/>
    <link rel="stylesheet" href="/elms/assets/css/alpha.min.css"/>
    <link rel="stylesheet" href="/elms/assets/css/custom.css"/>
    <link rel="stylesheet" href="/elms/assets/css/style.css"/>

    <style>
        .errorWrap {padding:10px;margin:0 0 20px 0;background:#fff;border-left:4px solid #dd3d36;box-shadow:0 1px 1px rgba(0,0,0,.1);}
        .succWrap {padding:10px;margin:0 0 20px 0;background:#fff;border-left:4px solid #5cb85c;box-shadow:0 1px 1px rgba(0,0,0,.1);}
    </style>
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
                            $lid = intval($_GET['leaveid']);
                            $sql = "SELECT tblleaves.id as lid,
                                           tblemployees.FirstName, tblemployees.LastName,
                                           tblemployees.EmpId, tblemployees.id, tblemployees.Gender,
                                           tblemployees.Phonenumber, tblemployees.EmailId,
                                           tblleaves.LeaveType, tblleaves.ToDate, tblleaves.FromDate,
                                           tblleaves.Description, tblleaves.PostingDate,
                                           tblleaves.Status, tblleaves.AdminRemark, tblleaves.AdminRemarkDate
                                    FROM tblleaves
                                    JOIN tblemployees ON tblleaves.empid=tblemployees.id
                                    WHERE tblleaves.id=:lid";
                            $query = $dbh->prepare($sql);
                            $query->bindParam(':lid', $lid, PDO::PARAM_INT);
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
                                    <td colspan="2"></td>
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
                                            <span style="color: red">Not Approved</span>
                                        <?php } else { ?>
                                            <span style="color: blue">Waiting for approval</span>
                                        <?php } ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td><b>Supervisor Remark :</b></td>
                                    <td colspan="5"><?php echo $result->AdminRemark == "" ? "Waiting for Approval" : htmlentities($result->AdminRemark); ?></td>
                                </tr>
                                <tr>
                                    <td><b>Action Date :</b></td>
                                    <td colspan="5"><?php echo $result->AdminRemarkDate == "" ? "NA" : htmlentities($result->AdminRemarkDate); ?></td>
                                </tr>
                                <?php if ($stats == 0) { ?>
                                <tr>
                                    <td colspan="6">
                                        <a class="modal-trigger waves-effect waves-light btn" href="#modal1">Take Action</a>
                                        <form name="supervisoraction" method="post">
                                            <div id="modal1" class="modal modal-fixed-footer" style="height: 60%">
                                                <div class="modal-content" style="width:90%">
                                                    <h4>Leave Action</h4>
                                                    <select class="browser-default" name="status" required>
                                                        <option value="">Choose your option</option>
                                                        <option value="1">Approved</option>
                                                        <option value="2">Not Approved</option>
                                                    </select>
                                                    <p>
                                                        <textarea id="textarea1" name="description" class="materialize-textarea" placeholder="Description" maxlength="500" required></textarea>
                                                    </p>
                                                </div>
                                                <div class="modal-footer" style="width:90%">
                                                    <input type="submit" class="waves-effect waves-light btn blue" name="update" value="Submit">
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

    <!-- Scripts -->
    <script src="/elms/assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="/elms/assets/plugins/materialize/js/materialize.min.js"></script>
    <script src="/elms/assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="/elms/assets/plugins/jquery-blockui/jquery.blockui.js"></script>
    <script src="/elms/assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
    <script src="/elms/assets/js/alpha.min.js"></script>
    <script src="/elms/assets/js/pages/table-data.js"></script>
    <script src="/elms/assets/js/pages/ui-modals.js"></script>
    <script src="/elms/assets/plugins/google-code-prettify/prettify.js"></script>
</body>
</html>
<?php } ?>
