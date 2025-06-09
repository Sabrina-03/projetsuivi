<?php
try {
    // Connexion à la base de données avec PDO
    $bdd = new PDO('mysql:host=localhost;dbname=suivie_équipe;charset=utf8', 'root', '');
    
    // Active le mode exception pour mieux gérer les erreurs
    $bdd->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Récupère tous les identifiants et mots de passe des utilisateurs
    $requete = $bdd->query('SELECT id, mot_de_passe FROM utilisateurs');
    $utilisateurs = $requete->fetchAll(PDO::FETCH_ASSOC);

    // Parcours de chaque utilisateur
    foreach ($utilisateurs as $utilisateur) {
        $id = $utilisateur['id'];
        $mot_de_passe = $utilisateur['mot_de_passe'];

        // Vérifie si le mot de passe a déjà été haché avec l'algorithme actuel
        if (!password_needs_rehash($mot_de_passe, PASSWORD_DEFAULT)) {
            continue; // Si déjà bien haché, on passe au suivant
        }

        // Sinon, on le rehash avec le bon algorithme
        $mot_de_passe_hache = password_hash($mot_de_passe, PASSWORD_DEFAULT);

        // Mise à jour du mot de passe dans la base
        $update = $bdd->prepare('UPDATE utilisateurs SET mot_de_passe = ? WHERE id = ?');
        $update->execute([$mot_de_passe_hache, $id]);

        // Affiche un message pour dire que le mot de passe a été mis à jour
        echo "Mot de passe mis à jour pour ID $id.<br>";
    }
} catch (Exception $e) {
    // Affiche une erreur en cas de problème de connexion ou de requête
    die('Erreur : ' . $e->getMessage());
}
?>
