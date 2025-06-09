<?php
// Inclusion des fichiers nécessaires
include 'database.php';
include 'includes/functions.php';
include 'includes/header.php';

// ————————————————————————————
// 1. Préparation des filtres
// ————————————————————————————
// Liste des clients disponibles
$clients = [
    'TOUS','SIDEL','RENAULT CLEON','RENAULT SANDOUVILLE',
    'ORIL','WÄRTSILÄ','2H ENERGY','AGENCE'
];

// Récupération du filtre sélectionné
$selectedClient = $_GET['client'] ?? 'TOUS';
$showInactive   = isset($_GET['show_inactive']);
$startYear      = date('Y'); // année en cours
$startMonth     = 5;         // démarrage en mai

// Fonction pour générer 12 mois glissants
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
// 1.b Chargement des collaborateurs par client
// ————————————————————————————
$groupes = [];

if ($selectedClient === 'TOUS') {
    // Tous les collaborateurs de l'année
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, site_client
        FROM collaborateurs
        WHERE annee = ?
        ORDER BY site_client, nom, prenom
    ");
    $stmt->execute([$startYear]);
} else {
    // Collaborateurs d'un client spécifique
    $stmt = $pdo->prepare("
        SELECT id, nom, prenom, site_client
        FROM collaborateurs
        WHERE annee = ? AND site_client = ?
        ORDER BY nom, prenom
    ");
    $stmt->execute([$startYear, $selectedClient]);
}
// Regroupe les collaborateurs par site client
while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $groupes[$r['site_client']][] = $r;
}

// Préparation du lien de redirection
$redirBase = "?client=" . urlencode($selectedClient)
           . ($showInactive ? "&show_inactive=1" : '');

// ————————————————————————————
// 2. Traitement du formulaire POST
// ————————————————————————————
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['commandes'])) {
    foreach ($_POST['commandes'] as $collabId => $cmds) {
        foreach ($cmds as $data) {
            $nom = trim($data['nom_commande']);
            $tot = intval($data['jours_total']);

            // On ajoute seulement si le nom est rempli
            if ($nom !== '') {
                // Vérifie si la commande existe déjà
                $chk = $pdo->prepare("
                    SELECT id FROM commandes 
                    WHERE collaborateur_id=? AND nom_commande=? 
                      AND client=? AND annee=?
                ");
                $chk->execute([$collabId, $nom, $selectedClient, $startYear]);
                $eid = $chk->fetchColumn();

                if ($eid) {
                    // Mise à jour si elle existe
                    $u = $pdo->prepare("UPDATE commandes SET jours_total=?, active=1 WHERE id=?");
                    $u->execute([$tot, $eid]);
                    $cmdId = $eid;
                } else {
                    // Insertion d'une nouvelle commande
                    $i = $pdo->prepare("
                        INSERT INTO commandes
                          (collaborateur_id,nom_commande,jours_total,annee,client,active)
                        VALUES(?,?,?,?,?,1)
                    ");
                    $i->execute([$collabId, $nom, $tot, $startYear, $selectedClient]);
                    $cmdId = $pdo->lastInsertId();
                }

                // Enregistrement des jours consommés par mois
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
// 3. Masquage d'une commande
// ————————————————————————————
if (isset($_GET['masquer_commande_id'])) {
    $mid = intval($_GET['masquer_commande_id']);
    $pdo->prepare("UPDATE commandes SET active=0 WHERE id=?")->execute([$mid]);
    header("Location: {$redirBase}");
    exit;
}

// ————————————————————————————
// 4. Chargement des commandes existantes
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
    // On affiche seulement les commandes actives
    $sql .= " AND c.active = 1";
}
$sql .= " ORDER BY c.collaborateur_id, c.id";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);

// Organise les commandes par collaborateur
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
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Suivi des affectations mensuelles</title>
  <link rel="stylesheet" href="styles.css?v=18">
</head>

<body class="with-navbar suivi-commandes">

  <!-- En-tête de la page -->
  <div class="header-affectation">
    <h2>📆 Suivi des affectations mensuelles</h2>

    <!-- Filtres en haut de page -->
    <form method="get" class="header-filtres">
      <label>Filtre par client:
        <select name="client" onchange="this.form.submit()">
          <!-- Affiche les options de clients -->
          <?php foreach ($clients as $c): ?>
            <option value="<?= htmlspecialchars($c) ?>" <?= $c === $selectedClient?'selected':''?>>
              <?= htmlspecialchars($c) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </label>

      <!-- Option pour afficher les commandes masquées -->
      <label>
        <input type="checkbox" name="show_inactive" <?= $showInactive?'checked':''?>
               onchange="this.form.submit()"> Afficher les masquées
      </label>
    </form>

    <!-- Affiche l'année en cours -->
    <div class="annee-en-cours"><?= date('Y') ?></div>
  </div>

  <!-- Formulaire principal -->
  <form method="POST">
    <?php foreach ($groupes as $client => $collabs): ?>
      <section class="client-section">
        <h2 class="titre-client">« <?= htmlspecialchars($client) ?> »</h2>

        <!-- Liste des collaborateurs -->
        <?php foreach ($collabs as $r):
          $colId = $r['id'];
          $cmds  = $commandesParCollab[$colId] ?? [];
        ?>
          <h3><?= htmlspecialchars($r['nom'].' '.$r['prenom']) ?></h3>

          <!-- Tableau des commandes -->
          <table id="table-<?= $colId ?>">
            <thead>
              <tr>
                <th>N° de commande</th>
                <th>Jours totaux</th>
                <!-- En-têtes des mois -->
                <?php foreach ($months as $m): ?>
                  <th><?= date('M Y',strtotime($m)) ?> (<?= joursOuvresDuMois(substr($m,0,4),substr($m,5,2))?>j)</th>
                <?php endforeach; ?>
                <th>nombre de jours restants</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <?php $idx=0; ?>
              <?php foreach ($cmds as $cmd): 
                $tot  = $cmd['jours_total'];                  // Total jours prévus
                $used = array_sum($cmd['mois']);             // Total jours utilisés
                $rem  = $tot - $used;                         // Jours restants
                $cls  = $cmd['active'] ? '' : 'commande-masquee'; // Classe CSS si masqué
              ?>
                <tr class="<?= $cls ?>">
                  <!-- Champs de saisie -->
                  <td><input type="text" name="commandes[<?= $colId?>][<?= $idx?>][nom_commande]"    value="<?=htmlspecialchars($cmd['nom_commande'])?>"></td>
                  <td><input type="number" name="commandes[<?= $colId?>][<?= $idx?>][jours_total]"     value="<?=$tot?>"></td>

                  <!-- Champs par mois -->
                  <?php foreach ($months as $m): ?>
                    <td><input type="number" name="commandes[<?= $colId?>][<?= $idx?>][mois][<?= $m?>]" value="<?=$cmd['mois'][$m]??0?>"></td>
                  <?php endforeach; ?>

                  <!-- Affichage jours restants -->
                  <td class="<?= $rem<0 ? 'negatif' : '' ?>"><?= $rem ?></td>

                  <!-- Bouton pour masquer la commande -->
                  <td>
                    <button type="button" class="btn-masquer"
                            onclick="masquer(<?= $cmd['id'] ?>);event.stopPropagation();">
                      Masquer
                    </button>
                  </td>
                </tr>
              <?php $idx++; endforeach; ?>

              <!-- Ligne vide pour ajouter une nouvelle commande -->
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

          <!-- Bouton pour ajouter dynamiquement une nouvelle ligne -->
          <button type="button" class="ajouter-commande" onclick="ajouterCommande(<?= $colId ?>)">➕ Ajouter une commande</button>
        <?php endforeach; ?>
      </section>
    <?php endforeach; ?>

    <!-- Bouton pour soumettre le formulaire -->
    <button type="submit"> Enregistrer</button>
  </form>

  <!-- Scripts JS -->
  <script>
    // Fonction pour masquer une commande
    function masquer(id) {
      if (confirm("Masquer cette commande ?")) {
        window.location.href = "<?= $redirBase ?>&masquer_commande_id=" + id;
      }
    }

    // Fonction pour ajouter dynamiquement une ligne vide
    function ajouterCommande(colId) {
      const tbl = document.querySelector('#table-'+colId+' tbody');
      const last = tbl.querySelector('tr:last-child');  // Dernière ligne
      const clone = last.cloneNode(true);               // Copie de la ligne

      // Vide les champs
      clone.querySelectorAll('input').forEach(i => i.value = '');
      tbl.appendChild(clone);                           // Ajoute la ligne au tableau
    }
  </script>
</body>
</html>
