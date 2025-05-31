<?php
include 'database.php';
include 'includes/functions.php';
include 'includes/header.php';

// ————————————————————————————
// 1. Initialisation des filtres
// ————————————————————————————
$clients = [
    'TOUS','SIDEL','RENAULT CLEON','RENAULT SANDOUVILLE',
    'ORIL','WÄRTSILÄ','2H ENERGY','AGENCE'
];
$selectedClient = $_GET['client'] ?? 'TOUS';
$showInactive   = isset($_GET['show_inactive']);
$startYear      = date('Y');
$startMonth     = 5;

// Génère 12 mois glissants à partir de mai
function getNext12MonthsSliding($year, $monthStart) {
    $m = [];
    for ($i = 0; $i < 12; $i++) {
        $ts = mktime(0, 0, 0, $monthStart + $i, 1, $year);
        $m[] = date('Y-m', $ts);
    }
    return $m;
}
$months = getNext12MonthsSliding($startYear, $startMonth);

// ————————————————————————————
// 1.b Chargement et groupement des collaborateurs
// ————————————————————————————
$groupes = [];

if ($selectedClient === 'TOUS') {
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, site_client
        FROM collaborateurs
        WHERE annee = ?
        ORDER BY site_client, nom, prenom
    ");
    $stmt->execute([$startYear]);
} else {
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, site_client
        FROM collaborateurs
        WHERE annee = ? AND site_client = ?
        ORDER BY nom, prenom
    ");
    $stmt->execute([$startYear, $selectedClient]);
}
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $groupes[$r['site_client']][] = $r;
}

$redirBase = "?client=" . urlencode($selectedClient)
           . ($showInactive ? "&show_inactive=1" : '');

// ————————————————————————————
// 2. Enregistrement POST
// ————————————————————————————
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commandes'])) {
    foreach ($_POST['commandes'] as $collabId => $cmds) {
        foreach ($cmds as $data) {
            $nom = trim($data['nom_commande']);
            $tot = intval($data['jours_total']);
            if ($nom !== '') {
                $chk = $pdo->prepare("
                    SELECT id FROM commandes 
                    WHERE collaborateur_id=? AND nom_commande=? 
                      AND client=? AND annee=?
                ");
                $chk->execute([$collabId, $nom, $selectedClient, $startYear]);
                $eid = $chk->fetchColumn();
                if ($eid) {
                    $u = $pdo->prepare("UPDATE commandes SET jours_total=?, active=1 WHERE id=?");
                    $u->execute([$tot, $eid]);
                    $cmdId = $eid;
                } else {
                    $i = $pdo->prepare("
                        INSERT INTO commandes
                          (collaborateur_id,nom_commande,jours_total,annee,client,active)
                        VALUES(?,?,?,?,?,1)
                    ");
                    $i->execute([$collabId, $nom, $tot, $startYear, $selectedClient]);
                    $cmdId = $pdo->lastInsertId();
                }
                if (!empty($data['mois']) && is_array($data['mois'])) {
                    $up = $pdo->prepare("
                        INSERT INTO consommations
                          (commande_id,mois,jours_consummes)
                        VALUES(?,?,?)
                        ON DUPLICATE KEY UPDATE jours_consummes=?
                    ");
                    foreach ($data['mois'] as $m => $j) {
                        $j = intval($j);
                        $up->execute([$cmdId, $m, $j, $j]);
                    }
                }
            }
        }
    }
    header("Location: {$redirBase}");
    exit;
}

// ————————————————————————————
// 3. Masquage de commande
// ————————————————————————————
if (isset($_GET['masquer_commande_id'])) {
    $mid = intval($_GET['masquer_commande_id']);
    $pdo->prepare("UPDATE commandes SET active=0 WHERE id=?")->execute([$mid]);
    header("Location: {$redirBase}");
    exit;
}

// ————————————————————————————
// 4. Chargement des commandes
// ————————————————————————————
$sql = "
    SELECT c.id, c.collaborateur_id, c.nom_commande,
           c.jours_total, c.active, cs.mois, cs.jours_consummes
    FROM commandes c
    LEFT JOIN consommations cs ON cs.commande_id = c.id
    WHERE c.annee = ? AND (c.client = ? OR ? = 'TOUS')
";
$params = [$startYear, $selectedClient, $selectedClient];
if (!$showInactive) {
    $sql .= " AND c.active = 1";
}
$sql .= " ORDER BY c.collaborateur_id, c.id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

$commandesParCollab = [];
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $cid = $r['collaborateur_id'];
    $cmd = $r['id'];
    if (!isset($commandesParCollab[$cid][$cmd])) {
        $commandesParCollab[$cid][$cmd] = [
            'id'           => $cmd,
            'nom_commande' => $r['nom_commande'],
            'jours_total'  => $r['jours_total'],
            'active'       => $r['active'],
            'mois'         => []
        ];
    }
    if ($r['mois']) {
        $commandesParCollab[$cid][$cmd]['mois'][$r['mois']] = $r['jours_consummes'];
    }
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="UTF-8">
  <title>Suivi des affectations mensuelles</title>
  <link rel="stylesheet" href="styles.css?v=22">

</head>
<body class="with-navbar suivi-commandes">

  <div class="header-affectation">
    <h2>📊 Suivi des affectations mensuelles</h2>
    <form method="get" class="header-filtres">
      <label>Filtre :
        <select name="client" onchange="this.form.submit()">
          <?php foreach ($clients as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $c === $selectedClient?'selected':''?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>
      <label>
        <input type="checkbox" name="show_inactive" <?= $showInactive?'checked':''?>
               onchange="this.form.submit()"> Afficher masquées
      </label>
    </form>
    <div class="annee-en-cours">📅 Année en cours : <?= date('Y') ?></div>
  </div>

  <form method="POST">
    <?php foreach ($groupes as $client => $collabs): ?>
      <section class="client-section">
        <h2 class="titre-client">Client « <?= htmlspecialchars($client) ?> »</h2>

        <?php foreach ($collabs as $r):
          $colId = $r['id'];
          $cmds  = $commandesParCollab[$colId] ?? [];
        ?>
          <h3><?= htmlspecialchars($r['nom'].' '.$r['prenom']) ?></h3>
          <table>
            <thead>
              <tr>
                <th>Numéro de commande</th><th>Jours totaux</th>
                <?php foreach ($months as $m): ?>
                  <th><?= date('M Y',strtotime($m)) ?><br>(<?= joursOuvresDuMois(substr($m,0,4),substr($m,5,2))?>j)</th>
                <?php endforeach; ?>
                <th>nombre de jours restants</th><th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $idx=0; ?>
              <?php foreach ($cmds as $cmd): 
                $tot  = $cmd['jours_total'];
                $used = array_sum($cmd['mois']);
                $rem  = $tot - $used;
                $cls  = $cmd['active']?'':'commande-masquee';
              ?>
                <tr class="<?= $cls ?>">
                  <td><input type="text" name="commandes[<?= $colId?>][<?= $idx?>][nom_commande]"    value="<?=htmlspecialchars($cmd['nom_commande'])?>"></td>
                  <td><input type="number" name="commandes[<?= $colId?>][<?= $idx?>][jours_total]"     value="<?=$tot?>"></td>
                  <?php foreach ($months as $m): ?>
                    <td><input type="number" name="commandes[<?= $colId?>][<?= $idx?>][mois][<?= $m?>]" value="<?=$cmd['mois'][$m]??0?>"></td>
                  <?php endforeach; ?>
                  <td class="<?= $rem<0?'negatif':''?>"><?=$rem?></td>
                  <td>
           <button type="button" class="btn-masquer"
               onclick="masquer(<?= $cmd['id'] ?>);event.stopPropagation();">
                Masquer
              </button>

                  </td>
                </tr>
              <?php $idx++; endforeach; ?>

              <!-- ligne vierge -->
              <tr>
                <td><input type="text" name="commandes[<?= $colId?>][<?= $idx?>][nom_commande]"></td>
                <td><input type="number" name="commandes[<?= $colId?>][<?= $idx?>][jours_total]"></td>
                <?php foreach ($months as $m): ?>
                  <td><input type="number" name="commandes[<?= $colId?>][<?= $idx?>][mois][<?= $m?>]"></td>
                <?php endforeach; ?>
                <td>-</td>
                <td></td>
              </tr>
            </tbody>
          </table>
          <button type="button" class="ajouter-commande" onclick="ajouterCommande(<?= $colId ?>)">➕ Ajouter une commande</button>
        <?php endforeach; ?>
      </section>
    <?php endforeach; ?>

    <button type="submit">💾 Enregistrer</button>
  </form>

  <script>
    function masquer(id) {
      if (confirm("Masquer cette commande ?")) {
        window.location.href = "<?= $redirBase ?>&masquer_commande_id=" + id;
      }
    }
    function ajouterCommande(colId) {
      const tbl = document.querySelector('#table-'+colId+' tbody');
      const last = tbl.querySelector('tr:last-child');
      const clone = last.cloneNode(true);
      clone.querySelectorAll('input').forEach(i=>i.value='');
      tbl.appendChild(clone);
    }
  </script>
</body>
</html>
