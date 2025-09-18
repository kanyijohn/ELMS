<?php
session_start();
$_SESSION = array();

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 3600,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

unset($_SESSION['alogin']); // for admin
unset($_SESSION['emplogin']); // for employees
session_destroy(); // destroy session

// Redirect to main login page at root
header("Location: /elms/index.php");
exit();
