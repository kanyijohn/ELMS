<?php
session_start();
include 'includes/config.php';

if (strlen($_SESSION['emplogin']) == 0) {
    header('location:index.php');
    exit();
}

$msg = $error = "";

if (isset($_POST['change'])) {
    $password = md5($_POST['password']);
    $newpassword = md5($_POST['newpassword']);
    $username = $_SESSION['emplogin'];

    $sql = "SELECT Password FROM tblemployees WHERE EmailId=:username AND Password=:password";
    $query = $dbh->prepare($sql);
    $query->bindParam(':username', $username, PDO::PARAM_STR);
    $query->bindParam(':password', $password, PDO::PARAM_STR);
    $query->execute();

    if ($query->rowCount() > 0) {
        $con = "UPDATE tblemployees SET Password=:newpassword WHERE EmailId=:username";
        $chngpwd1 = $dbh->prepare($con);
        $chngpwd1->bindParam(':username', $username, PDO::PARAM_STR);
        $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
        $chngpwd1->execute();
        $msg = "Your password has been successfully changed.";
    } else {
        $error = "Your current password is incorrect.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | ELMS</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="assets/css/modern.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f5f6fa;
        }
        .main-wrapper {
            display: flex;
            min-height: 100vh;
        }
        .content-area {
            flex: 1;
            padding: 2rem;
            background-color: #fff;
        }
        .enhanced-card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        .password-strength {
            height: 4px;
            background: #e0e0e0;
            border-radius: 2px;
            margin-top: 0.5rem;
        }
        .password-strength-fill {
            height: 100%;
            width: 0%;
            background: #dc3545;
            transition: width 0.3s ease;
        }
    </style>
</head>

<body>
<div class="main-wrapper">
    <!-- Sidebar Include -->
    <div>
        <?php include('includes/sidebar.php'); ?>
    </div>

    <!-- Main Content -->
    <div class="content-area">
        <div class="container-fluid">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4><i class="fas fa-key"></i> Change Password</h4>
                <a href="index.php" class="btn btn-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
            </div>

            <?php if ($error) { ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i> <?php echo htmlentities($error); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } else if ($msg) { ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i> <?php echo htmlentities($msg); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php } ?>

            <div class="card enhanced-card">
                <div class="card-body">
                    <form method="post" id="passwordForm" autocomplete="off">
                        <div class="mb-3">
                            <label class="form-label">Current Password</label>
                            <div class="input-group">
                                <input type="password" name="password" id="currentPassword"
                                       class="form-control" required autocomplete="current-password">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="currentPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">New Password</label>
                            <div class="input-group">
                                <input type="password" name="newpassword" id="newPassword"
                                       class="form-control" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="newPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <div class="password-strength mt-2">
                                <div class="password-strength-fill" id="passwordStrength"></div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Confirm New Password</label>
                            <div class="input-group">
                                <input type="password" name="confirmpassword" id="confirmPassword"
                                       class="form-control" required autocomplete="new-password">
                                <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmPassword">
                                    <i class="fas fa-eye"></i>
                                </button>
                            </div>
                            <small id="confirmMessage"></small>
                        </div>

                        <div class="text-end">
                            <button type="submit" name="change" id="submitButton" class="btn btn-primary" disabled>
                                <i class="fas fa-save"></i> Update Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="card enhanced-card mt-4">
                <div class="card-header"><i class="fas fa-shield-alt"></i> Password Security Tips</div>
                <div class="card-body">
                    <ul class="mb-0">
                        <li>Use a combination of uppercase, lowercase, numbers, and symbols.</li>
                        <li>Avoid using personal information like birthdates or names.</li>
                        <li>Do not reuse old passwords from other sites.</li>
                        <li>Consider using a password manager for safety.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- JS Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Toggle Password Visibility
    $('.toggle-password').on('click', function() {
        const target = $(this).data('target');
        const input = $('#' + target);
        const icon = $(this).find('i');
        if (input.attr('type') === 'password') {
            input.attr('type', 'text');
            icon.removeClass('fa-eye').addClass('fa-eye-slash');
        } else {
            input.attr('type', 'password');
            icon.removeClass('fa-eye-slash').addClass('fa-eye');
        }
    });

    // Password Strength Checker
    $('#newPassword').on('input', function() {
        const password = $(this).val();
        let strength = 0;
        if (password.length >= 8) strength += 25;
        if (/[A-Z]/.test(password)) strength += 25;
        if (/[a-z]/.test(password)) strength += 25;
        if (/[0-9]/.test(password)) strength += 25;

        const bar = $('#passwordStrength');
        bar.css('width', strength + '%');
        bar.css('background', strength < 50 ? '#dc3545' : (strength < 75 ? '#ffc107' : '#28a745'));
        validateForm();
    });

    // Confirm Password Check
    $('#confirmPassword').on('input', function() {
        validateForm();
    });

    function validateForm() {
        const newPass = $('#newPassword').val();
        const confirmPass = $('#confirmPassword').val();
        const btn = $('#submitButton');
        const msg = $('#confirmMessage');

        if (newPass && confirmPass) {
            if (newPass === confirmPass) {
                msg.text('Passwords match ✅').css('color', 'green');
                btn.prop('disabled', false);
            } else {
                msg.text('Passwords do not match ❌').css('color', 'red');
                btn.prop('disabled', true);
            }
        } else {
            msg.text('');
            btn.prop('disabled', true);
        }
    }
</script>
</body>
</html>
