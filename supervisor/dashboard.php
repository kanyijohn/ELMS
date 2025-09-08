<?php
session_start();

// Always use absolute paths for includes
include __DIR__ . '/../includes/config.php';

if (!isset($_SESSION['eid']) || $_SESSION['role'] != 'Supervisor') {
    header('location:../index.php');
    exit();
}

$supervisor_id = $_SESSION['eid'];

// Handle supervisor action (approve/decline)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['leave_id'], $_POST['action'])) {
    $leave_id = intval($_POST['leave_id']);
    $action = $_POST['action'];
    $remark = trim($_POST['SupervisorRemark']);
    $status = $action === 'Approve' ? 'Approved' : 'Declined';
    $actionDate = date('Y-m-d H:i:s');

    $sql = "UPDATE tblleaves 
            SET SupervisorStatus=:status, 
                SupervisorRemark=:remark, 
                SupervisorActionDate=:actionDate 
            WHERE id=:leave_id";
    $query = $dbh->prepare($sql);
    $query->bindParam(':status', $status, PDO::PARAM_STR);
    $query->bindParam(':remark', $remark, PDO::PARAM_STR);
    $query->bindParam(':actionDate', $actionDate, PDO::PARAM_STR);
    $query->bindParam(':leave_id', $leave_id, PDO::PARAM_INT);
    $query->execute();
}

// Fetch leave requests for this supervisor
$sql = "SELECT l.*, e.FirstName, e.LastName 
        FROM tblleaves l
        JOIN tblemployees e ON l.empid = e.id
        WHERE e.supervisor_id = :supid";
$query = $dbh->prepare($sql);
$query->bindParam(':supid', $supervisor_id, PDO::PARAM_INT);
$query->execute();
$leaves = $query->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <!-- Title -->
    <title>Supervisor | Dashboard</title>

    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"/>
    <meta charset="UTF-8">
    <meta name="description" content="Responsive Admin Dashboard Template" />
    <meta name="keywords" content="admin,dashboard" />
    <meta name="author" content="Steelcoders" />

    <!-- Styles -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css">
    <link type="text/css" rel="stylesheet" href="../assets/plugins/materialize/css/materialize.css"/>
    <link href="http://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="../assets/plugins/metrojs/MetroJs.min.css" rel="stylesheet">
    <link href="../assets/plugins/weather-icons-master/css/weather-icons.min.css" rel="stylesheet">

    <!-- Theme Styles -->
    <link href="../assets/css/alpha.min.css" rel="stylesheet" type="text/css"/>
    <link href="../assets/css/style.css" rel="stylesheet" type="text/css"/>
</head>
<body>
    <?php include __DIR__ . '/../includes/header.php'; ?>
    <?php include __DIR__ . '/../includes/sidebar.php'; ?>

    <main class="mn-inner mt-5">
        <div class="">
            <div class="row no-m-t no-m-b">
                <div class="col s12 m12 col-md-4 l4">
                    <div class="card stats-card border-0 shadow bg-dark">
                        <div class="card-content">
                            <span class="card-title text-white">Total Registered Employees</span>
                            <span class="stats-counter text-white">
                                <?php
                                $sql = "SELECT id FROM tblemployees";
                                $query = $dbh->prepare($sql);
                                $query->execute();
                                $empcount = $query->rowCount();
                                ?>
                                <span class="counter"><?php echo htmlentities($empcount); ?></span>
                            </span>
                        </div>
                        <div id="sparkline-bar"></div>
                    </div>
                </div>

                <div class="col s12 m12 col-md-4 l4">
                    <div class="card stats-card border-0 shadow bg-dark">
                        <div class="card-content">
                            <span class="card-title text-white">Total Departments</span>
                            <?php
                            $sql = "SELECT id FROM tbldepartments";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $dptcount = $query->rowCount();
                            ?>
                            <span class="stats-counter text-white">
                                <span class="counter"><?php echo htmlentities($dptcount); ?></span>
                            </span>
                        </div>
                        <div id="sparkline-line"></div>
                    </div>
                </div>

                <div class="col s12 m12 col-md-4 l4">
                    <div class="card stats-card border-0 shadow bg-dark">
                        <div class="card-content">
                            <span class="card-title text-white">Total Leave Types</span>
                            <?php
                            $sql = "SELECT id FROM tblleavetype";
                            $query = $dbh->prepare($sql);
                            $query->execute();
                            $leavtypcount = $query->rowCount();
                            ?>
                            <span class="stats-counter text-white">
                                <span class="counter"><?php echo htmlentities($leavtypcount); ?></span>
                            </span>
                        </div>
                        <div id="sparkline-line"></div>
                    </div>
                </div>
            </div>

            <div class="row no-m-t no-m-b">
                <div class="col s12 m12 l12 col-md-12">
                    <div class="card invoices-card border-0 shadow">
                        <div class="card-content">
                            <span class="card-title text-success">Latest Leave Applications</span>
                            <table id="example" class="display responsive-table bg-transparent">
                                <thead>
                                    <tr>
                                        <th class="text-danger">Sl No.</th>
                                        <th width="200" class="text-danger">Employee Name</th>
                                        <th width="120" class="text-danger">Leave Type</th>
                                        <th width="180" class="text-danger">Posting Date</th>
                                        <th class="text-danger">Status</th>
                                        <th align="center" class="text-danger text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $sql = "SELECT tblleaves.id as lid,
                                                   tblemployees.FirstName,
                                                   tblemployees.LastName,
                                                   tblemployees.EmpId,
                                                   tblemployees.id,
                                                   tblleaves.LeaveType,
                                                   tblleaves.PostingDate,
                                                   tblleaves.Status 
                                            FROM tblleaves 
                                            JOIN tblemployees ON tblleaves.empid = tblemployees.id 
                                            ORDER BY lid DESC LIMIT 6";
                                    $query = $dbh->prepare($sql);
                                    $query->execute();
                                    $results = $query->fetchAll(PDO::FETCH_OBJ);
                                    $cnt = 1;
                                    if ($query->rowCount() > 0) {
                                        foreach ($results as $result) {
                                    ?>
                                    <tr>
                                        <td><b><?php echo htmlentities($cnt); ?></b></td>
                                        <td>
                                            <a href="editemployee.php?empid=<?php echo htmlentities($result->id); ?>" target="_blank">
                                                <?php echo htmlentities($result->FirstName . " " . $result->LastName); ?>
                                                (<?php echo htmlentities($result->EmpId); ?>)
                                            </a>
                                        </td>
                                        <td><?php echo htmlentities($result->LeaveType); ?></td>
                                        <td><?php echo htmlentities($result->PostingDate); ?></td>
                                        <td>
                                            <?php
                                            $stats = $result->Status;
                                            if ($stats == 1) {
                                                echo '<span style="color: green">Approved</span>';
                                            } elseif ($stats == 2) {
                                                echo '<span style="color: red">Not Approved</span>';
                                            } else {
                                                echo '<span style="color: blue">Waiting for approval</span>';
                                            }
                                            ?>
                                        </td>
                                        <td>
                                            <a href="leave-details.php?leaveid=<?php echo htmlentities($result->lid); ?>" 
                                               class="waves-effect waves-light btn blue m-b-xs">View Details</a>
                                        </td>
                                    </tr>
                                    <?php $cnt++; }} ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Javascripts -->
    <script src="../assets/plugins/jquery/jquery-2.2.0.min.js"></script>
    <script src="../assets/plugins/materialize/js/materialize.min.js"></script>
    <script src="../assets/plugins/material-preloader/js/materialPreloader.min.js"></script>
    <script src="../assets/plugins/jquery-blockui/jquery.blockui.js"></script>
    <script src="../assets/plugins/waypoints/jquery.waypoints.min.js"></script>
    <script src="../assets/plugins/counter-up-master/jquery.counterup.min.js"></script>
    <script src="../assets/plugins/jquery-sparkline/jquery.sparkline.min.js"></script>
    <script src="../assets/plugins/chart.js/chart.min.js"></script>
    <script src="../assets/plugins/flot/jquery.flot.min.js"></script>
    <script src="../assets/plugins/flot/jquery.flot.time.min.js"></script>
    <script src="../assets/plugins/flot/jquery.flot.symbol.min.js"></script>
    <script src="../assets/plugins/flot/jquery.flot.resize.min.js"></script>
    <script src="../assets/plugins/flot/jquery.flot.tooltip.min.js"></script>
    <script src="../assets/plugins/curvedlines/curvedLines.js"></script>
    <script src="../assets/plugins/peity/jquery.peity.min.js"></script>
    <script src="../assets/js/alpha.min.js"></script>
    <script src="../assets/js/pages/dashboard.js"></script>
</body>
</html>