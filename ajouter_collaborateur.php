<?php
require_once 'database.php';
session_start();

// Sécurité : accès réservé à l'admin
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

if ($user['role'] !== 'admin') {
    echo "Accès refusé.";
    exit();
}

// Récupérer l'année sélectionnée
$annee_selectionnee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');

// Traitement du formulaire
$message = "";
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Champs obligatoires
    $obligatoires = [
        'nom', 'prenom', 'niveau', 'specialite', 'statut', 'site_client'
    ];

    foreach ($obligatoires as $champ) {
        if (empty($_POST[$champ])) {
            $message = "⚠️ Veuillez remplir tous les champs obligatoires.";
            break;
        }
    }

    if (empty($message)) {
        // Préparer la requête d'insertion
        $colonnes = [
            'annee', 'nom', 'prenom', 'niveau', 'specialite', 'statut', 'site_client',
            'debut_prestation', 'donneur_ordre'
        ];

        // Ajouter les colonnes dynamiques pour toutes les années jusqu'à l'année sélectionnée
        for ($y = 2021; $y <= $annee_selectionnee; $y++) {
            $colonnes[] = 'taux_' . $y;
            $colonnes[] = 'ai_' . $y;
        }

        // Construire la requête SQL
        $placeholders = str_repeat('?, ', count($colonnes) - 1) . '?';
        $sql = "INSERT INTO collaborateurs (" . implode(', ', $colonnes) . ") VALUES ($placeholders)";

        // Préparer et exécuter la requête
        $stmt = $pdo->prepare($sql);

        // Données à insérer
        $donnees = [
            $annee_selectionnee,
            $_POST['nom'],
            $_POST['prenom'],
            $_POST['niveau'],
            $_POST['specialite'],
            $_POST['statut'],
            $_POST['site_client'],
            $_POST['debut_prestation'] ?? null,
            $_POST['donneur_ordre'] ?? null
        ];

        for ($y = 2021; $y <= $annee_selectionnee; $y++) {
            $donnees[] = $_POST['taux_' . $y] ?? null;
            $donnees[] = $_POST['ai_' . $y] ?? null;
        }

        $stmt->execute($donnees);

        header("Location: gestion_collaborateurs.php?annee=$annee_selectionnee");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un collaborateur</title>
    <link rel="stylesheet" href="styles.css?v=18">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="page-ajouter">

<div class="main-container">
  <div class="left-section">
    <h2 class="ajout-title">➕ Ajouter un collaborateur</h2>

    <?php if ($message): ?>
      <p class="error-message"><?= $message ?></p>
    <?php endif; ?>

    <form method="POST" class="ajout-form">
      <div class="form-grid">
        <div class="form-group">
          <label for="nom">Nom :</label>
          <input type="text" name="nom" id="nom" required>
        </div>

        <div class="form-group">
          <label for="prenom">Prénom :</label>
          <input type="text" name="prenom" id="prenom" required>
        </div>

        <div class="form-group">
          <label for="niveau">Niveau :</label>
          <input type="text" name="niveau" id="niveau" required>
        </div>

        <div class="form-group">
          <label for="specialite">Spécialité :</label>
          <input type="text" name="specialite" id="specialite" required>
        </div>

        <div class="form-group">
          <label for="statut">Statut :</label>
          <select name="statut" id="statut" required>
            <option value="">-- Choisir --</option>
            <option value="actif">Actif</option>
            <option value="inactif">Inactif</option>
          </select>
        </div>

        <div class="form-group">
          <label for="site_client">Site client :</label>
          <input type="text" name="site_client" id="site_client" required>
        </div>

        <div class="form-group">
          <label for="debut_prestation">Date de prestation :</label>
          <input type="date" name="debut_prestation" id="debut_prestation">
        </div>

        <div class="form-group">
          <label for="donneur_ordre">Donneur d’ordres :</label>
          <input type="text" name="donneur_ordre" id="donneur_ordre">
        </div>

        <?php for ($y = 2021; $y <= $annee_selectionnee; $y++): ?>
          <div class="form-group">
            <label for="taux_<?= $y ?>">Taux <?= $y ?> :</label>
            <input type="number" name="taux_<?= $y ?>" id="taux_<?= $y ?>" step="0.01">
          </div>
          <div class="form-group">
            <label for="ai_<?= $y ?>">AI <?= $y ?> :</label>
            <input type="number" name="ai_<?= $y ?>" id="ai_<?= $y ?>" step="0.01">
          </div>
        <?php endfor; ?>
      </div>

      <button type="submit">Ajouter</button>
      <a href="gestion_collaborateurs.php?annee=<?= $annee_selectionnee ?>" class="btn-retour">Retour</a>
    </form>
  </div>
</div>

</body>
</html>
