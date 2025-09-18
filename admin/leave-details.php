<?php 
session_start();
error_reporting(0);
include 'includes/config.php';
if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
} else {

    // Mark leave as read
    $isread = 1;
    $did = intval($_GET['leaveid']);
    $sql = "UPDATE tblleaves SET IsRead=:isread WHERE id=:did";
    $query = $dbh->prepare($sql);
    $query->bindParam(':isread', $isread, PDO::PARAM_STR);
    $query->bindParam(':did', $did, PDO::PARAM_STR);
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
        $query->bindParam(':status', $status, PDO::PARAM_STR);
        $query->bindParam(':admremarkdate', $admremarkdate, PDO::PARAM_STR);
        $query->bindParam(':did', $did, PDO::PARAM_STR);
        $query->execute();
        $msg = "Leave status updated successfully";
    }

    // Admin issue leave
    if (isset($_POST['issue'])) {
        $did = intval($_GET['leaveid']);
        $sql = "UPDATE tblleaves SET Issued=1 WHERE id=:did";
        $query = $dbh->prepare($sql);
        $query->bindParam(':did', $did, PDO::PARAM_STR);
        $query->execute();
        $msg = "Leave issued successfully";
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin | Leave Details</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta charset="UTF-8">

    <!-- Styles -->
    <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.min.css"/>
    <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.css"/>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../assets/plugins/material-preloader/css/materialPreloader.min.css" rel="stylesheet">
    <link href="../assets/plugins/datatables/css/jquery.dataTables.min.css" rel="stylesheet">
    <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/css/custom.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css"/>

    <style>
        .errorWrap {
            padding: 10px; margin: 0 0 20px 0;
            background: #fff; border-left: 4px solid #dd3d36;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
        .succWrap {
            padding: 10px; margin: 0 0 20px 0;
            background: #fff; border-left: 4px solid #5cb85c;
            box-shadow: 0 1px 1px 0 rgba(0,0,0,.1);
        }
    </style>
</head>
<body>
<?php include 'includes/header.php';?>
<?php include 'includes/sidebar.php';?>

<main class="mn-inner">
    <div class="row">
        <div class="col s12">
            <div class="page-title" style="font-size:24px;">Leave Details</div>
        </div>

        <div class="col s12 m12 l12">
            <div class="card">
                <div class="card-content">
                    <span class="card-title">Leave Details</span>
                    <?php if ($msg) { ?>
                        <div class="succWrap"><strong>SUCCESS</strong>: <?php echo htmlentities($msg); ?></div>
                    <?php } ?>

                    <table id="example" class="display responsive-table">
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
                        $query->bindParam(':lid', $lid, PDO::PARAM_STR);
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
                            <td><b>Gender:</b></td>
                            <td><?php echo htmlentities($result->Gender); ?></td>
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
                            <td colspan="5"><?php echo htmlentities($result->Description); ?></td>
                        </tr>
                        <tr>
                            <td><b>Supervisor Status:</b></td>
                            <td colspan="5">
                                <?php if ($result->Status == 1) echo '<span style="color:green">Approved</span>';
                                elseif ($result->Status == 2) echo '<span style="color:red">Rejected</span>';
                                else echo '<span style="color:blue">Pending</span>'; ?>
                            </td>
                        </tr>
                        <tr>
                            <td><b>Admin Remark:</b></td>
                            <td colspan="5"><?php echo $result->AdminRemark ? htmlentities($result->AdminRemark) : "None"; ?></td>
                        </tr>
                        <tr>
                            <td><b>Admin Action Date:</b></td>
                            <td colspan="5"><?php echo $result->AdminRemarkDate ? htmlentities($result->AdminRemarkDate) : "NA"; ?></td>
                        </tr>
                        <tr>
                            <td><b>Issued:</b></td>
                            <td colspan="5"><?php echo $result->Issued ? '<span style="color:green">Yes</span>' : '<span style="color:red">No</span>'; ?></td>
                        </tr>

                        <?php if ($result->Status == 0) { ?>
                        <!-- Approve/Reject Modal -->
                        <tr>
                            <td colspan="6">
                                <a class="modal-trigger btn" href="#actionModal">Take Action</a>
                                <form method="post">
                                    <div id="actionModal" class="modal">
                                        <div class="modal-content">
                                            <h4>Admin Action</h4>
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

                        <?php if ($result->Issued == 0) { ?>
                        <!-- Issue Leave -->
                        <tr>
                            <td colspan="6">
                                <form method="post">
                                    <button type="submit" name="issue" class="btn green">Issue Leave</button>
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

<!-- JS Scripts -->
<script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
<script src="../assets/plugins/materialize/js/materialize.min.js"></script>
<script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
<script src="../assets/plugins/jquery-blockui/jquery.blockui.js"></script>
<script src="../assets/plugins/datatables/js/jquery.dataTables.min.js"></script>
<script src="../assets/js/alpha.min.js"></script>
<script src="../assets/js/pages/table-data.js"></script>
<script src="../assets/plugins/google-code-prettify/prettify.js"></script>

<script>
    $(document).ready(function(){
        $('.modal').modal();
        $('select').formSelect();
    });
</script>
</body>
</html>
<?php } ?>
