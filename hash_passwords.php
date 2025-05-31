<?php
try {
    $bdd = new PDO('mysql:host=localhost;dbname=suivie_équipe;charset=utf8', 'root', '');
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $requete = $bdd->query('SELECT id, mot_de_passe FROM utilisateurs');
    $utilisateurs = $requete->fetchAll(PDO::FETCH_ASSOC);

    foreach ($utilisateurs as $utilisateur) {
        $id = $utilisateur['id'];
        $mot_de_passe = $utilisateur['mot_de_passe'];

        if (!password_needs_rehash($mot_de_passe, PASSWORD_DEFAULT)) {
            continue; // Ignore les mots de passe déjà hachés
        }

        $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);
        $update = $bdd->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?');
        $update->execute([$mot_de_passe_hache, $id]);

        echo "Mot de passe mis à jour pour ID $id.<br>";
    }
} catch (Exception $e) {
    die('Erreur : ' . $e->getMessage());
}
?>