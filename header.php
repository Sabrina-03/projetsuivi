<?php 
session_start();
if (!isset($_SESSION['email'])) {
    header("Location: connexion.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Gestion d'équipe</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css?v=4">
</head>
<body>

<header class="navbar">
    <div class="navbar-left">
        <img src="images/logo.png" alt="Logo gestion" class="navbar-logo">
    </div>

    <!-- NAVIGATION POUR DESKTOP -->
    <nav class="navbar-center desktop-menu">
        <a href="accueil.php" class="nav-link">Accueil</a>
        <a href="gestion_collaborateurs.php" class="nav-link">Collaborateurs</a>
        <a href="suivi_affectations.php" class="nav-link">Affectations</a>
        <a href="suivi_devis.php" class="nav-link">Devis</a>
        <a href="tableaux_bord.php" class="nav-link">Tableaux de bord</a>
    </nav>

    <!-- BOUTON MENU POUR MOBILE -->
    <button class="menu-toggle" id="menu-toggle">&#9776;</button>

    <div class="navbar-right desktop-menu">
        <a href="deconnexion.php" class="logout-btn">Se déconnecter</a>
    </div>
</header>

<!-- MENU MOBILE SLIDE -->
<nav class="mobile-menu" id="mobile-menu">
    <a href="accueil.php" class="nav-link">Accueil</a>
    <a href="gestion_collaborateurs.php" class="nav-link">Collaborateurs</a>
    <a href="suivi_affectations.php" class="nav-link">Affectations</a>
    <a href="suivi_devis.php" class="nav-link">Devis</a>
    <a href="tableaux_bord.php" class="nav-link">Tableaux de bord</a>
    <a href="deconnexion.php" class="logout-btn">Se déconnecter</a>
</nav>

<script>
    const toggleButton = document.getElementById('menu-toggle');
    const mobileMenu = document.getElementById('mobile-menu');

    toggleButton.addEventListener('click', () => {
        mobileMenu.classList.toggle('active');
    });

    // Fermer si on clique ailleurs
    document.addEventListener('click', (e) => {
        if (!mobileMenu.contains(e.target) && !toggleButton.contains(e.target)) {
            mobileMenu.classList.remove('active');
        }
    });
</script>

</body>
</html>
