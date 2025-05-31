<?php 
// gestion_collaborateurs.php

include 'database.php';
include 'includes/header.php';

// ————————————————————————————
// 1. Initialisation des filtres
// ————————————————————————————
$annee_selectionnee = isset($_GET['annee']) 
    ? intval($_GET['annee']) 
    : date('Y');
$show_masques = isset($_GET['show_masques']);

// Prépare la base de redirection pour conserver les filtres
$redirBase = "?annee={$annee_selectionnee}" . ($show_masques ? "&show_masques=1" : '');

// ————————————————————————————
// 2. Toggle masquage collaborateurs
// ————————————————————————————
if (isset($_GET['masquer_collab_id'])) {
    $cid = intval($_GET['masquer_collab_id']);
    $pdo->prepare("UPDATE collaborateurs SET masque = 1 WHERE id = ?")->execute([$cid]);
    header("Location: gestion_collaborateurs.php{$redirBase}");
    exit;
}
if (isset($_GET['afficher_collab_id'])) {
    $cid = intval($_GET['afficher_collab_id']);
    $pdo->prepare("UPDATE collaborateurs SET masque = 0 WHERE id = ?")->execute([$cid]);
    header("Location: gestion_collaborateurs.php{$redirBase}");
    exit;
}

// ————————————————————————————
// 3. Chargement des collaborateurs
// ————————————————————————————
$sql = "SELECT * FROM collaborateurs WHERE annee = ?";
$params = [$annee_selectionnee];
if (!$show_masques) {
    $sql .= " AND (masque = 0 OR masque IS NULL)";
}
$sql .= " ORDER BY site_client, nom, prenom";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$collaborateurs = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $collaborateurs[$r['site_client']][] = $r;
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Gestion des Collaborateurs</title>
  <link rel="stylesheet" href="styles.css?v=15">
</head>
<body class="gestion-collaborateurs">
  <h1>👥 Gestion des Collaborateurs</h1>

  <form method="GET">
    <label>Année :
      <select name="annee" onchange="this.form.submit()">
        <?php for ($y = 2021; $y <= date('Y') + 10; $y++): ?>
          <option value="<?= $y ?>" <?= $y === $annee_selectionnee ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </label>
    <label style="margin-left:1em;">
      <input type="checkbox" name="show_masques" value="1" <?= $show_masques ? 'checked' : '' ?> onchange="this.form.submit()">
      Afficher masqués
    </label>
    <a href="ajouter_collaborateur.php?annee=<?= $annee_selectionnee ?>" class="btn">➕ Ajouter</a>
  </form>

  <?php if ($collaborateurs): ?>
    <?php foreach ($collaborateurs as $site => $collabs): ?>
      <h2><?= htmlspecialchars($site) ?></h2>
      <table>
        <thead>
          <tr>
            <th>Nom</th>
            <th>Prénom</th>
            <th>Spécialité</th>
            <th>Niveau</th>
            <th>Statut</th>
            <th>Site Client</th>
            <th>Début prestation</th>
            <th>Donneur d’ordres</th>
            <?php for ($y = 2021; $y <= $annee_selectionnee; $y++): ?>
              <th>Taux <?= $y ?></th>
              <th>AI <?= $y ?></th>
            <?php endfor; ?>
            <th>Actions</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($collabs as $c): ?>
            <tr class="ligne-collab <?= ($c['masque'] ?? 0) ? 'ligne-masquee' : '' ?>">
              <td><?= htmlspecialchars($c['nom']) ?></td>
              <td><?= htmlspecialchars($c['prenom']) ?></td>
              <td><?= htmlspecialchars($c['specialite']) ?></td>
              <td><?= htmlspecialchars($c['niveau']) ?></td>
              <td><?= htmlspecialchars($c['statut']) ?></td>
              <td><?= htmlspecialchars($c['site_client']) ?></td>
              <td><?= htmlspecialchars($c['debut_prestation']) ?></td>
              <td><?= htmlspecialchars($c['donneur_ordre']) ?></td>
              <?php for ($y = 2021; $y <= $annee_selectionnee; $y++): ?>
                <td><?= htmlspecialchars($c['taux_'.$y] ?? '') ?></td>
                <td><?= htmlspecialchars($c['ai_'.$y] ?? '') ?></td>
              <?php endfor; ?>
              <td>
                <a href="modifier_collaborateur.php?id=<?= $c['id'] ?>&annee=<?= $annee_selectionnee ?>">Modifier</a>
                <?php if (empty($c['masque'])): ?>
                  &nbsp;|&nbsp;
                  <a href="?masquer_collab_id=<?= $c['id'] ?><?= $redirBase ?>" onclick="return confirm('Masquer ce collaborateur ?')">Masqué</a>
                <?php else: ?>
                  &nbsp;|&nbsp;
                  <a href="?afficher_collab_id=<?= $c['id'] ?><?= $redirBase ?>" onclick="return confirm('Réactiver ce collaborateur ?')">Afficher</a>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    <?php endforeach; ?>
  <?php else: ?>
    <p>Aucun collaborateur trouvé pour l'année <?= $annee_selectionnee ?>.</p>
  <?php endif; ?>
</body>
</html>
