<?php
if (isset($_GET['session_name'])) {
    session_name($_GET['session_name']);
} elseif (isset($_POST['session_name'])) {
    session_name($_POST['session_name']);
} elseif (isset($_COOKIE['session_name'])) {
    session_name($_COOKIE['session_name']);
}
session_start();
session_destroy();
setcookie('session_name', '', time() - 3600, '/');
header("Location: ../login.php");
exit();
