<?php
require_once 'database.php';

// Récupérer les données POST
$id = $_POST['id'];
$annee = $_POST['annee'];

try {
    // Connexion à la base de données
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Vérifier si le collaborateur est déjà masqué
    $query = "SELECT masque FROM collaborateurs WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$id]);
    $result = $stmt->fetch();

    $masque = ($result['masque'] ?? 0) ? 0 : 1;

    // Mettre à jour le statut de masquage
    $query = "UPDATE collaborateurs SET masque = ? WHERE id = ?";
    $stmt = $pdo->prepare($query);
    $stmt->execute([$masque, $id]);

    // Redirection vers la page de gestion
    header("Location: gestion_collaborateurs.php?annee=$annee");
    exit();

} catch (PDOException $e) {
    die("Erreur de connexion à la base de données : " . $e->getMessage());
}
