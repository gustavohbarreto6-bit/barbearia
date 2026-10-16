<?php
session_start();

if (isset($_SESSION['usuario']) && !empty($_SESSION['usuario'])) {
    header('Location: principal.php');
    exit();
}

header('Location: login.php');
exit();
