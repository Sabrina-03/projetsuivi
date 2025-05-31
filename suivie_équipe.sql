- Base de données : `suivie_équipe`

-- Structure de la table `utilisateurs`

CREATE TABLE `utilisateurs` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `mot_de_passe` varchar(255) NOT NULL,
  `role` varchar(50) DEFAULT 'admin'
);

-- Déchargement des données de la table `utilisateurs`
INSERT INTO `utilisateurs` (`id`, `email`, `mot_de_passe`, `role`) VALUES
(2, 'Sabrina@gmail.com', 'Sabrina123', 'admin');

-- Index pour la table `utilisateurs`
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);
  
-- AUTO_INCREMENT pour la table `utilisateurs`
  ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;



-- Structure de la table `collaborateurs`
CREATE TABLE `collaborateurs` (
  `id` int(11) NOT NULL,
  `nom` varchar(50) DEFAULT NULL,
  `prenom` varchar(50) DEFAULT NULL,
  `niveau` varchar(20) DEFAULT NULL,
  `specialite` varchar(50) DEFAULT NULL,
  `debut_prestation` date DEFAULT NULL,
  `tjm` double DEFAULT NULL,
  `prime` double DEFAULT NULL,
  `augmentation_individuelle` double DEFAULT NULL,
  `statut` varchar(10) DEFAULT NULL,
  `site_client` varchar(50) DEFAULT NULL,
  `donneur_ordre` varchar(50) DEFAULT NULL,
  `taux_2021` double DEFAULT NULL,
  `taux_2022` double DEFAULT NULL,
  `taux_2023` double DEFAULT NULL,
  `taux_2024` double DEFAULT NULL,
  `taux_2025` double DEFAULT NULL,
  `ai_2021` double DEFAULT NULL,
  `ai_2022` double DEFAULT NULL,
  `ai_2023` double DEFAULT NULL,
  `ai_2024` double DEFAULT NULL,
  `ai_2025` double DEFAULT NULL,
  `annee` int(11) NOT NULL DEFAULT 0,
  `taux_2026` decimal(5,2) DEFAULT NULL,
  `ai_2026` decimal(5,2) DEFAULT NULL,
  `taux_2027` decimal(5,2) DEFAULT NULL,
  `ai_2027` decimal(5,2) DEFAULT NULL,
  `taux_2028` decimal(5,2) DEFAULT NULL,
  `ai_2028` decimal(5,2) DEFAULT NULL,
  `masque` tinyint(1) DEFAULT 0
);

-- Déchargement des données de la table `collaborateurs`
INSERT INTO `collaborateurs` (`id`, `nom`, `prenom`, `niveau`, `specialite`, `debut_prestation`, `tjm`, `prime`, `augmentation_individuelle`, `statut`, `site_client`, `donneur_ordre`, `taux_2021`, `taux_2022`, `taux_2023`, `taux_2024`, `taux_2025`, `ai_2021`, `ai_2022`, `ai_2023`, `ai_2024`, `ai_2025`, `annee`, `taux_2026`, `ai_2026`, `taux_2027`, `ai_2027`, `taux_2028`, `ai_2028`, `masque`) VALUES()

-- Index pour la table `collaborateurs`
ALTER TABLE `collaborateurs`
  ADD PRIMARY KEY (`id`);
 
  -- AUTO_INCREMENT pour la table `collaborateurs`
ALTER TABLE `collaborateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;
COMMIT;



-- Structure de la table `affectations`
CREATE TABLE `affectations` (
  `id` int(11) NOT NULL,
  `collaborateur` varchar(100) DEFAULT NULL,
  `mois` varchar(7) DEFAULT NULL,
  `jours_affectes` int(11) DEFAULT NULL,
  `date_saisie` timestamp NOT NULL DEFAULT current_timestamp()
);

-- Déchargement des données de la table `affectations`
INSERT INTO `affectations` (`id`, `collaborateur`, `mois`, `jours_affectes`, `date_saisie`) VALUES()

-- Index pour la table `affectations`
ALTER TABLE `affectations`
  ADD PRIMARY KEY (`id`);

  -- AUTO_INCREMENT pour la table `affectations`
ALTER TABLE `affectations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=307;
COMMIT;



-- Structure de la table `commandes`
CREATE TABLE `commandes` (
  `id` int(11) NOT NULL,
  `collaborateur_id` int(11) NOT NULL,
  `nom_commande` varchar(100) NOT NULL,
  `jours_total` int(11) NOT NULL,
  `annee` int(11) DEFAULT NULL
);

ALTER TABLE `commandes` ADD `client` VARCHAR(100) DEFAULT 'TOUS';
ALTER TABLE commandes ADD COLUMN active TINYINT(1) DEFAULT 1;


-- Déchargement des données de la table `commandes`
INSERT INTO `commandes` (`id`, `collaborateur_id`, `nom_commande`, `jours_total`, `annee`) VALUES()

-- Index pour la table `commandes`
ALTER TABLE `commandes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `collaborateur_id` (`collaborateur_id`);

  -- AUTO_INCREMENT pour la table `commandes`
ALTER TABLE `commandes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=294;

-- Contraintes pour la table `commandes`
ALTER TABLE `commandes`
  ADD CONSTRAINT `commandes_ibfk_1` FOREIGN KEY (`collaborateur_id`) REFERENCES `collaborateurs` (`id`) ON DELETE CASCADE;
COMMIT;



-- Structure de la table `consommations`
CREATE TABLE `consommations` (
  `id` int(11) NOT NULL,
  `commande_id` int(11) NOT NULL,
  `mois` varchar(7) NOT NULL,
  `jours_consummes` int(11) DEFAULT 0
);

-- Déchargement des données de la table `consommations`
INSERT INTO `consommations` (`id`, `commande_id`, `mois`, `jours_consummes`) VALUES()

-- Index pour la table `consommations`
ALTER TABLE `consommations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `commande_id` (`commande_id`);

-- AUTO_INCREMENT pour la table `consommations`
ALTER TABLE `consommations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7637;

-- Contraintes pour la table `consommations`
ALTER TABLE `consommations`
  ADD CONSTRAINT `consommations_ibfk_1` FOREIGN KEY (`commande_id`) REFERENCES `commandes` (`id`) ON DELETE CASCADE;
COMMIT;


CREATE TABLE devis (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero_devis VARCHAR(50) NOT NULL,
    revision VARCHAR(10) NOT NULL,
    date DATE NOT NULL,
    client VARCHAR(100) NOT NULL,
    libelle TEXT NOT NULL,
    progression VARCHAR(10) NOT NULL,
    redacteur VARCHAR(100) NOT NULL,
    destinataire VARCHAR(100) NOT NULL,
    realise_par VARCHAR(100) NOT NULL,
    temps VARCHAR(50) NOT NULL,
    fichier_pdf VARCHAR(255) NOT NULL -- Chemin vers le fichier PDF
);