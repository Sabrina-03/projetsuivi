<?php
require_once 'database.php';  // Connexion à la base de données
session_start();              // Démarre la session pour vérifier l'utilisateur

// Vérifie si l'utilisateur est connecté (sécurité)
if (!isset($_SESSION['email'])) {
    header("Location: index.php");  // Redirection vers la page de login si non connecté
    exit();
}

// Vérifie si un ID est passé dans l'URL pour supprimer un collaborateur
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Prépare et exécute la suppression dans la table "collaborateurs"
    $stmt = $pdo->prepare("DELETE FROM collaborateurs WHERE id = ?");
    $stmt->execute([$id]);
}

// Après suppression, redirige vers la page de gestion des collaborateurs
header("Location: gestion_collaborateurs.php");
exit();
