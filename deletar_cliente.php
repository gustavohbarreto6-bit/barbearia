<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header('Location: login.php');
    exit();
}
if (time() - $_SESSION['ultimo_acesso'] > 30 * 60) {
    session_destroy();
    header('Location: login.php?expirou=1');
    exit();
}
$_SESSION['ultimo_acesso'] = time();
include 'config.php';
$id = (int) ($_GET['id'] ?? 0);
$stmt = $conn->prepare("DELETE FROM clientes WHERE id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
header('Location: criar_clientes.php');
exit();
