<?php
require_once 'database.php';          // Connexion à la base de données
include 'includes/header.php';        // Inclusion de l'en-tête (menu, session, etc.)

// Vérifie si l'on souhaite afficher les devis masqués
$show_masques = isset($_GET['show_masques']) && $_GET['show_masques'] === '1';

// Masquer un devis (on met à jour la colonne "masque" à 1)
if (isset($_GET['masquer_devis_id'])) {
    $pdo->prepare('UPDATE devis SET masque = 1 WHERE id = ?')
        ->execute([ (int) $_GET['masquer_devis_id'] ]);
    header('Location: suivi_devis.php' . ($show_masques ? '?show_masques=1' : ''));
    exit;
}

// Réafficher un devis masqué (on remet "masque" à 0)
if (isset($_GET['afficher_devis_id'])) {
    $pdo->prepare('UPDATE devis SET masque = 0 WHERE id = ?')
        ->execute([ (int) $_GET['afficher_devis_id'] ]);
    header('Location: suivi_devis.php' . ($show_masques ? '?show_masques=1' : ''));
    exit;
}

// Téléversement (upload) d'un fichier PDF pour un devis
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $id = (int) ($_POST['devis_id'] ?? 0);
    if ($id > 0 && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['pdf']['tmp_name'];                        // Fichier temporaire
        $name = basename($_FILES['pdf']['name']);                  // Nom du fichier
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));   // Extension
        if ($ext === 'pdf') {
            $dir = 'uploads/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);            // Crée le dossier s’il n'existe pas
            $dest = $dir . $name;
            $i = 1;
            // Évite les doublons de noms
            while (file_exists($dest)) {
                $base = pathinfo($name, PATHINFO_FILENAME);
                $dest = "{$dir}{$base}_{$i}.{$ext}";
                $i++;
            }
            // Déplace le fichier dans le dossier "uploads" et met à jour la base
            if (move_uploaded_file($tmp, $dest)) {
                $pdo->prepare('UPDATE devis SET fichier_pdf = ? WHERE id = ?')
                    ->execute([ $dest, $id ]);
            }
        }
    }
    header('Location: suivi_devis.php' . ($show_masques ? '?show_masques=1' : ''));
    exit;
}

// Requête SQL : récupère tous les devis, sauf ceux masqués (sauf si show_masques = true)
$sql = 'SELECT * FROM devis' . ($show_masques ? '' : ' WHERE masque = 0 OR masque IS NULL') . ' ORDER BY id DESC';
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Suivi des Devis</title>
  <link rel="stylesheet" href="styles.css?v=20">
</head>
<body class="page-devis">
  <div class="container">
    <h1>📋 Suivi des devis forfaitaires</h1>

    <!-- Bouton pour ajouter un nouveau devis -->
    <a href="ajouter_devis.php" class="btn">➕ Ajouter un devis</a>

    <!-- Checkbox pour afficher/masquer les devis masqués -->
    <label>
      <input type="checkbox" class="afficher"
        onchange="location.href='suivi_devis.php'+(this.checked ? '?show_masques=1' : '')"
        <?= isset($_GET['show_masques']) ? 'checked' : '' ?>>
      Afficher les masqués
    </label>

    <!-- Tableau des devis -->
    <table id="devisTable" class="full-width-table">
      <thead>
        <tr>
          <th>N° de Devis</th><th>Rev.</th><th>Date</th><th>Client</th>
          <th>Libellé</th><th>Progression</th><th>Rédacteur</th><th>Destinataire</th>
          <th>Réalisé par</th><th>Temps (h)</th><th>PDF</th><th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rows as $r): ?>
          <tr class="<?= ($r['masque'] ?? 0) ? 'ligne-masquee' : '' ?>">
            <td><?= htmlspecialchars($r['numero_devis']) ?></td>
            <td><?= htmlspecialchars($r['revision']) ?></td>
            <td><?= htmlspecialchars($r['date']) ?></td>
            <td><?= htmlspecialchars($r['client']) ?></td>
            <td><?= htmlspecialchars($r['libelle']) ?></td>
            <td><?= htmlspecialchars($r['progression']) ?></td>
            <td><?= htmlspecialchars($r['redacteur']) ?></td>
            <td><?= htmlspecialchars($r['destinataire']) ?></td>
            <td><?= htmlspecialchars($r['realise_par']) ?></td>
            <td><?= htmlspecialchars($r['temps']) ?> h</td>

            <!-- PDF : lien + formulaire d’upload -->
            <td>
              <?php if (!empty($r['fichier_pdf'])): ?>
                <a href="<?= htmlspecialchars($r['fichier_pdf']) ?>" target="_blank">
                  <?= basename($r['fichier_pdf']) ?>
                </a><br>
              <?php endif; ?>
              <!-- Upload PDF -->
              <form action="" method="post" enctype="multipart/form-data" style="margin:0;">
                <input type="hidden" name="devis_id" value="<?= $r['id'] ?>">
                <input type="file" name="pdf" accept="application/pdf" onchange="this.form.submit()">
              </form>
            </td>

            <!-- Liens pour modifier, masquer ou afficher -->
            <td>
              <a href="modifier_devis.php?id=<?= $r['id'] ?>">Modifier</a>
              <?php if (empty($r['masque'])): ?>
                &nbsp;|&nbsp;
                <a href="?masquer_devis_id=<?= $r['id'] ?>&show_masques=<?= (int)$show_masques ?>"
                   onclick="return confirm('Masquer ce devis ?')">Masquer</a>
              <?php else: ?>
                &nbsp;|&nbsp;
                <a href="?afficher_devis_id=<?= $r['id'] ?>&show_masques=<?= (int)$show_masques ?>"
                   onclick="return confirm('Réafficher ce devis ?')">Afficher</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
