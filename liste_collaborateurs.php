<?php
session_start();
require_once 'database.php'; // ton fichier de connexion

// Vérifie si l'utilisateur est connecté
if (!isset($_SESSION['email'])) {
    header("Location: index.php");
    exit();
}

// Récupération des collaborateurs depuis la BDD
$stmt = $pdo->query("SELECT * FROM collaborateurs");
$collaborateurs = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Liste des collaborateurs</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #aaa;
            padding: 8px;
            text-align: center;
        }
        th {
            background-color: #eee;
        }
    </style>
</head>
<body>

<h2>Liste des collaborateurs</h2>

<a href="tableau_de_bord.php">⬅️ Retour au tableau de bord</a>

<table>
    <tr>
        <th>Nom</th>
        <th>Prénom</th>
        <th>Niveau</th>
        <th>Spécialité</th>
        <th>Début prestation</th>
        <th>Prime (€)</th>
        <th>Statut</th>
        <th>Site client</th>
        <th>Donneur d'ordre</th>
        <th>Taux 2025</th>
        <th>AI 2025</th>
    </tr>

    <?php foreach ($collaborateurs as $collab): ?>
        <tr>
            <td><?= htmlspecialchars($collab['nom']) ?></td>
            <td><?= htmlspecialchars($collab['prenom']) ?></td>
            <td><?= htmlspecialchars($collab['niveau']) ?></td>
            <td><?= htmlspecialchars($collab['specialite']) ?></td>
            <td><?= htmlspecialchars($collab['debut_prestation']) ?></td>
            <td><?= htmlspecialchars($collab['prime']) ?></td>
            <td><?= htmlspecialchars($collab['statut']) ?></td>
            <td><?= htmlspecialchars($collab['site_client']) ?></td>
            <td><?= htmlspecialchars($collab['donneur_ordre']) ?></td>
            <td><?= htmlspecialchars($collab['taux_2025']) ?></td>
            <td><?= htmlspecialchars($collab['ai_2025']) ?></td>
        </tr>
    <?php endforeach; ?>

</table>

</body>
</html>
