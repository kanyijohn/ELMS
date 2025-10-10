<?php
session_start();
include('includes/config.php');

if (strlen($_SESSION['alogin']) == 0) {
    header('location:index.php');
    exit();
} else {
    // Password change logic
    if (isset($_POST['change'])) {
        $password = md5($_POST['password']);
        $newpassword = md5($_POST['newpassword']);
        $username = $_SESSION['alogin'];

        $sql = "SELECT Password FROM admin WHERE UserName=:username AND Password=:password";
        $query = $dbh->prepare($sql);
        $query->bindParam(':username', $username, PDO::PARAM_STR);
        $query->bindParam(':password', $password, PDO::PARAM_STR);
        $query->execute();

        if ($query->rowCount() > 0) {
            $con = "UPDATE admin SET Password=:newpassword WHERE UserName=:username";
            $chngpwd1 = $dbh->prepare($con);
            $chngpwd1->bindParam(':username', $username, PDO::PARAM_STR);
            $chngpwd1->bindParam(':newpassword', $newpassword, PDO::PARAM_STR);
            $chngpwd1->execute();
            $msg = "Your password has been successfully changed.";
        } else {
            $error = "Your current password is incorrect.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Change Password | Employee Leave Management System</title>

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Custom CSS -->
    <link rel="stylesheet" href="../assets/css/modern.css">

    <style>
        .password-strength {
            height: 5px;
            background: #e9ecef;
            border-radius: 3px;
            margin-top: 0.5rem;
            overflow: hidden;
        }
        .password-strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s ease;
        }
        .password-requirements {
            font-size: 0.875rem;
            margin-top: 0.5rem;
        }
        .requirement-met { color: #28a745; }
        .requirement-unmet { color: #adb5bd; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <?php include('includes/sidebar.php'); ?>

        <div class="main-content p-4">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h3 mb-1">Change Password</h1>
                    <p class="text-muted mb-0">Update your account password securely.</p>
                </div>
                <a href="dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Dashboard
                </a>
            </div>

            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0">
                        <div class="card-header bg-primary text-white">
                            <h5 class="mb-0"><i class="fas fa-key me-2"></i> Update Password</h5>
                        </div>
                        <div class="card-body">

                            <?php if(isset($error)) { ?>
                                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo htmlentities($error); ?></div>
                            <?php } elseif(isset($msg)) { ?>
                                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlentities($msg); ?></div>
                            <?php } ?>

                            <form method="post" id="passwordForm" name="changepassword" onsubmit="return valid();">
                                <div class="mb-3">
                                    <label for="password" class="form-label">Current Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="password" class="form-control" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="password">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="newpassword" class="form-label">New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="newpassword" id="newpassword" class="form-control" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="newpassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>

                                    <!-- Password strength & requirements placed right below new password -->
                                    <div class="password-strength mt-2">
                                        <div class="password-strength-fill" id="passwordStrength"></div>
                                    </div>

                                    <div class="password-requirements mt-2">
                                        <div id="reqLength"><i class="fas fa-circle requirement-unmet"></i> At least 8 characters</div>
                                        <div id="reqUppercase"><i class="fas fa-circle requirement-unmet"></i> One uppercase letter</div>
                                        <div id="reqLowercase"><i class="fas fa-circle requirement-unmet"></i> One lowercase letter</div>
                                        <div id="reqNumber"><i class="fas fa-circle requirement-unmet"></i> One number</div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label for="confirmpassword" class="form-label">Confirm New Password</label>
                                    <div class="input-group">
                                        <input type="password" name="confirmpassword" id="confirmpassword" class="form-control" required>
                                        <button type="button" class="btn btn-outline-secondary toggle-password" data-target="confirmpassword">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                    </div>
                                    <small id="confirmMessage"></small>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" name="change" id="submitButton" class="btn btn-primary">
                                        <i class="fas fa-check"></i> Change Password
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <div class="card mt-4 shadow-sm border-0">
                        <div class="card-header bg-light">
                            <strong><i class="fas fa-shield-alt"></i> Password Security Tips</strong>
                        </div>
                        <div class="card-body">
                            <ul class="mb-0">
                                <li>Use a mix of uppercase, lowercase, and numbers.</li>
                                <li>Avoid personal information like birthdays.</li>
                                <li>Never reuse old passwords.</li>
                                <li>Consider using a password manager.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/modern.js"></script>

    <script>
        // Toggle password visibility
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

        // Password validation before submit
        function valid() {
            const newpassword = $('#newpassword').val();
            const confirmpassword = $('#confirmpassword').val();

            if (newpassword.length < 8) {
                alert("New password must be at least 8 characters long.");
                return false;
            }

            if (newpassword !== confirmpassword) {
                alert("New password and confirmation password do not match!");
                return false;
            }
            return true;
        }

        // Password strength and requirements
        $('#newpassword').on('input', function() {
            const password = $(this).val();
            let strength = 0;

            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);

            // Update indicators
            $('#reqLength i').toggleClass('requirement-met', hasLength).toggleClass('requirement-unmet', !hasLength);
            $('#reqUppercase i').toggleClass('requirement-met', hasUppercase).toggleClass('requirement-unmet', !hasUppercase);
            $('#reqLowercase i').toggleClass('requirement-met', hasLowercase).toggleClass('requirement-unmet', !hasLowercase);
            $('#reqNumber i').toggleClass('requirement-met', hasNumber).toggleClass('requirement-unmet', !hasNumber);

            // Strength bar update
            if (hasLength) strength += 25;
            if (hasUppercase) strength += 25;
            if (hasLowercase) strength += 25;
            if (hasNumber) strength += 25;

            $('#passwordStrength').css('width', strength + '%');
            if (strength <= 25) $('#passwordStrength').css('background', 'red');
            else if (strength <= 50) $('#passwordStrength').css('background', 'orange');
            else if (strength <= 75) $('#passwordStrength').css('background', 'yellow');
            else $('#passwordStrength').css('background', 'green');
        });

        // Confirm password check
        $('#confirmpassword').on('input', function() {
            const newpassword = $('#newpassword').val();
            const confirmpassword = $(this).val();
            const message = $('#confirmMessage');
            if (newpassword === confirmpassword) {
                message.text("Passwords match!").css('color', 'green');
            } else {
                message.text("Passwords do not match!").css('color', 'red');
            }
        });
    </script>
</body>
</html>
