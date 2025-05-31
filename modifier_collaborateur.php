<?php 
require_once 'database.php';
session_start();

if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

if (!isset($_GET['id'])) {
    echo "Aucun collaborateur sélectionné.";
    exit();
}

$id = $_GET['id'];

// Récupérer les infos du collaborateur
$stmt = $pdo->prepare("SELECT * FROM collaborateurs WHERE id = ?");
$stmt->execute([$id]);
$collab = $stmt->fetch();

if (!$collab) {
    echo "Collaborateur introuvable.";
    exit();
}

$annee_selectionnee = 2025; // à adapter si nécessaire

$message = "";

// Mise à jour après soumission
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Champs obligatoires
    $obligatoires = ['nom', 'prenom', 'niveau', 'specialite', 'statut', 'site_client'];

    foreach ($obligatoires as $champ) {
        if (empty($_POST[$champ])) {
            $message = "⚠️ Veuillez remplir tous les champs obligatoires.";
            break;
        }
    }

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

        header("Location: gestion_collaborateurs.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Modifier collaborateur</title>
    <link rel="stylesheet" href="styles.css?v=16" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

</head>
<body class="page-modifier">
    <h2 class="modifier-title">📝 Modifier un collaborateur</h2>

    <div class="main-container">
        <div class="left-section">

            <?php if ($message): ?>
                <p class="error-message"><?= $message ?></p>
            <?php endif; ?>

            <form method="POST" class="ajout-form">
                <div class="form-columns">
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

                <button type="submit">Mettre à jour</button>
            </form>
        </div>
    </div>
</body>
</html>
