<?php
// ajouter_devis.php
// On se connecte à la base de données
require_once 'database.php';
session_start();

// Message d'erreur ou de confirmation
$message = '';

// Si on envoie le formulaire (via POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // On récupère les données du formulaire
    $data = [
        'numero_devis' => $_POST['numero_devis'],
        'revision'     => $_POST['revision'],
        'date'         => $_POST['date'],
        'client'       => $_POST['client'],
        'libelle'      => $_POST['libelle'],
        'progression'  => $_POST['progression'],
        'redacteur'    => $_POST['redacteur'],
        'destinataire' => $_POST['destinataire'],
        'realise_par'  => $_POST['realise_par'],
        'temps'        => $_POST['temps'],
    ];

    // On construit la requête SQL avec des noms de colonnes et des placeholders
    $sql = "INSERT INTO devis (" . implode(',', array_keys($data)) . ") VALUES (:" . implode(', :', array_keys($data)) . ")";

    // On prépare la requête pour éviter les injections SQL
    $stmt = $pdo->prepare($sql);

    // On exécute la requête avec les données du formulaire
    $stmt->execute($data);

    // On redirige vers la page de suivi des devis
    header('Location: suivi_devis.php');
    exit;
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un Devis</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="styles.css?v=16">
</head>
<body class="page-ajouter-devis">
  <div class="main-container">
    <h1 class="ajout-title">➕ Ajouter un Devis</h1>

    <!-- Formulaire pour ajouter un devis -->
    <form method="POST" action="ajouter_devis.php" class="ajout-form">
      <div class="form-grid">
        <div class="form-group">
          <label>Numéro de devis</label>
          <input name="numero_devis" required>
        </div>
        <div class="form-group">
          <label>Révision</label>
          <input name="revision">
        </div>
        <div class="form-group">
          <label>Date</label>
          <input type="date" name="date" required>
        </div>
        <div class="form-group">
          <label>Client</label>
          <input name="client" required>
        </div>
        <div class="form-group">
          <label>Libellé</label>
          <input name="libelle" required>
        </div>
        <div class="form-group">
          <label>Progression</label>
          <input name="progression">
        </div>
        <div class="form-group">
          <label>Rédacteur</label>
          <input name="redacteur">
        </div>
        <div class="form-group">
          <label>Destinataire</label>
          <input name="destinataire">
        </div>
        <div class="form-group">
          <label>Réalisé par</label>
          <input name="realise_par">
        </div>
        <div class="form-group">
          <label>Temps (h)</label>
          <input name="temps" type="number" step="0.1">
        </div>

        <!-- Boutons pour valider ou revenir -->
        <div class="btn-actions">
          <button type="submit">Enregistrer</button>
          <a href="suivi_devis.php" class="btn-retour">Retour</a>
        </div>

      </div>
    </form>
  </div>
</body>
</html>
