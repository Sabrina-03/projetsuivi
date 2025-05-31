<?php
session_start();
try {
    $bdd = new PDO('mysql:host=localhost;dbname=suivie_équipe;charset=utf8', 'root', '');
} catch(Exception $e) {
    die('Erreur : '.$e->getMessage());
}

$email = $_POST['email'];
$mot_de_passe = $_POST['mot_de_passe'];

$requete = $bdd->prepare('SELECT * FROM utilisateurs WHERE email = ? AND mot_de_passe = ?');
$requete->execute([$email, $mot_de_passe]);
$utilisateur = $requete->fetch();

if ($utilisateur) {
    $_SESSION['email'] = $utilisateur['email']; // ENREGISTRE L'EMAIL DANS LA SESSION
    header("Location: accueil.php");
    exit();
} else {
    echo "Email ou mot de passe incorrect.";
}
?>
