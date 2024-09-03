<?php
session_start();
if (!isset($_SESSION['username'])) {
    header("Location: ../login.php");
    exit();
}

$role = $_SESSION['role'];

switch ($role) {
    case 'admin':
        header("Location: admin_dashboard.php");
        break;
    case 'rescuer':
        header("Location: rescuer_dashboard.php");
        break;
    case 'citizen':
        header("Location: citizen_dashboard.php");
        break;
    default:
        header("Location: ../login.php");
        break;
}
exit();
?>
