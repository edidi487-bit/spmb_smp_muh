<?php
require_once 'config/db.php';

// Unset all session values
$_SESSION = array();

// If session cookie is used, delete it
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Destroy session
session_destroy();

// Redirect to home/login
header("Location: " . BASE_URL . "index.php");
exit();
?>
