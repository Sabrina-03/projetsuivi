<?php
require_once 'database.php';
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM collaborateurs WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: gestion_collaborateurs.php");
exit();
