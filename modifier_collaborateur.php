<?php 
require_once 'database.php'; // Connexion à la base de données
session_start(); // Démarre la session

// Si l'utilisateur n'est pas connecté, on le redirige vers la page de connexion
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Vérifie si un ID a été passé dans l’URL
if (!isset($_GET['id'])) {
    echo "Aucun collaborateur sélectionné.";
    exit();
}

$id = $_GET['id'];

// Récupère les informations du collaborateur
$stmt = $pdo->prepare("SELECT * FROM collaborateurs WHERE id = ?");
$stmt->execute([$id]);
$collab = $stmt->fetch();

// Si aucun collaborateur trouvé
if (!$collab) {
    echo "Collaborateur introuvable.";
    exit();
}

$annee_selectionnee = 2025; // À adapter selon le besoin

$message = "";

// Si le formulaire a été soumis
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Liste des champs obligatoires
    $obligatoires = ['nom', 'prenom', 'niveau', 'specialite', 'statut', 'site_client'];

    // Vérifie si tous les champs obligatoires sont remplis
    foreach ($obligatoires as $champ) {
        if (empty($_POST[$champ])) {
            $message = "⚠️ Veuillez remplir tous les champs obligatoires.";
            break;
        }
    }

    // Si tout est bon, mise à jour dans la base
    if (empty($message)) {
        $sql = "UPDATE collaborateurs SET 
            nom = ?, prenom = ?, niveau = ?, specialite = ?, debut_prestation = ?, 
            statut = ?, site_client = ?, donneur_ordre = ?, 
            taux_2021 = ?, taux_2022 = ?, taux_2023 = ?, taux_2024 = ?, taux_2025 = ?, 
            ai_2021 = ?, ai_2022 = ?, ai_2023 = ?, ai_2024 = ?, ai_2025 = ?
            WHERE id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['niveau'],
            $_POST['specialite'],
            $_POST['debut_prestation'] ?? null,
            $_POST['statut'],
            $_POST['site_client'],
            $_POST['donneur_ordre'] ?? null,
            $_POST['taux_2021'] ?? null,
            $_POST['taux_2022'] ?? null,
            $_POST['taux_2023'] ?? null,
            $_POST['taux_2024'] ?? null,
            $_POST['taux_2025'] ?? null,
            $_POST['ai_2021'] ?? null,
            $_POST['ai_2022'] ?? null,
            $_POST['ai_2023'] ?? null,
            $_POST['ai_2024'] ?? null,
            $_POST['ai_2025'] ?? null,
            $id
        ]);

        // Redirection après mise à jour
        header("Location: gestion_collaborateurs.php");
        exit();
    }
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Modifier collaborateur</title>
    <!-- Fichier CSS -->
    <link rel="stylesheet" href="styles.css?v=17" />
    <!-- Adaptation mobile -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="page-modifier">

    <!-- Titre principal -->
    <h2 class="modifier-title">📝 Modifier un collaborateur</h2>

    <div class="main-container">
        <div class="left-section">

            <!-- Affiche un message d’erreur si nécessaire -->
            <?php if ($message): ?>
                <p class="error-message"><?= $message ?></p>
            <?php endif; ?>

            <!-- Formulaire de modification -->
            <form method="POST" class="ajout-form">
                <div class="form-columns">

                    <!-- Champs pour les infos de base -->
                    <div>
                        <label for="nom">Nom :</label>
                        <input type="text" name="nom" id="nom" value="<?= htmlspecialchars($collab['nom']) ?>" required>
                    </div>

                    <div>
                        <label for="prenom">Prénom :</label>
                        <input type="text" name="prenom" id="prenom" value="<?= htmlspecialchars($collab['prenom']) ?>" required>
                    </div>

                    <div>
                        <label for="niveau">Niveau :</label>
                        <input type="text" name="niveau" id="niveau" value="<?= htmlspecialchars($collab['niveau']) ?>" required>
                    </div>

                    <div>
                        <label for="specialite">Spécialité :</label>
                        <input type="text" name="specialite" id="specialite" value="<?= htmlspecialchars($collab['specialite']) ?>" required>
                    </div>

                    <div>
                        <label for="statut">Statut :</label>
                        <!-- Liste déroulante pour le statut -->
                        <select name="statut" id="statut" required>
                            <option value="">-- Choisir --</option>
                            <option value="actif" <?= strtolower($collab['statut']) == 'actif' ? 'selected' : '' ?>>Actif</option>
                            <option value="inactif" <?= strtolower($collab['statut']) == 'inactif' ? 'selected' : '' ?>>Inactif</option>
                        </select>
                    </div>

                    <div>
                        <label for="site_client">Site client :</label>
                        <input type="text" name="site_client" id="site_client" value="<?= htmlspecialchars($collab['site_client']) ?>" required>
                    </div>

                    <div>
                        <label for="debut_prestation">Date de prestation :</label>
                        <input type="date" name="debut_prestation" id="debut_prestation" value="<?= htmlspecialchars($collab['debut_prestation']) ?>">
                    </div>

                    <div>
                        <label for="donneur_ordre">Donneur d’ordres :</label>
                        <input type="text" name="donneur_ordre" id="donneur_ordre" value="<?= htmlspecialchars($collab['donneur_ordre']) ?>">
                    </div>

                    <!-- Boucle pour afficher les taux et AI de chaque année -->
                    <?php for ($y = 2021; $y <= $annee_selectionnee; $y++): ?>
                        <div>
                            <label for="taux_<?= $y ?>">Taux <?= $y ?>:</label>
                            <input type="number" name="taux_<?= $y ?>" id="taux_<?= $y ?>" step="0.01" value="<?= htmlspecialchars($collab['taux_' . $y]) ?>">
                        </div>
                        <div>
                            <label for="ai_<?= $y ?>">AI <?= $y ?>:</label>
                            <input type="number" name="ai_<?= $y ?>" id="ai_<?= $y ?>" step="0.01" value="<?= htmlspecialchars($collab['ai_' . $y]) ?>">
                        </div>
                    <?php endfor; ?>
                </div>

                <!-- Bouton de validation -->
                <button type="submit">Mettre à jour</button>

                <!-- Bouton retour -->
                <a href="gestion_collaborateurs.php?annee=<?= $annee_selectionnee ?>" class="btn-retour">Retour</a>
            </form>

        </div>
    </div>
</body>
</html>
