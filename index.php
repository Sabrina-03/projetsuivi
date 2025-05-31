<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion</title>
    <link rel="stylesheet" href="styles.css">
   

</head>
<body class="index-body">
    <div class="index-title-container">
        <span class="index-title">Gestion d'équipe</span>
    </div>

    <div class="index-login-container">
        <h2 class="index-login-title">Connexion</h2>
        <form method="POST" action="verif_connexion.php" class="index-login-form">
            <label class="index-label-email">Email :</label>
            <input type="email" name="email" required placeholder="exemple@email.com" class="index-input-email">

            <label class="index-label-password">Mot de passe :</label>
            <div class="index-password-wrapper">
                <input type="password" name="mot_de_passe" id="mot_de_passe" required class="index-input-password">
                <span class="index-toggle-password" onclick="togglePassword()">👁️</span>
            </div>

            <input type="submit" value="Se connecter" class="index-submit-button">
        </form>
    </div>

    <script>
    function togglePassword() {
        const input = document.getElementById("mot_de_passe");
        input.type = input.type === "password" ? "text" : "password";
    }
    </script>
</body>
</html>
