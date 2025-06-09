<?php
// On inclut le fichier pour se connecter à la base de données
require_once 'database.php';
session_start();

// Sécurité : on vérifie que l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    // Sinon on le redirige vers la page de connexion
    header("Location: index.php");
    exit();
}

// On récupère l'utilisateur connecté
$email = $_SESSION['email'];
$stmt = $pdo->prepare("SELECT * FROM utilisateurs WHERE email = ?");
$stmt->execute([$email]);
$user = $stmt->fetch();

// Si ce n'est pas un admin, on bloque l'accès
if ($user['role'] !== 'admin') {
    echo "Accès refusé.";
    exit();
}

// On prend l'année sélectionnée (ou l'année actuelle si rien de choisi)
$annee_selectionnee = isset($_GET['annee']) ? intval($_GET['annee']) : date('Y');

// Message d'erreur par défaut vide
$message = "";

// Si on a envoyé le formulaire (méthode POST)
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Champs qu'on doit obligatoirement remplir
    $obligatoires = [
        'nom', 'prenom', 'niveau', 'specialite', 'statut', 'site_client'
    ];

    // On vérifie que chaque champ obligatoire est rempli
    foreach ($obligatoires as $champ) {
        if (empty($_POST[$champ])) {
            $message = "\u26a0\ufe0f Veuillez remplir tous les champs obligatoires.";
            break;
        }
    }

    // Si tout est bon
    if (empty($message)) {
        // Liste des colonnes de la table
        $colonnes = [
            'annee', 'nom', 'prenom', 'niveau', 'specialite', 'statut', 'site_client',
            'debut_prestation', 'donneur_ordre'
        ];

        // On ajoute les colonnes pour chaque année (taux et AI)
        for ($y = 2021; $y <= $annee_selectionnee; $y++) {
            $colonnes[] = 'taux_' . $y;
            $colonnes[] = 'ai_' . $y;
        }

        // On prépare la requête SQL avec des "?" pour sécuriser
        $placeholders = str_repeat('?, ', count($colonnes) - 1) . '?';
        $sql = "INSERT INTO collaborateurs (" . implode(', ', $colonnes) . ") VALUES ($placeholders)";

        $stmt = $pdo->prepare($sql);

        // On crée un tableau avec toutes les données à insérer
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

        // Ajouter les valeurs taux et AI pour chaque année
        for ($y = 2021; $y <= $annee_selectionnee; $y++) {
            $donnees[] = $_POST['taux_' . $y] ?? null;
            $donnees[] = $_POST['ai_' . $y] ?? null;
        }

        // On exécute la requête pour insérer les données
        $stmt->execute($donnees);

        // On redirige vers la page de gestion avec l'année
        header("Location: gestion_collaborateurs.php?annee=$annee_selectionnee");
        exit();
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ajouter un collaborateur</title>
    <link rel="stylesheet" href="styles.css?v=23">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body class="page-ajouter">

<div class="main-container">
  <div class="left-section">
    <h2 class="ajout-title">➕ Ajouter un collaborateur</h2>

    <!-- Affiche un message si une erreur -->
    <?php if ($message): ?>
      <p class="error-message"><?= $message ?></p>
    <?php endif; ?>

    <!-- Formulaire d'ajout de collaborateur -->
    <form method="POST" class="ajout-form">
      <div class="form-grid">
        <!-- Tous les champs de saisie -->
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

        <!-- Champs taux et AI pour chaque année -->
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

      <!-- Bouton pour valider -->
      <button type="submit">Ajouter</button>
      <a href="gestion_collaborateurs.php?annee=<?= $annee_selectionnee ?>" class="btn-retour">Retour</a>
    </form>
  </div>
</div>

</body>
</html>
