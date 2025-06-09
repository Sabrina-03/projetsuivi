<?php
// Informations pour se connecter à la base de données
$host = 'localhost';           
$dbname = 'suivie_équipe';     
$username = 'root';            
$password = '';               

try {
    // Connexion à la base de données avec PDO
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    
    // Active l'affichage des erreurs si quelque chose ne va pas
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
} catch (PDOException $e) {
    // En cas d’erreur de connexion, affiche un message et arrête tout
    die("Erreur de connexion : " . $e->getMessage());
}
?>
