<?php
require_once 'database.php';
// session_start() déjà géré dans header
include 'includes/header.php';

// Filtre "Afficher masqués"
$show_masques = isset($_GET['show_masques']) && $_GET['show_masques'] === '1';

// Masquer un devis
if (isset($_GET['masquer_devis_id'])) {
    $pdo->prepare('UPDATE devis SET masque = 1 WHERE id = ?')
        ->execute([ (int) $_GET['masquer_devis_id'] ]);
    header('Location: suivi_devis.php' . ($show_masques ? '?show_masques=1' : ''));
    exit;
}
// Réafficher un devis
if (isset($_GET['afficher_devis_id'])) {
    $pdo->prepare('UPDATE devis SET masque = 0 WHERE id = ?')
        ->execute([ (int) $_GET['afficher_devis_id'] ]);
    header('Location: suivi_devis.php' . ($show_masques ? '?show_masques=1' : ''));
    exit;
}

// Upload PDF
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['pdf'])) {
    $id = (int) ($_POST['devis_id'] ?? 0);
    if ($id > 0 && $_FILES['pdf']['error'] === UPLOAD_ERR_OK) {
        $tmp  = $_FILES['pdf']['tmp_name'];
        $name = basename($_FILES['pdf']['name']);
        $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));
        if ($ext === 'pdf') {
            $dir = 'uploads/';
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $dest = $dir . $name;
            $i = 1;
            while (file_exists($dest)) {
                $base = pathinfo($name, PATHINFO_FILENAME);
                $dest = "{$dir}{$base}_{$i}.{$ext}";
                $i++;
            }
            if (move_uploaded_file($tmp, $dest)) {
                $pdo->prepare('UPDATE devis SET fichier_pdf = ? WHERE id = ?')
                    ->execute([ $dest, $id ]);
            }
        }
    }
    header('Location: suivi_devis.php' . ($show_masques ? '?show_masques=1' : ''));
    exit;
}

// Chargement des devis
$sql = 'SELECT * FROM devis' . ($show_masques ? '' : ' WHERE masque = 0 OR masque IS NULL') . ' ORDER BY id DESC';
$stmt = $pdo->query($sql);
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Suivi des Devis</title>
  <link rel="stylesheet" href="styles.css?v=24">
</head>
<body class="page-devis">
  <div class="container">
    <header >
      <h1>📋 Suivi des Devis</h1>
      <a href="ajouter_devis.php" class="btn" >
        ➕ Ajouter un devis
      </a>
      <label style="margin-left:20px;">
        <input type="checkbox" onchange="location.href='suivi_devis.php'+(this.checked?'?show_masques=1':'')" <?= $show_masques ? 'checked' : '' ?> >
        Afficher masqués
      </label>
    </header>

    <table id="devisTable" border="1" cellpadding="6" cellspacing="0" width="100%">
      <thead>
        <tr>
          <th>N° de Devis</th><th>Rev.</th><th>Date</th><th>Client</th>
          <th>Libellé</th><th>Prog.</th><th>Rédacteur</th><th>Destinataire</th>
          <th>Réalisé par</th><th>Temps</th><th>PDF</th><th>Actions</th>
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
            <td><?= htmlspecialchars($r['temps']) ?></td>
            <td>
              <?php if (!empty($r['fichier_pdf'])): ?>
                <a href="<?= htmlspecialchars($r['fichier_pdf']) ?>" target="_blank"><?= basename($r['fichier_pdf']) ?></a><br>
              <?php endif; ?>
              <form action="" method="post" enctype="multipart/form-data" style="margin:0;">
                <input type="hidden" name="devis_id" value="<?= $r['id'] ?>">
                <input type="file" name="pdf" accept="application/pdf" onchange="this.form.submit()">
              </form>
            </td>
            <td>
              <a href="modifier_devis.php?id=<?= $r['id'] ?>">✏️ Modifier</a>
              <?php if (empty($r['masque'])): ?>
                &nbsp;|&nbsp;
                <a href="?masquer_devis_id=<?= $r['id'] ?>&show_masques=<?= (int)$show_masques ?>" onclick="return confirm('Masquer ce devis ?')">🚫 Masquer</a>
              <?php else: ?>
                &nbsp;|&nbsp;
                <a href="?afficher_devis_id=<?= $r['id'] ?>&show_masques=<?= (int)$show_masques ?>" onclick="return confirm('Réafficher ce devis ?')">👁 Afficher</a>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</body>
</html>
