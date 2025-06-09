<!DOCTYPE html>
<!-- Déclaration du type de document HTML5 -->
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <!-- Encodage des caractères -->
    <title>Connexion</title>
    <!-- Titre de la page affiché dans l’onglet -->
    <link rel="stylesheet" href="styles.css?V=15">
    <!-- Lien vers le fichier CSS pour le style -->
</head>

<body class="index-body">
    <!-- Corps de la page avec une classe CSS pour le style -->

    <div class="index-title-container">
        <!-- Conteneur pour le titre principal -->
        <span class="index-title">Gestion d'équipe</span>
        <!-- Texte du titre -->
    </div>

    <div class="index-login-container">
        <!-- Conteneur pour le formulaire de connexion -->
        <h2 class="index-login-title">Connexion</h2>
        <!-- Sous-titre du formulaire -->

        <form method="POST" action="verif_connexion.php" class="index-login-form">
            <!-- Formulaire qui envoie les données à verif_connexion.php en POST -->

            <label class="index-label-email">Email :</label>
            <!-- Étiquette pour le champ email -->
            <input type="email" name="email" required placeholder="exemple@email.com" class="index-input-email">
            <!-- Champ de saisie de l’email -->

            <label class="index-label-password">Mot de passe :</label>
            <!-- Étiquette pour le mot de passe -->
            <div class="index-password-wrapper">
                <!-- Conteneur du champ mot de passe avec icône -->
                <input type="password" name="mot_de_passe" id="mot_de_passe" required class="index-input-password">
                <!-- Champ de mot de passe -->
                <span class="index-toggle-password" onclick="togglePassword()">👁️</span>
                <!-- Icône pour afficher/masquer le mot de passe -->
            </div>

            <input type="submit" value="Se connecter" class="index-submit-button">
            <!-- Bouton de soumission du formulaire -->
        </form>
    </div>

    <script>
    // Fonction pour afficher ou masquer le mot de passe
    function togglePassword() {
        const input = document.getElementById("mot_de_passe");
        input.type = input.type === "password" ? "text" : "password";
    }
    </script>
</body>
</html>
ss
