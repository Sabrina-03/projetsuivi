<?php
// Démarre une session PHP pour pouvoir enregistrer des données (comme l'email de l'utilisateur connecté)
session_start();

try {
    // Connexion à la base de données (host = localhost, nom = suivie_équipe, encodage UTF-8)
    $bdd = new PDO('mysql:host=localhost;dbname=suivie_équipe;charset=utf8', 'root', '');
} catch(Exception $e) {
    // En cas d'erreur de connexion, on affiche le message d'erreur
    die('Erreur : '.$e->getMessage());
}

// Récupère les données du formulaire envoyées en POST
$email = $_POST['email'];
$mot_de_passe = $_POST['mot_de_passe'];

// Prépare une requête SQL pour chercher un utilisateur avec l'email et le mot de passe donnés
$requete = $bdd->prepare('SELECT * FROM utilisateurs WHERE email = ? AND mot_de_passe = ?');
$requete->execute([$email, $mot_de_passe]); // Exécute la requête avec les valeurs reçues
$utilisateur = $requete->fetch(); // Récupère le résultat (sous forme de tableau associatif)

// Si un utilisateur est trouvé
if ($utilisateur) {
    // On enregistre son email dans la session
    $_SESSION['email'] = $utilisateur['email'];

    // On redirige vers la page d'accueil
    header("Location: accueil.php");
    exit();
} else {
    // Si aucun utilisateur trouvé, on affiche un message d'erreur
    echo "Email ou mot de passe incorrect.";
}
?>
