-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1
-- Généré le : sam. 28 fév. 2026 à 22:42
-- Version du serveur : 10.4.32-MariaDB
-- Version de PHP : 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `agrifund`
--

-- --------------------------------------------------------

--
-- Structure de la table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `admin`
--

INSERT INTO `admin` (`id`, `utilisateur_id`) VALUES
(2, 3);

-- --------------------------------------------------------

--
-- Structure de la table `agriculteur`
--

CREATE TABLE `agriculteur` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `adresseferme` varchar(255) DEFAULT NULL,
  `superficieferme` decimal(10,2) DEFAULT NULL,
  `typeCulture` varchar(100) DEFAULT NULL,
  `statuscompte` varchar(50) DEFAULT 'en_attente',
  `compteverifie` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `agriculteur`
--

INSERT INTO `agriculteur` (`id`, `utilisateur_id`, `adresseferme`, `superficieferme`, `typeCulture`, `statuscompte`, `compteverifie`) VALUES
(1, 2, 'boukrim', 150.00, 'olive', 'actif', 1),
(3, 6, 'el haouaria', 180.00, 'Céréales', 'en_attente', 0),
(4, 7, 'tunis', 140.00, 'Maraîchage', 'actif', 1);

-- --------------------------------------------------------

--
-- Structure de la table `analyse_risque_agricole`
--

CREATE TABLE `analyse_risque_agricole` (
  `id` int(11) NOT NULL,
  `banque_id` int(11) NOT NULL,
  `agriculteur_id` int(11) DEFAULT NULL,
  `region` varchar(100) DEFAULT NULL,
  `score_risque` int(11) DEFAULT NULL COMMENT 'Score de 0 à 100',
  `niveau_risque` varchar(20) DEFAULT NULL,
  `facteurs_risque` text DEFAULT NULL,
  `recommandations` text DEFAULT NULL,
  `date_analyse` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `analyse_risque_agricole`
--

INSERT INTO `analyse_risque_agricole` (`id`, `banque_id`, `agriculteur_id`, `region`, `score_risque`, `niveau_risque`, `facteurs_risque`, `recommandations`, `date_analyse`) VALUES
(17, 1, NULL, 'Tunis', 10, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 16:39:50'),
(18, 1, NULL, 'Tunis', 10, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 16:42:04'),
(19, 1, NULL, 'Tunis, Tunisie', 23, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 16:53:34'),
(20, 1, NULL, 'Nabeul, Al Hadaek, Délégation Nabeul', 10, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 16:59:05'),
(21, 1, NULL, 'Boukrim, Bou Krim, Délégation El Haouaria', 10, 'faible', '• Risque d\'inondation (179,5 mm)\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 16:59:46'),
(22, 1, NULL, 'Tunis, Tunisie', 22, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 17:08:27'),
(23, 1, NULL, 'Tunisie', 22, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 17:19:45'),
(24, 1, NULL, 'Gouvernorat Nabeul, Tunisie', 22, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 17:23:05'),
(25, 1, NULL, 'Gouvernorat Ben Arous, Tunisie', 32, 'moyen', '• Aucun facteur de risque majeur identifié\n', '🟡 RISQUE MODÉRÉ\n• Conditions globalement acceptables\n• Assurance récolte recommandée\n• Suivi régulier des conditions météo conseillé\n• Garanties standards suffisantes\n', '2026-02-21 17:28:25'),
(26, 1, NULL, 'Tunis, Tunisie', 22, 'faible', '• Aucun facteur de risque majeur identifié\n', '✅ CONDITIONS FAVORABLES\n• Risque financier faible\n• Conditions optimales pour les cultures\n• Financement peut être accordé avec garanties minimales\n• Suivi trimestriel suffisant\n', '2026-02-21 18:08:15'),
(27, 1, NULL, 'Bizerte, Gouvernorat Bizerte, Tunisie', 25, 'moyen', '• Aucun facteur de risque majeur identifié\n', '🟡 RISQUE MODÉRÉ\n• Conditions globalement acceptables\n• Assurance récolte recommandée\n• Suivi régulier des conditions météo conseillé\n• Garanties standards suffisantes\n', '2026-02-23 06:37:22');

-- --------------------------------------------------------

--
-- Structure de la table `banque`
--

CREATE TABLE `banque` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `codebanque` varchar(50) NOT NULL,
  `addresseSiege` varchar(255) DEFAULT NULL,
  `representantLegal` varchar(255) DEFAULT NULL,
  `adresseAgence` varchar(255) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `siteweb` varchar(255) DEFAULT NULL,
  `statusCompte` varchar(50) DEFAULT 'en_attente',
  `compteVerfiee` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `banque`
--

INSERT INTO `banque` (`id`, `utilisateur_id`, `codebanque`, `addresseSiege`, `representantLegal`, `adresseAgence`, `logo`, `siteweb`, `statusCompte`, `compteVerfiee`) VALUES
(1, 4, '478c', 'tunis', 'sirine ben rhouma', 'el haouaria', NULL, 'www.stb.com', 'actif', 1);

-- --------------------------------------------------------

--
-- Structure de la table `capteur`
--

CREATE TABLE `capteur` (
  `id_capteur` int(11) NOT NULL,
  `typeCapteur` varchar(50) NOT NULL,
  `localisation` varchar(100) NOT NULL,
  `statut` varchar(20) NOT NULL DEFAULT 'ACTIF',
  `date_installation` timestamp NOT NULL DEFAULT current_timestamp(),
  `idproject` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `capteur`
--

INSERT INTO `capteur` (`id_capteur`, `typeCapteur`, `localisation`, `statut`, `date_installation`, `idproject`) VALUES
(1, 'Humidité du sol', 'Zone Test', 'INACTIF', '2026-02-21 13:28:23', 1),
(4, 'pH du sol', 'Zone Test', 'INACTIF', '2026-02-21 13:28:23', 1),
(5, 'Température', 'Zone Test', 'INACTIF', '2026-02-21 13:29:32', 1),
(6, 'Humidité du sol', 'Zone Test', 'INACTIF', '2026-02-21 13:29:32', 1),
(7, 'pH du sol', 'Zone Test', 'INACTIF', '2026-02-21 13:29:32', 1),
(8, 'Température', 'Zone Test', 'INACTIF', '2026-02-21 13:31:16', 1),
(9, 'Humidité du sol', 'Zone Test', 'INACTIF', '2026-02-21 13:31:16', 1),
(10, 'pH du sol', 'Zone Test', 'INACTIF', '2026-02-21 13:31:16', 1),
(11, 'Température', 'Zone Test 2', 'INACTIF', '2026-02-22 10:25:16', 2),
(12, 'Humidité du sol', 'Zone Test 2', 'INACTIF', '2026-02-22 10:25:16', 2),
(13, 'pH du sol', 'Zone Test 2', 'INACTIF', '2026-02-22 10:25:16', 2),
(17, 'Température', 'Zone Test 4', 'INACTIF', '2026-02-22 10:25:16', 4),
(18, 'Humidité du sol', 'Zone Test 4', 'INACTIF', '2026-02-22 10:25:16', 4),
(19, 'pH du sol', 'Zone Test 4', 'INACTIF', '2026-02-22 10:25:16', 4),
(20, 'Température', 'Zone Test 30', 'INACTIF', '2026-02-22 10:25:16', 30),
(21, 'Humidité du sol', 'Zone Test 30', 'INACTIF', '2026-02-22 10:25:16', 30),
(22, 'pH du sol', 'Zone Test 30', 'INACTIF', '2026-02-22 10:25:16', 30),
(23, 'Température', 'Zone Test 3', 'INACTIF', '2026-02-22 10:33:27', 3),
(24, 'Humidité du sol', 'Zone Test 3', 'INACTIF', '2026-02-22 10:33:28', 3),
(25, 'pH du sol', 'Zone Test 3', 'INACTIF', '2026-02-22 10:33:28', 3),
(26, 'humidité', 'tunis', 'INACTIF', '2026-02-27 10:57:56', 30),
(27, 'Température', 'Ariana', 'INACTIF', '2026-02-27 20:28:43', 32),
(28, 'temp', 'sfax', 'ACTIF', '2026-02-28 11:28:02', 33),
(29, 'temp', 'gabes', 'ACTIF', '2026-02-28 17:57:45', 31);

-- --------------------------------------------------------

--
-- Structure de la table `code2fa`
--

CREATE TABLE `code2fa` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `code` varchar(6) NOT NULL,
  `date_creation` datetime DEFAULT current_timestamp(),
  `date_expiration` datetime NOT NULL,
  `est_utilise` tinyint(1) DEFAULT 0,
  `date_utilisation` datetime DEFAULT NULL,
  `type_envoi` enum('email','sms') DEFAULT 'email'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `code2fa`
--

INSERT INTO `code2fa` (`id`, `utilisateur_id`, `code`, `date_creation`, `date_expiration`, `est_utilise`, `date_utilisation`, `type_envoi`) VALUES
(1, 2, '951310', '2026-02-09 17:13:44', '2026-02-09 17:18:44', 1, '2026-02-09 17:14:13', 'email'),
(2, 2, '525929', '2026-02-09 17:16:11', '2026-02-09 17:21:11', 1, '2026-02-09 17:16:51', 'sms'),
(3, 2, '230478', '2026-02-11 19:51:26', '2026-02-11 19:56:26', 1, '2026-02-11 19:52:10', 'email'),
(4, 2, '129165', '2026-02-11 20:24:31', '2026-02-11 20:29:31', 1, '2026-02-11 20:24:53', 'email'),
(5, 2, '192562', '2026-02-12 10:47:03', '2026-02-12 10:52:03', 0, NULL, 'email'),
(6, 2, '762690', '2026-02-12 10:51:16', '2026-02-12 10:56:16', 0, NULL, 'email'),
(7, 2, '354680', '2026-02-12 10:52:06', '2026-02-12 10:57:06', 1, '2026-02-12 10:52:20', 'email'),
(8, 3, '467889', '2026-02-13 15:18:42', '2026-02-13 15:23:42', 0, NULL, 'email'),
(9, 7, '603035', '2026-02-16 09:39:39', '2026-02-16 09:44:39', 1, '2026-02-16 09:39:51', 'email'),
(10, 2, '372882', '2026-02-21 11:42:59', '2026-02-21 11:47:59', 1, '2026-02-21 11:43:23', 'email'),
(11, 2, '745036', '2026-02-22 17:43:29', '2026-02-22 17:48:29', 1, '2026-02-22 17:43:52', 'email'),
(12, 2, '434717', '2026-02-26 13:49:38', '2026-02-26 13:54:38', 0, NULL, 'email'),
(13, 2, '189017', '2026-02-26 13:51:49', '2026-02-26 13:56:49', 1, '2026-02-26 13:52:51', 'email'),
(14, 2, '603961', '2026-02-26 13:59:35', '2026-02-26 14:04:35', 0, NULL, 'email'),
(15, 2, '156274', '2026-02-26 14:00:56', '2026-02-26 14:05:56', 1, '2026-02-26 14:01:30', 'email'),
(16, 2, '642757', '2026-02-26 21:36:42', '2026-02-26 21:41:42', 1, '2026-02-26 21:37:16', 'email'),
(17, 2, '910718', '2026-02-26 21:41:24', '2026-02-26 21:46:24', 1, '2026-02-26 21:41:53', 'email'),
(18, 2, '931312', '2026-02-26 21:50:15', '2026-02-26 21:55:15', 0, NULL, 'email'),
(19, 2, '928854', '2026-02-27 11:44:13', '2026-02-27 11:49:13', 1, '2026-02-27 11:45:25', 'email'),
(20, 2, '646752', '2026-02-27 11:48:42', '2026-02-27 11:53:42', 1, '2026-02-27 11:48:54', 'email'),
(21, 2, '900317', '2026-02-27 11:53:34', '2026-02-27 11:58:34', 1, '2026-02-27 11:53:45', 'email'),
(22, 2, '235085', '2026-02-27 15:47:55', '2026-02-27 15:52:55', 1, '2026-02-27 15:48:10', 'email'),
(23, 2, '129686', '2026-02-27 18:46:58', '2026-02-27 18:51:58', 1, '2026-02-27 18:47:22', 'email'),
(24, 2, '561038', '2026-02-27 22:17:57', '2026-02-27 22:22:57', 1, '2026-02-27 22:18:25', 'email');

-- --------------------------------------------------------

--
-- Structure de la table `conversation`
--

CREATE TABLE `conversation` (
  `id` int(11) NOT NULL,
  `utilisateur1_id` int(11) NOT NULL,
  `utilisateur2_id` int(11) NOT NULL,
  `utilisateur_min` int(11) GENERATED ALWAYS AS (least(`utilisateur1_id`,`utilisateur2_id`)) STORED,
  `utilisateur_max` int(11) GENERATED ALWAYS AS (greatest(`utilisateur1_id`,`utilisateur2_id`)) STORED,
  `date_creation` datetime DEFAULT current_timestamp(),
  `derniere_activite` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `conversation`
--

INSERT INTO `conversation` (`id`, `utilisateur1_id`, `utilisateur2_id`, `date_creation`, `derniere_activite`) VALUES
(1, 2, 3, '2026-02-08 14:20:37', '2026-02-27 14:41:27'),
(2, 3, 4, '2026-02-10 09:42:37', '2026-02-15 21:17:16'),
(4, 3, 7, '2026-02-16 09:32:06', '2026-02-16 09:32:12'),
(5, 2, 4, '2026-02-26 21:43:06', '2026-02-26 21:43:36');

-- --------------------------------------------------------

--
-- Structure de la table `decisionfinanciere`
--

CREATE TABLE `decisionfinanciere` (
  `idDecision` int(11) NOT NULL,
  `statut` varchar(50) NOT NULL,
  `justification` text NOT NULL,
  `dateDecision` datetime NOT NULL,
  `idEvaluation` int(11) NOT NULL,
  `banqueId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `decisionfinanciere`
--

INSERT INTO `decisionfinanciere` (`idDecision`, `statut`, `justification`, `dateDecision`, `idEvaluation`, `banqueId`) VALUES
(2, 'En attente', 'hkglykbjkjk', '2026-02-27 00:00:00', 15, NULL),
(3, 'Approuvé', 'bonne projet', '2026-02-28 00:00:00', 17, NULL),
(4, 'Approuvé', 'bonne projet vraiment\nbonne projet vraiment\nbonne projet vraiment\nbonne projet vraiment', '2026-02-28 00:00:00', 18, 4),
(5, 'Approuvé', 'vraiment c\'est un bon projet j\'aime bien\nvraiment c\'est un bon projet j\'aime bien\nvraiment c\'est un bon projet j\'aime bien', '2026-02-28 00:00:00', 19, 4),
(7, 'Rejeté', 'c\'est pas un bon projet il faut améliorer l\'environnement', '2026-02-28 00:00:00', 21, 4);

--
-- Déclencheurs `decisionfinanciere`
--
DELIMITER $$
CREATE TRIGGER `sync_project_status_on_insert` AFTER INSERT ON `decisionfinanciere` FOR EACH ROW BEGIN
  UPDATE projectagricole
  SET statut = CASE
    WHEN NEW.statut = 'Approuvé' THEN 'accepte'
    WHEN NEW.statut = 'Rejeté'   THEN 'refuse'
    ELSE 'en cours'
  END
  WHERE idproject = (
    SELECT idProjet FROM evaluationrisque WHERE idEvaluation = NEW.idEvaluation
  );
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `sync_project_status_on_update` AFTER UPDATE ON `decisionfinanciere` FOR EACH ROW BEGIN
  UPDATE projectagricole
  SET statut = CASE
    WHEN NEW.statut = 'Approuvé' THEN 'accepte'
    WHEN NEW.statut = 'Rejeté'   THEN 'refuse'
    ELSE 'en cours'
  END
  WHERE idproject = (
    SELECT idProjet FROM evaluationrisque WHERE idEvaluation = NEW.idEvaluation
  );
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `document`
--

CREATE TABLE `document` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `type_document` varchar(50) NOT NULL,
  `chemin_fichier` varchar(255) NOT NULL,
  `taille` int(11) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp(),
  `date_expiration` date DEFAULT NULL,
  `statut` varchar(20) DEFAULT 'en_attente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `document`
--

INSERT INTO `document` (`id`, `utilisateur_id`, `nom`, `type_document`, `chemin_fichier`, `taille`, `date_upload`, `date_expiration`, `statut`) VALUES
(1, 2, 'cin', 'cni', 'C:\\Users\\SS TECH\\Pictures\\Screenshots', 1024, '2026-02-07 10:41:11', NULL, 'valide'),
(2, 2, 'diplome', 'certificat_bio', 'C:\\Users\\SS TECH\\Desktop\\esprit\\Doc1.docx', 1024, '2026-02-07 11:21:19', NULL, 'valide'),
(3, 2, 'diplome', 'certificat_bio', 'uploads/documents/doc_2_certificat_bio_20260207_171450.pdf', 23785, '2026-02-07 17:14:50', NULL, 'rejete'),
(5, 7, 'cin', 'cni', 'uploads/documents/doc_7_cni_20260216_093048.pdf', 912120, '2026-02-16 09:30:48', NULL, 'rejete'),
(6, 6, 'aaa', 'passeport', 'uploads/documents/doc_6_passeport_20260222_192628.pdf', 4522, '2026-02-22 19:26:28', NULL, 'en_attente');

-- --------------------------------------------------------

--
-- Structure de la table `donnees_satellite`
--

CREATE TABLE `donnees_satellite` (
  `id` int(11) NOT NULL,
  `agriculteur_id` int(11) DEFAULT NULL,
  `latitude` double NOT NULL,
  `longitude` double NOT NULL,
  `date_mesure` date NOT NULL,
  `ndvi` double DEFAULT NULL COMMENT 'Indice de végétation normalisé (-1 à 1)',
  `temperature_moyenne` double DEFAULT NULL COMMENT 'Température moyenne en °C',
  `precipitation` double DEFAULT NULL COMMENT 'Précipitations en mm',
  `humidite` double DEFAULT NULL COMMENT 'Humidité relative en %',
  `indice_secheresse` double DEFAULT NULL COMMENT 'Indice de sécheresse (0-100)',
  `risque_agricole` varchar(20) DEFAULT NULL COMMENT 'faible, moyen, eleve, critique',
  `donnees_brutes` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL COMMENT 'Données JSON brutes de l''API' CHECK (json_valid(`donnees_brutes`)),
  `date_creation` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `donnees_satellite`
--

INSERT INTO `donnees_satellite` (`id`, `agriculteur_id`, `latitude`, `longitude`, `date_mesure`, `ndvi`, `temperature_moyenne`, `precipitation`, `humidite`, `indice_secheresse`, `risque_agricole`, `donnees_brutes`, `date_creation`) VALUES
(1, NULL, 36.8065, 10.1815, '2026-02-14', 0.7, 13.24107142857143, 143.63000000000005, 78.41071428571429, 20, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.181,36.806,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.64,\"20260116\":13.8,\"20260117\":13.54,\"20260118\":12.85,\"20260119\":13.97,\"20260120\":12.47,\"20260121\":11.95,\"20260122\":11.98,\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94},\"PRECTOTCORR\":{\"20260115\":0.01,\"20260116\":6.97,\"20260117\":0.34,\"20260118\":1.55,\"20260119\":15.91,\"20260120\":51.01,\"20260121\":11.23,\"20260122\":0.68,\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11},\"RH2M\":{\"20260115\":72.31,\"20260116\":91.1,\"20260117\":81.35,\"20260118\":81.03,\"20260119\":90.39,\"20260120\":95.22,\"20260121\":89.69,\"20260122\":82.1,\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.606,\"process\":0.01}}', '2026-02-21 11:12:09'),
(2, NULL, 36.8101, 10.0863, '2026-02-14', 0.7, 13.24107142857143, 143.63000000000005, 78.41071428571429, 20, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.086,36.81,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.64,\"20260116\":13.8,\"20260117\":13.54,\"20260118\":12.85,\"20260119\":13.97,\"20260120\":12.47,\"20260121\":11.95,\"20260122\":11.98,\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94},\"PRECTOTCORR\":{\"20260115\":0.01,\"20260116\":6.97,\"20260117\":0.34,\"20260118\":1.55,\"20260119\":15.91,\"20260120\":51.01,\"20260121\":11.23,\"20260122\":0.68,\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11},\"RH2M\":{\"20260115\":72.31,\"20260116\":91.1,\"20260117\":81.35,\"20260118\":81.03,\"20260119\":90.39,\"20260120\":95.22,\"20260121\":89.69,\"20260122\":82.1,\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.296,\"process\":0.01}}', '2026-02-21 11:13:37'),
(3, NULL, 36.8101, 10.0863, '2026-02-14', 0.7, 13.24107142857143, 143.63000000000005, 78.41071428571429, 20, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.086,36.81,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.64,\"20260116\":13.8,\"20260117\":13.54,\"20260118\":12.85,\"20260119\":13.97,\"20260120\":12.47,\"20260121\":11.95,\"20260122\":11.98,\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94},\"PRECTOTCORR\":{\"20260115\":0.01,\"20260116\":6.97,\"20260117\":0.34,\"20260118\":1.55,\"20260119\":15.91,\"20260120\":51.01,\"20260121\":11.23,\"20260122\":0.68,\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11},\"RH2M\":{\"20260115\":72.31,\"20260116\":91.1,\"20260117\":81.35,\"20260118\":81.03,\"20260119\":90.39,\"20260120\":95.22,\"20260121\":89.69,\"20260122\":82.1,\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.288,\"process\":0.01}}', '2026-02-21 11:13:46'),
(4, NULL, 33.9197, 8.1339, '2026-02-14', 0.3, 14.097499999999998, 7.809999999999999, 51.71392857142856, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[8.134,33.92,88.15]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":13.77,\"20260116\":12.12,\"20260117\":11.6,\"20260118\":11.62,\"20260119\":13.58,\"20260120\":12.27,\"20260121\":11.15,\"20260122\":9.91,\"20260123\":10.44,\"20260124\":11.49,\"20260125\":10.14,\"20260126\":13.14,\"20260127\":14.88,\"20260128\":13.54,\"20260129\":16.04,\"20260130\":17.17,\"20260131\":14.77,\"20260201\":12.45,\"20260202\":12.97,\"20260203\":14.74,\"20260204\":13.78,\"20260205\":18.08,\"20260206\":18.04,\"20260207\":16.41,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":18.18,\"20260212\":19.32,\"20260213\":17.02,\"20260214\":16.11},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.0,\"20260117\":0.0,\"20260118\":0.01,\"20260119\":0.22,\"20260120\":2.52,\"20260121\":0.02,\"20260122\":1.77,\"20260123\":0.0,\"20260124\":0.31,\"20260125\":0.0,\"20260126\":0.55,\"20260127\":0.0,\"20260128\":2.0,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.26,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.1,\"20260204\":0.05,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":49.73,\"20260116\":62.4,\"20260117\":62.07,\"20260118\":57.79,\"20260119\":63.57,\"20260120\":63.62,\"20260121\":67.22,\"20260122\":72.15,\"20260123\":63.15,\"20260124\":50.63,\"20260125\":53.03,\"20260126\":54.58,\"20260127\":55.28,\"20260128\":45.23,\"20260129\":57.11,\"20260130\":57.99,\"20260131\":44.9,\"20260201\":48.46,\"20260202\":40.95,\"20260203\":30.76,\"20260204\":42.33,\"20260205\":47.35,\"20260206\":39.78,\"20260207\":40.55,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":61.4,\"20260212\":43.67,\"20260213\":38.44,\"20260214\":33.85}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.306,\"process\":0.01}}', '2026-02-21 11:13:52'),
(5, NULL, 36.8065, 10.1815, '2026-02-14', 0.7, 13.24107142857143, 143.63000000000005, 78.41071428571429, 20, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.181,36.806,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.64,\"20260116\":13.8,\"20260117\":13.54,\"20260118\":12.85,\"20260119\":13.97,\"20260120\":12.47,\"20260121\":11.95,\"20260122\":11.98,\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94},\"PRECTOTCORR\":{\"20260115\":0.01,\"20260116\":6.97,\"20260117\":0.34,\"20260118\":1.55,\"20260119\":15.91,\"20260120\":51.01,\"20260121\":11.23,\"20260122\":0.68,\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11},\"RH2M\":{\"20260115\":72.31,\"20260116\":91.1,\"20260117\":81.35,\"20260118\":81.03,\"20260119\":90.39,\"20260120\":95.22,\"20260121\":89.69,\"20260122\":82.1,\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.286,\"process\":0.01}}', '2026-02-21 11:14:17'),
(6, NULL, 34.7406, 10.7603, '2026-02-14', 0.7, 15.249642857142858, 21.569999999999997, 65.41607142857144, 70, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.76,34.741,34.93]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":14.2,\"20260116\":14.07,\"20260117\":13.32,\"20260118\":14.71,\"20260119\":15.09,\"20260120\":13.22,\"20260121\":12.53,\"20260122\":13.02,\"20260123\":13.51,\"20260124\":14.79,\"20260125\":13.38,\"20260126\":13.99,\"20260127\":14.61,\"20260128\":15.24,\"20260129\":16.04,\"20260130\":17.3,\"20260131\":15.38,\"20260201\":14.48,\"20260202\":14.72,\"20260203\":15.21,\"20260204\":15.87,\"20260205\":17.84,\"20260206\":18.32,\"20260207\":15.91,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":18.13,\"20260212\":18.7,\"20260213\":16.75,\"20260214\":16.66},\"PRECTOTCORR\":{\"20260115\":0.06,\"20260116\":0.28,\"20260117\":0.0,\"20260118\":0.04,\"20260119\":0.03,\"20260120\":9.79,\"20260121\":3.11,\"20260122\":1.47,\"20260123\":0.01,\"20260124\":0.1,\"20260125\":0.35,\"20260126\":1.32,\"20260127\":0.0,\"20260128\":1.94,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":2.43,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":0.31,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.02,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.31},\"RH2M\":{\"20260115\":85.4,\"20260116\":77.96,\"20260117\":73.14,\"20260118\":79.3,\"20260119\":72.66,\"20260120\":87.48,\"20260121\":78.62,\"20260122\":73.22,\"20260123\":65.85,\"20260124\":61.96,\"20260125\":62.17,\"20260126\":59.62,\"20260127\":66.27,\"20260128\":58.44,\"20260129\":60.26,\"20260130\":67.81,\"20260131\":63.23,\"20260201\":59.93,\"20260202\":56.84,\"20260203\":60.65,\"20260204\":51.1,\"20260205\":58.95,\"20260206\":54.13,\"20260207\":75.74,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":65.5,\"20260212\":52.56,\"20260213\":49.46,\"20260214\":53.4}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.295,\"process\":0.01}}', '2026-02-21 11:16:01'),
(7, NULL, 34.7406, 10.7603, '2026-02-14', 0.4, 17.56, 0.33, 55.230000000000004, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.76,34.741,34.93]},\"properties\":{\"parameter\":{\"T2M\":{\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":18.13,\"20260212\":18.7,\"20260213\":16.75,\"20260214\":16.66},\"PRECTOTCORR\":{\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.02,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.31},\"RH2M\":{\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":65.5,\"20260212\":52.56,\"20260213\":49.46,\"20260214\":53.4}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260208\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.295,\"process\":0.01}}', '2026-02-21 11:16:38'),
(8, NULL, 36.8065, 10.1815, '2026-02-14', 0.4, 15.052499999999998, 6.770000000000001, 73.75500000000001, 70, 'moyen', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.181,36.806,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94},\"PRECTOTCORR\":{\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11},\"RH2M\":{\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260208\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.543,\"process\":0.01}}', '2026-02-21 14:04:59'),
(9, NULL, 33.7044, 8.969, '2026-02-14', 0.3, 14.799285714285716, 7.8999999999999995, 49.718571428571416, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[8.969,33.704,41.01]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.76,\"20260116\":12.11,\"20260117\":11.39,\"20260118\":12.18,\"20260119\":13.36,\"20260120\":11.6,\"20260121\":10.97,\"20260122\":10.74,\"20260123\":11.15,\"20260124\":13.11,\"20260125\":11.0,\"20260126\":13.36,\"20260127\":15.2,\"20260128\":15.39,\"20260129\":17.98,\"20260130\":18.18,\"20260131\":15.75,\"20260201\":13.21,\"20260202\":13.59,\"20260203\":17.01,\"20260204\":16.07,\"20260205\":19.5,\"20260206\":18.6,\"20260207\":17.31,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":18.81,\"20260212\":19.81,\"20260213\":17.64,\"20260214\":16.6},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.01,\"20260117\":0.0,\"20260118\":0.0,\"20260119\":0.05,\"20260120\":4.05,\"20260121\":0.08,\"20260122\":2.46,\"20260123\":0.0,\"20260124\":0.19,\"20260125\":0.0,\"20260126\":0.78,\"20260127\":0.0,\"20260128\":0.05,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.21,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.02,\"20260204\":0.0,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":53.04,\"20260116\":59.1,\"20260117\":64.78,\"20260118\":55.08,\"20260119\":69.54,\"20260120\":72.27,\"20260121\":69.95,\"20260122\":68.93,\"20260123\":58.11,\"20260124\":42.48,\"20260125\":49.36,\"20260126\":51.16,\"20260127\":56.64,\"20260128\":33.25,\"20260129\":51.58,\"20260130\":54.03,\"20260131\":43.08,\"20260201\":45.37,\"20260202\":38.68,\"20260203\":24.53,\"20260204\":34.82,\"20260205\":38.3,\"20260206\":39.75,\"20260207\":44.62,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":59.36,\"20260212\":44.06,\"20260213\":37.89,\"20260214\":32.36}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.29,\"process\":0.01}}', '2026-02-21 14:46:32'),
(10, NULL, 33.7044, 8.969, '2026-02-14', 0.3, 14.799285714285716, 7.8999999999999995, 49.718571428571416, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[8.969,33.704,41.01]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.76,\"20260116\":12.11,\"20260117\":11.39,\"20260118\":12.18,\"20260119\":13.36,\"20260120\":11.6,\"20260121\":10.97,\"20260122\":10.74,\"20260123\":11.15,\"20260124\":13.11,\"20260125\":11.0,\"20260126\":13.36,\"20260127\":15.2,\"20260128\":15.39,\"20260129\":17.98,\"20260130\":18.18,\"20260131\":15.75,\"20260201\":13.21,\"20260202\":13.59,\"20260203\":17.01,\"20260204\":16.07,\"20260205\":19.5,\"20260206\":18.6,\"20260207\":17.31,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":18.81,\"20260212\":19.81,\"20260213\":17.64,\"20260214\":16.6},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.01,\"20260117\":0.0,\"20260118\":0.0,\"20260119\":0.05,\"20260120\":4.05,\"20260121\":0.08,\"20260122\":2.46,\"20260123\":0.0,\"20260124\":0.19,\"20260125\":0.0,\"20260126\":0.78,\"20260127\":0.0,\"20260128\":0.05,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.21,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.02,\"20260204\":0.0,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":53.04,\"20260116\":59.1,\"20260117\":64.78,\"20260118\":55.08,\"20260119\":69.54,\"20260120\":72.27,\"20260121\":69.95,\"20260122\":68.93,\"20260123\":58.11,\"20260124\":42.48,\"20260125\":49.36,\"20260126\":51.16,\"20260127\":56.64,\"20260128\":33.25,\"20260129\":51.58,\"20260130\":54.03,\"20260131\":43.08,\"20260201\":45.37,\"20260202\":38.68,\"20260203\":24.53,\"20260204\":34.82,\"20260205\":38.3,\"20260206\":39.75,\"20260207\":44.62,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":59.36,\"20260212\":44.06,\"20260213\":37.89,\"20260214\":32.36}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.301,\"process\":0.01}}', '2026-02-21 14:46:36'),
(11, NULL, 32.9211, 10.4509, '2026-02-14', 0.3, 15.427142857142853, 4.97, 44.36, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.451,32.921,197.64]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":14.77,\"20260116\":13.83,\"20260117\":12.34,\"20260118\":13.09,\"20260119\":13.51,\"20260120\":11.25,\"20260121\":10.8,\"20260122\":12.45,\"20260123\":12.43,\"20260124\":15.66,\"20260125\":12.16,\"20260126\":12.46,\"20260127\":14.64,\"20260128\":16.57,\"20260129\":18.22,\"20260130\":18.51,\"20260131\":16.23,\"20260201\":13.13,\"20260202\":13.77,\"20260203\":17.03,\"20260204\":17.1,\"20260205\":19.74,\"20260206\":20.55,\"20260207\":17.97,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":19.17,\"20260212\":20.02,\"20260213\":17.72,\"20260214\":16.84},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.15,\"20260117\":0.0,\"20260118\":0.0,\"20260119\":0.0,\"20260120\":2.29,\"20260121\":1.01,\"20260122\":0.69,\"20260123\":0.03,\"20260124\":0.0,\"20260125\":0.06,\"20260126\":0.72,\"20260127\":0.01,\"20260128\":0.0,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.01,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":0.0,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":34.96,\"20260116\":50.01,\"20260117\":58.68,\"20260118\":47.07,\"20260119\":38.59,\"20260120\":74.92,\"20260121\":67.89,\"20260122\":59.75,\"20260123\":46.97,\"20260124\":31.88,\"20260125\":44.69,\"20260126\":48.75,\"20260127\":58.24,\"20260128\":27.61,\"20260129\":43.4,\"20260130\":52.54,\"20260131\":43.49,\"20260201\":41.01,\"20260202\":36.14,\"20260203\":23.85,\"20260204\":30.64,\"20260205\":32.39,\"20260206\":31.48,\"20260207\":48.07,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":56.54,\"20260212\":47.96,\"20260213\":32.8,\"20260214\":31.76}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.295,\"process\":0.01}}', '2026-02-21 14:46:41'),
(12, NULL, 32.9211, 10.4509, '2026-02-14', 0.3, 15.427142857142853, 4.97, 44.36, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.451,32.921,197.64]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":14.77,\"20260116\":13.83,\"20260117\":12.34,\"20260118\":13.09,\"20260119\":13.51,\"20260120\":11.25,\"20260121\":10.8,\"20260122\":12.45,\"20260123\":12.43,\"20260124\":15.66,\"20260125\":12.16,\"20260126\":12.46,\"20260127\":14.64,\"20260128\":16.57,\"20260129\":18.22,\"20260130\":18.51,\"20260131\":16.23,\"20260201\":13.13,\"20260202\":13.77,\"20260203\":17.03,\"20260204\":17.1,\"20260205\":19.74,\"20260206\":20.55,\"20260207\":17.97,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":19.17,\"20260212\":20.02,\"20260213\":17.72,\"20260214\":16.84},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.15,\"20260117\":0.0,\"20260118\":0.0,\"20260119\":0.0,\"20260120\":2.29,\"20260121\":1.01,\"20260122\":0.69,\"20260123\":0.03,\"20260124\":0.0,\"20260125\":0.06,\"20260126\":0.72,\"20260127\":0.01,\"20260128\":0.0,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.01,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":0.0,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":34.96,\"20260116\":50.01,\"20260117\":58.68,\"20260118\":47.07,\"20260119\":38.59,\"20260120\":74.92,\"20260121\":67.89,\"20260122\":59.75,\"20260123\":46.97,\"20260124\":31.88,\"20260125\":44.69,\"20260126\":48.75,\"20260127\":58.24,\"20260128\":27.61,\"20260129\":43.4,\"20260130\":52.54,\"20260131\":43.49,\"20260201\":41.01,\"20260202\":36.14,\"20260203\":23.85,\"20260204\":30.64,\"20260205\":32.39,\"20260206\":31.48,\"20260207\":48.07,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":56.54,\"20260212\":47.96,\"20260213\":32.8,\"20260214\":31.76}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.282,\"process\":0.01}}', '2026-02-21 14:46:43'),
(13, NULL, 32.9211, 10.4509, '2026-02-14', 0.3, 15.427142857142853, 4.97, 44.36, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.451,32.921,197.64]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":14.77,\"20260116\":13.83,\"20260117\":12.34,\"20260118\":13.09,\"20260119\":13.51,\"20260120\":11.25,\"20260121\":10.8,\"20260122\":12.45,\"20260123\":12.43,\"20260124\":15.66,\"20260125\":12.16,\"20260126\":12.46,\"20260127\":14.64,\"20260128\":16.57,\"20260129\":18.22,\"20260130\":18.51,\"20260131\":16.23,\"20260201\":13.13,\"20260202\":13.77,\"20260203\":17.03,\"20260204\":17.1,\"20260205\":19.74,\"20260206\":20.55,\"20260207\":17.97,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":19.17,\"20260212\":20.02,\"20260213\":17.72,\"20260214\":16.84},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.15,\"20260117\":0.0,\"20260118\":0.0,\"20260119\":0.0,\"20260120\":2.29,\"20260121\":1.01,\"20260122\":0.69,\"20260123\":0.03,\"20260124\":0.0,\"20260125\":0.06,\"20260126\":0.72,\"20260127\":0.01,\"20260128\":0.0,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.01,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":0.0,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":34.96,\"20260116\":50.01,\"20260117\":58.68,\"20260118\":47.07,\"20260119\":38.59,\"20260120\":74.92,\"20260121\":67.89,\"20260122\":59.75,\"20260123\":46.97,\"20260124\":31.88,\"20260125\":44.69,\"20260126\":48.75,\"20260127\":58.24,\"20260128\":27.61,\"20260129\":43.4,\"20260130\":52.54,\"20260131\":43.49,\"20260201\":41.01,\"20260202\":36.14,\"20260203\":23.85,\"20260204\":30.64,\"20260205\":32.39,\"20260206\":31.48,\"20260207\":48.07,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":56.54,\"20260212\":47.96,\"20260213\":32.8,\"20260214\":31.76}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.292,\"process\":0.01}}', '2026-02-21 14:46:46'),
(14, NULL, 32.9211, 10.4509, '2026-02-14', 0.3, 15.427142857142853, 4.97, 44.36, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.451,32.921,197.64]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":14.77,\"20260116\":13.83,\"20260117\":12.34,\"20260118\":13.09,\"20260119\":13.51,\"20260120\":11.25,\"20260121\":10.8,\"20260122\":12.45,\"20260123\":12.43,\"20260124\":15.66,\"20260125\":12.16,\"20260126\":12.46,\"20260127\":14.64,\"20260128\":16.57,\"20260129\":18.22,\"20260130\":18.51,\"20260131\":16.23,\"20260201\":13.13,\"20260202\":13.77,\"20260203\":17.03,\"20260204\":17.1,\"20260205\":19.74,\"20260206\":20.55,\"20260207\":17.97,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":19.17,\"20260212\":20.02,\"20260213\":17.72,\"20260214\":16.84},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.15,\"20260117\":0.0,\"20260118\":0.0,\"20260119\":0.0,\"20260120\":2.29,\"20260121\":1.01,\"20260122\":0.69,\"20260123\":0.03,\"20260124\":0.0,\"20260125\":0.06,\"20260126\":0.72,\"20260127\":0.01,\"20260128\":0.0,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.01,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":0.0,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":34.96,\"20260116\":50.01,\"20260117\":58.68,\"20260118\":47.07,\"20260119\":38.59,\"20260120\":74.92,\"20260121\":67.89,\"20260122\":59.75,\"20260123\":46.97,\"20260124\":31.88,\"20260125\":44.69,\"20260126\":48.75,\"20260127\":58.24,\"20260128\":27.61,\"20260129\":43.4,\"20260130\":52.54,\"20260131\":43.49,\"20260201\":41.01,\"20260202\":36.14,\"20260203\":23.85,\"20260204\":30.64,\"20260205\":32.39,\"20260206\":31.48,\"20260207\":48.07,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":56.54,\"20260212\":47.96,\"20260213\":32.8,\"20260214\":31.76}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.312,\"process\":0.01}}', '2026-02-21 14:47:35'),
(15, NULL, 33.9197, 8.1339, '2026-02-14', 0.3, 14.097499999999998, 7.809999999999999, 51.71392857142856, 80, 'eleve', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[8.134,33.92,88.15]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":13.77,\"20260116\":12.12,\"20260117\":11.6,\"20260118\":11.62,\"20260119\":13.58,\"20260120\":12.27,\"20260121\":11.15,\"20260122\":9.91,\"20260123\":10.44,\"20260124\":11.49,\"20260125\":10.14,\"20260126\":13.14,\"20260127\":14.88,\"20260128\":13.54,\"20260129\":16.04,\"20260130\":17.17,\"20260131\":14.77,\"20260201\":12.45,\"20260202\":12.97,\"20260203\":14.74,\"20260204\":13.78,\"20260205\":18.08,\"20260206\":18.04,\"20260207\":16.41,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":18.18,\"20260212\":19.32,\"20260213\":17.02,\"20260214\":16.11},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.0,\"20260117\":0.0,\"20260118\":0.01,\"20260119\":0.22,\"20260120\":2.52,\"20260121\":0.02,\"20260122\":1.77,\"20260123\":0.0,\"20260124\":0.31,\"20260125\":0.0,\"20260126\":0.55,\"20260127\":0.0,\"20260128\":2.0,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.26,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.1,\"20260204\":0.05,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.0,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.0},\"RH2M\":{\"20260115\":49.73,\"20260116\":62.4,\"20260117\":62.07,\"20260118\":57.79,\"20260119\":63.57,\"20260120\":63.62,\"20260121\":67.22,\"20260122\":72.15,\"20260123\":63.15,\"20260124\":50.63,\"20260125\":53.03,\"20260126\":54.58,\"20260127\":55.28,\"20260128\":45.23,\"20260129\":57.11,\"20260130\":57.99,\"20260131\":44.9,\"20260201\":48.46,\"20260202\":40.95,\"20260203\":30.76,\"20260204\":42.33,\"20260205\":47.35,\"20260206\":39.78,\"20260207\":40.55,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":61.4,\"20260212\":43.67,\"20260213\":38.44,\"20260214\":33.85}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.317,\"process\":0.01}}', '2026-02-21 14:47:59'),
(16, NULL, 34.4311, 8.7757, '2026-02-14', 0.3, 11.883214285714287, 13.75, 57.33535714285715, 65, 'moyen', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[8.776,34.431,416.93]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":11.71,\"20260116\":11.19,\"20260117\":10.07,\"20260118\":9.71,\"20260119\":11.67,\"20260120\":9.28,\"20260121\":8.87,\"20260122\":7.68,\"20260123\":8.17,\"20260124\":10.29,\"20260125\":8.49,\"20260126\":10.42,\"20260127\":11.72,\"20260128\":11.57,\"20260129\":12.41,\"20260130\":14.69,\"20260131\":12.49,\"20260201\":10.28,\"20260202\":11.05,\"20260203\":14.22,\"20260204\":11.17,\"20260205\":15.8,\"20260206\":16.48,\"20260207\":13.03,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.37,\"20260212\":16.61,\"20260213\":13.72,\"20260214\":13.57},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":0.0,\"20260117\":0.0,\"20260118\":0.01,\"20260119\":0.23,\"20260120\":6.72,\"20260121\":0.49,\"20260122\":0.39,\"20260123\":0.0,\"20260124\":1.05,\"20260125\":0.0,\"20260126\":0.47,\"20260127\":0.0,\"20260128\":2.87,\"20260129\":0.0,\"20260130\":0.0,\"20260131\":0.44,\"20260201\":0.0,\"20260202\":0.0,\"20260203\":0.05,\"20260204\":0.97,\"20260205\":0.0,\"20260206\":0.0,\"20260207\":0.02,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.0,\"20260212\":0.0,\"20260213\":0.0,\"20260214\":0.04},\"RH2M\":{\"20260115\":53.03,\"20260116\":60.34,\"20260117\":63.63,\"20260118\":66.46,\"20260119\":68.78,\"20260120\":79.3,\"20260121\":74.95,\"20260122\":82.35,\"20260123\":70.19,\"20260124\":52.95,\"20260125\":61.85,\"20260126\":56.57,\"20260127\":64.93,\"20260128\":53.81,\"20260129\":62.0,\"20260130\":63.87,\"20260131\":50.58,\"20260201\":53.72,\"20260202\":45.33,\"20260203\":31.43,\"20260204\":48.46,\"20260205\":56.85,\"20260206\":42.44,\"20260207\":51.67,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":63.49,\"20260212\":45.49,\"20260213\":40.97,\"20260214\":39.95}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.308,\"process\":0.01}}', '2026-02-21 14:48:08'),
(17, NULL, 36.8065, 10.1815, '2026-02-14', 0.8, 13.24, 143.63, 78.41, 13, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.181,36.806,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.64,\"20260116\":13.8,\"20260117\":13.54,\"20260118\":12.85,\"20260119\":13.97,\"20260120\":12.47,\"20260121\":11.95,\"20260122\":11.98,\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94},\"PRECTOTCORR\":{\"20260115\":0.01,\"20260116\":6.97,\"20260117\":0.34,\"20260118\":1.55,\"20260119\":15.91,\"20260120\":51.01,\"20260121\":11.23,\"20260122\":0.68,\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11},\"RH2M\":{\"20260115\":72.31,\"20260116\":91.1,\"20260117\":81.35,\"20260118\":81.03,\"20260119\":90.39,\"20260120\":95.22,\"20260121\":89.69,\"20260122\":82.1,\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.365,\"process\":0.01}}', '2026-02-21 16:39:50'),
(18, NULL, 36.8065, 10.1815, '2026-02-14', 0.8, 13.24, 143.63, 78.41, 13, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.181,36.806,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":12.64,\"20260116\":13.8,\"20260117\":13.54,\"20260118\":12.85,\"20260119\":13.97,\"20260120\":12.47,\"20260121\":11.95,\"20260122\":11.98,\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94},\"PRECTOTCORR\":{\"20260115\":0.01,\"20260116\":6.97,\"20260117\":0.34,\"20260118\":1.55,\"20260119\":15.91,\"20260120\":51.01,\"20260121\":11.23,\"20260122\":0.68,\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11},\"RH2M\":{\"20260115\":72.31,\"20260116\":91.1,\"20260117\":81.35,\"20260118\":81.03,\"20260119\":90.39,\"20260120\":95.22,\"20260121\":89.69,\"20260122\":82.1,\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.333,\"process\":0.01}}', '2026-02-21 16:42:04'),
(19, NULL, 36.8065, 10.1815, '2026-02-14', 0.8, 23.8, 66.6, 63.9, 45, 'faible', '{\"source\": \"demo\", \"generated\": \"2026-02-21T18:53:34.492938100\"}', '2026-02-21 16:53:34'),
(20, NULL, 36.4512897, 10.7355915, '2026-02-14', 0.8, 13.85, 124.59, 75.95, 13, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.736,36.451,69.09]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":13.92,\"20260116\":14.39,\"20260117\":13.5,\"20260118\":13.59,\"20260119\":15.15,\"20260120\":13.11,\"20260121\":12.54,\"20260122\":12.71,\"20260123\":13.33,\"20260124\":13.54,\"20260125\":11.2,\"20260126\":11.33,\"20260127\":13.24,\"20260128\":15.09,\"20260129\":14.22,\"20260130\":14.59,\"20260131\":12.3,\"20260201\":12.67,\"20260202\":13.8,\"20260203\":14.13,\"20260204\":13.85,\"20260205\":14.57,\"20260206\":15.94,\"20260207\":12.55,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.15,\"20260212\":15.95,\"20260213\":15.93,\"20260214\":14.51},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":4.45,\"20260117\":0.16,\"20260118\":1.23,\"20260119\":17.89,\"20260120\":57.18,\"20260121\":12.77,\"20260122\":0.86,\"20260123\":0.02,\"20260124\":2.73,\"20260125\":2.28,\"20260126\":1.91,\"20260127\":0.0,\"20260128\":1.42,\"20260129\":1.91,\"20260130\":5.06,\"20260131\":3.34,\"20260201\":6.26,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":0.5,\"20260205\":0.33,\"20260206\":0.0,\"20260207\":2.22,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.65,\"20260212\":0.72,\"20260213\":0.21,\"20260214\":0.49},\"RH2M\":{\"20260115\":83.02,\"20260116\":89.7,\"20260117\":78.6,\"20260118\":82.29,\"20260119\":85.78,\"20260120\":93.37,\"20260121\":90.75,\"20260122\":79.04,\"20260123\":72.56,\"20260124\":71.83,\"20260125\":76.16,\"20260126\":73.4,\"20260127\":71.79,\"20260128\":67.31,\"20260129\":71.65,\"20260130\":79.64,\"20260131\":77.95,\"20260201\":78.27,\"20260202\":65.46,\"20260203\":65.89,\"20260204\":72.22,\"20260205\":83.42,\"20260206\":65.45,\"20260207\":78.8,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":77.76,\"20260212\":65.25,\"20260213\":61.41,\"20260214\":67.82}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.364,\"process\":0.01}}', '2026-02-21 16:59:05'),
(21, NULL, 36.9235909, 10.9313384, '2026-02-14', 0.8, 14.13, 179.53, 79.85, 13, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.931,36.924,23.07]},\"properties\":{\"parameter\":{\"T2M\":{\"20260115\":14.5,\"20260116\":15.11,\"20260117\":14.11,\"20260118\":14.34,\"20260119\":15.14,\"20260120\":13.45,\"20260121\":13.19,\"20260122\":13.34,\"20260123\":14.05,\"20260124\":13.31,\"20260125\":11.92,\"20260126\":12.09,\"20260127\":13.71,\"20260128\":15.12,\"20260129\":14.08,\"20260130\":14.6,\"20260131\":12.56,\"20260201\":13.4,\"20260202\":14.13,\"20260203\":14.45,\"20260204\":13.85,\"20260205\":15.17,\"20260206\":15.74,\"20260207\":13.41,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":15.71,\"20260212\":15.71,\"20260213\":15.46,\"20260214\":13.86},\"PRECTOTCORR\":{\"20260115\":0.0,\"20260116\":5.13,\"20260117\":0.87,\"20260118\":1.79,\"20260119\":28.58,\"20260120\":79.31,\"20260121\":12.71,\"20260122\":2.27,\"20260123\":0.69,\"20260124\":3.91,\"20260125\":2.17,\"20260126\":5.67,\"20260127\":0.0,\"20260128\":2.72,\"20260129\":2.15,\"20260130\":5.59,\"20260131\":5.12,\"20260201\":8.03,\"20260202\":0.04,\"20260203\":0.0,\"20260204\":1.04,\"20260205\":0.93,\"20260206\":0.0,\"20260207\":1.54,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":1.63,\"20260212\":5.77,\"20260213\":0.1,\"20260214\":1.77},\"RH2M\":{\"20260115\":84.64,\"20260116\":88.37,\"20260117\":83.6,\"20260118\":82.63,\"20260119\":87.1,\"20260120\":92.42,\"20260121\":87.59,\"20260122\":79.78,\"20260123\":77.23,\"20260124\":79.49,\"20260125\":79.23,\"20260126\":75.79,\"20260127\":72.75,\"20260128\":73.13,\"20260129\":75.63,\"20260130\":84.35,\"20260131\":83.09,\"20260201\":78.38,\"20260202\":71.57,\"20260203\":70.98,\"20260204\":76.14,\"20260205\":86.2,\"20260206\":75.32,\"20260207\":80.76,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":86.68,\"20260212\":74.7,\"20260213\":71.68,\"20260214\":76.6}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260115\",\"end\":\"20260214\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.303,\"process\":0.01}}', '2026-02-21 16:59:46');
INSERT INTO `donnees_satellite` (`id`, `agriculteur_id`, `latitude`, `longitude`, `date_mesure`, `ndvi`, `temperature_moyenne`, `precipitation`, `humidite`, `indice_secheresse`, `risque_agricole`, `donnees_brutes`, `date_creation`) VALUES
(22, NULL, 36.8065, 10.1815, '2026-02-22', 0.7, 13.41, 59.88, 75.16, 33, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.181,36.806,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94,\"20260215\":12.14,\"20260216\":14.06,\"20260217\":13.9,\"20260218\":14.15,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"PRECTOTCORR\":{\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11,\"20260215\":1.58,\"20260216\":0.06,\"20260217\":2.31,\"20260218\":0.0,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"RH2M\":{\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17,\"20260215\":71.25,\"20260216\":74.5,\"20260217\":69.95,\"20260218\":75.89,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260123\",\"end\":\"20260221\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.364,\"process\":0.01}}', '2026-02-21 17:08:27'),
(23, NULL, 36.77409249464195, 11.09619140625, '2026-02-21', 0.7, 14.8, 59.33, 77.43, 33, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[11.096,36.774,6.41]},\"properties\":{\"parameter\":{\"T2M\":{\"20260122\":14.14,\"20260123\":14.9,\"20260124\":14.87,\"20260125\":13.06,\"20260126\":13.45,\"20260127\":14.34,\"20260128\":15.62,\"20260129\":14.67,\"20260130\":15.19,\"20260131\":13.68,\"20260201\":14.23,\"20260202\":14.93,\"20260203\":15.38,\"20260204\":14.73,\"20260205\":15.55,\"20260206\":16.25,\"20260207\":14.65,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":15.77,\"20260212\":15.96,\"20260213\":15.72,\"20260214\":14.59,\"20260215\":13.64,\"20260216\":14.83,\"20260217\":14.81,\"20260218\":14.98,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"PRECTOTCORR\":{\"20260122\":4.45,\"20260123\":1.65,\"20260124\":3.25,\"20260125\":4.05,\"20260126\":4.99,\"20260127\":0.07,\"20260128\":2.85,\"20260129\":1.44,\"20260130\":3.81,\"20260131\":5.05,\"20260201\":6.15,\"20260202\":0.12,\"20260203\":0.0,\"20260204\":0.93,\"20260205\":0.81,\"20260206\":0.0,\"20260207\":1.76,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":3.02,\"20260212\":5.9,\"20260213\":0.11,\"20260214\":2.12,\"20260215\":5.71,\"20260216\":0.4,\"20260217\":0.69,\"20260218\":0.0,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"RH2M\":{\"20260122\":76.96,\"20260123\":75.47,\"20260124\":78.52,\"20260125\":77.09,\"20260126\":73.18,\"20260127\":71.81,\"20260128\":76.78,\"20260129\":74.61,\"20260130\":83.16,\"20260131\":80.28,\"20260201\":75.78,\"20260202\":73.0,\"20260203\":76.43,\"20260204\":76.24,\"20260205\":86.41,\"20260206\":78.04,\"20260207\":81.14,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":88.79,\"20260212\":76.46,\"20260213\":74.52,\"20260214\":77.28,\"20260215\":78.4,\"20260216\":77.99,\"20260217\":70.81,\"20260218\":76.69,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260122\",\"end\":\"20260221\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.346,\"process\":0.01}}', '2026-02-21 17:19:45'),
(24, NULL, 36.798288873837045, 10.969848632812502, '2026-02-21', 0.7, 14.8, 59.33, 77.43, 33, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.97,36.798,6.41]},\"properties\":{\"parameter\":{\"T2M\":{\"20260122\":14.14,\"20260123\":14.9,\"20260124\":14.87,\"20260125\":13.06,\"20260126\":13.45,\"20260127\":14.34,\"20260128\":15.62,\"20260129\":14.67,\"20260130\":15.19,\"20260131\":13.68,\"20260201\":14.23,\"20260202\":14.93,\"20260203\":15.38,\"20260204\":14.73,\"20260205\":15.55,\"20260206\":16.25,\"20260207\":14.65,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":15.77,\"20260212\":15.96,\"20260213\":15.72,\"20260214\":14.59,\"20260215\":13.64,\"20260216\":14.83,\"20260217\":14.81,\"20260218\":14.98,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"PRECTOTCORR\":{\"20260122\":4.45,\"20260123\":1.65,\"20260124\":3.25,\"20260125\":4.05,\"20260126\":4.99,\"20260127\":0.07,\"20260128\":2.85,\"20260129\":1.44,\"20260130\":3.81,\"20260131\":5.05,\"20260201\":6.15,\"20260202\":0.12,\"20260203\":0.0,\"20260204\":0.93,\"20260205\":0.81,\"20260206\":0.0,\"20260207\":1.76,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":3.02,\"20260212\":5.9,\"20260213\":0.11,\"20260214\":2.12,\"20260215\":5.71,\"20260216\":0.4,\"20260217\":0.69,\"20260218\":0.0,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"RH2M\":{\"20260122\":76.96,\"20260123\":75.47,\"20260124\":78.52,\"20260125\":77.09,\"20260126\":73.18,\"20260127\":71.81,\"20260128\":76.78,\"20260129\":74.61,\"20260130\":83.16,\"20260131\":80.28,\"20260201\":75.78,\"20260202\":73.0,\"20260203\":76.43,\"20260204\":76.24,\"20260205\":86.41,\"20260206\":78.04,\"20260207\":81.14,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":88.79,\"20260212\":76.46,\"20260213\":74.52,\"20260214\":77.28,\"20260215\":78.4,\"20260216\":77.99,\"20260217\":70.81,\"20260218\":76.69,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260122\",\"end\":\"20260221\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.303,\"process\":0.01}}', '2026-02-21 17:23:05'),
(25, NULL, 36.721273880045004, 10.173339843750002, '2026-02-21', 0.6, 12.89, 39.39, 68.08, 50, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.173,36.721,162.85]},\"properties\":{\"parameter\":{\"T2M\":{\"20260122\":10.9,\"20260123\":12.05,\"20260124\":11.47,\"20260125\":9.15,\"20260126\":9.57,\"20260127\":12.09,\"20260128\":14.6,\"20260129\":13.62,\"20260130\":13.95,\"20260131\":10.68,\"20260201\":11.38,\"20260202\":12.51,\"20260203\":13.62,\"20260204\":12.53,\"20260205\":14.57,\"20260206\":15.55,\"20260207\":11.26,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.03,\"20260212\":15.46,\"20260213\":15.8,\"20260214\":13.7,\"20260215\":11.3,\"20260216\":13.54,\"20260217\":13.33,\"20260218\":13.64,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"PRECTOTCORR\":{\"20260122\":0.31,\"20260123\":0.02,\"20260124\":4.73,\"20260125\":0.62,\"20260126\":1.09,\"20260127\":0.0,\"20260128\":1.33,\"20260129\":1.72,\"20260130\":4.27,\"20260131\":10.31,\"20260201\":6.23,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.01,\"20260205\":0.3,\"20260206\":0.0,\"20260207\":1.45,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.22,\"20260212\":0.43,\"20260213\":0.27,\"20260214\":0.76,\"20260215\":1.02,\"20260216\":0.02,\"20260217\":3.28,\"20260218\":0.0,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"RH2M\":{\"20260122\":79.24,\"20260123\":66.83,\"20260124\":70.27,\"20260125\":76.23,\"20260126\":70.78,\"20260127\":71.03,\"20260128\":58.3,\"20260129\":68.3,\"20260130\":77.84,\"20260131\":77.71,\"20260201\":76.38,\"20260202\":62.66,\"20260203\":53.08,\"20260204\":68.51,\"20260205\":78.2,\"20260206\":57.55,\"20260207\":74.5,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":72.24,\"20260212\":59.57,\"20260213\":51.37,\"20260214\":62.43,\"20260215\":66.72,\"20260216\":64.04,\"20260217\":66.67,\"20260218\":71.67,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260122\",\"end\":\"20260221\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.295,\"process\":0.01}}', '2026-02-21 17:28:25'),
(26, NULL, 36.8065, 10.1815, '2026-02-21', 0.7, 13.35, 60.56, 75.44, 33, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[10.181,36.806,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260122\":11.98,\"20260123\":13.33,\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94,\"20260215\":12.14,\"20260216\":14.06,\"20260217\":13.9,\"20260218\":14.15,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"PRECTOTCORR\":{\"20260122\":0.68,\"20260123\":0.24,\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11,\"20260215\":1.58,\"20260216\":0.06,\"20260217\":2.31,\"20260218\":0.0,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0},\"RH2M\":{\"20260122\":82.1,\"20260123\":74.55,\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17,\"20260215\":71.25,\"20260216\":74.5,\"20260217\":69.95,\"20260218\":75.89,\"20260219\":-999.0,\"20260220\":-999.0,\"20260221\":-999.0}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260122\",\"end\":\"20260221\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.378,\"process\":0.01}}', '2026-02-21 18:08:15'),
(27, NULL, 37.21283151445594, 9.898681640625002, '2026-02-23', 0.7, 13.41, 60.37, 74.55, 40, 'faible', '{\"type\":\"Feature\",\"geometry\":{\"type\":\"Point\",\"coordinates\":[9.899,37.213,54.49]},\"properties\":{\"parameter\":{\"T2M\":{\"20260124\":11.39,\"20260125\":10.32,\"20260126\":10.77,\"20260127\":12.77,\"20260128\":14.83,\"20260129\":13.61,\"20260130\":14.08,\"20260131\":11.27,\"20260201\":12.45,\"20260202\":13.31,\"20260203\":13.72,\"20260204\":12.74,\"20260205\":15.25,\"20260206\":15.33,\"20260207\":12.17,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":16.04,\"20260212\":15.87,\"20260213\":15.36,\"20260214\":12.94,\"20260215\":12.14,\"20260216\":14.06,\"20260217\":13.9,\"20260218\":14.15,\"20260219\":13.6,\"20260220\":13.18,\"20260221\":-999.0,\"20260222\":-999.0,\"20260223\":-999.0},\"PRECTOTCORR\":{\"20260124\":6.01,\"20260125\":0.63,\"20260126\":4.51,\"20260127\":0.0,\"20260128\":2.48,\"20260129\":3.09,\"20260130\":6.47,\"20260131\":10.32,\"20260201\":11.42,\"20260202\":0.0,\"20260203\":0.0,\"20260204\":1.54,\"20260205\":0.99,\"20260206\":0.0,\"20260207\":1.46,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":0.4,\"20260212\":4.11,\"20260213\":0.15,\"20260214\":2.11,\"20260215\":1.58,\"20260216\":0.06,\"20260217\":2.31,\"20260218\":0.0,\"20260219\":0.04,\"20260220\":0.69,\"20260221\":-999.0,\"20260222\":-999.0,\"20260223\":-999.0},\"RH2M\":{\"20260124\":80.18,\"20260125\":81.05,\"20260126\":75.11,\"20260127\":73.7,\"20260128\":66.2,\"20260129\":74.9,\"20260130\":83.61,\"20260131\":84.4,\"20260201\":78.36,\"20260202\":69.41,\"20260203\":66.3,\"20260204\":75.5,\"20260205\":83.77,\"20260206\":71.02,\"20260207\":79.23,\"20260208\":-999.0,\"20260209\":-999.0,\"20260210\":-999.0,\"20260211\":82.28,\"20260212\":69.78,\"20260213\":66.79,\"20260214\":76.17,\"20260215\":71.25,\"20260216\":74.5,\"20260217\":69.95,\"20260218\":75.89,\"20260219\":67.66,\"20260220\":66.72,\"20260221\":-999.0,\"20260222\":-999.0,\"20260223\":-999.0}}},\"header\":{\"title\":\"NASA/POWER Source Native Resolution Daily Data\",\"api\":{\"version\":\"v2.8.10\",\"name\":\"POWER Daily API\"},\"sources\":[\"GEOSIT\",\"POWER\"],\"fill_value\":-999.0,\"time_standard\":\"LST\",\"start\":\"20260124\",\"end\":\"20260223\"},\"messages\":[],\"parameters\":{\"T2M\":{\"units\":\"C\",\"longname\":\"Temperature at 2 Meters\"},\"PRECTOTCORR\":{\"units\":\"mm/day\",\"longname\":\"Precipitation Corrected\"},\"RH2M\":{\"units\":\"%\",\"longname\":\"Relative Humidity at 2 Meters\"}},\"times\":{\"data\":0.507,\"process\":0.01}}', '2026-02-23 06:37:22');

-- --------------------------------------------------------

--
-- Structure de la table `evaluationrisque`
--

CREATE TABLE `evaluationrisque` (
  `idEvaluation` int(11) NOT NULL,
  `scoreGlobal` int(11) NOT NULL,
  `niveauRisque` varchar(20) NOT NULL,
  `fiabiliteDonnees` varchar(20) NOT NULL,
  `facteurPrincipal` text NOT NULL,
  `recommandation` int(11) NOT NULL,
  `dateEvaluation` datetime NOT NULL,
  `idProjet` int(11) NOT NULL,
  `banqueId` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `evaluationrisque`
--

INSERT INTO `evaluationrisque` (`idEvaluation`, `scoreGlobal`, `niveauRisque`, `fiabiliteDonnees`, `facteurPrincipal`, `recommandation`, `dateEvaluation`, `idProjet`, `banqueId`) VALUES
(15, 80, 'Faible', 'Élevée', 'Conditions optimales\n\nConseils pour améliorer le projet:\n- ✅ Température optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 20°C et 25°C.\n- ✅ Humidité optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 50% et 70%.\n- ✅ pH optimal: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 6 et 7.5.\n- 🌟 Projet excellent: Continuez ainsi pour maintenir ces conditions optimales.', -1, '2026-02-27 00:00:00', 1, NULL),
(17, 80, 'Faible', 'Élevée', 'Conditions optimales\n\nConseils pour améliorer le projet:\n- ✅ Température optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 20°C et 25°C.\n- ✅ Humidité optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 50% et 70%.\n- ✅ pH optimal: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 6 et 7.5.\n- 🌟 Projet excellent: Continuez ainsi pour maintenir ces conditions optimales.', -1, '2026-02-28 00:00:00', 33, NULL),
(18, 80, 'Faible', 'Élevée', 'Conditions optimales\n\nConseils pour améliorer le projet:\n- ✅ Température optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 20°C et 25°C.\n- ✅ Humidité optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 50% et 70%.\n- ✅ pH optimal: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 6 et 7.5.\n- 🌟 Projet excellent: Continuez ainsi pour maintenir ces conditions optimales.', 0, '2026-02-28 00:00:00', 33, 4),
(19, 80, 'Faible', 'Élevée', 'Conditions optimales\n\nConseils pour améliorer le projet:\n- ✅ Température optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 20°C et 25°C.\n- ✅ Humidité optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 50% et 70%.\n- ✅ pH optimal: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 6 et 7.5.\n- 🌟 Projet excellent: Continuez ainsi pour maintenir ces conditions optimales.', -1, '2026-02-28 00:00:00', 33, 4),
(20, 80, 'Faible', 'Élevée', 'Conditions optimales\n\nConseils pour améliorer le projet:\n- ✅ Température optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 20°C et 25°C.\n- ✅ Humidité optimale: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 50% et 70%.\n- ✅ pH optimal: Continuer à surveiller pour maintenir cette condition. Idéalement, maintenir entre 6 et 7.5.\n- 🌟 Projet excellent: Continuez ainsi pour maintenir ces conditions optimales.', 2, '2026-02-28 00:00:00', 33, 4),
(21, 0, 'Faible', 'Faible', 'Aucune donnée récente. Conseil: Veuillez ajouter des données de capteurs pour évaluer ce projet.', 3, '2026-02-28 00:00:00', 32, 4);

-- --------------------------------------------------------

--
-- Structure de la table `historiqueconnexion`
--

CREATE TABLE `historiqueconnexion` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `date_connexion` datetime DEFAULT current_timestamp(),
  `adresse_ip` varchar(45) DEFAULT NULL,
  `navigateur` varchar(255) DEFAULT NULL,
  `systeme_exploitation` varchar(100) DEFAULT NULL,
  `connexion_reussie` tinyint(1) DEFAULT 1,
  `methode_auth` enum('password','2fa','token') DEFAULT 'password',
  `localisation` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `historiqueconnexion`
--

INSERT INTO `historiqueconnexion` (`id`, `utilisateur_id`, `date_connexion`, `adresse_ip`, `navigateur`, `systeme_exploitation`, `connexion_reussie`, `methode_auth`, `localisation`) VALUES
(1, 2, '2026-02-09 17:12:06', NULL, NULL, NULL, 1, 'password', NULL),
(2, 2, '2026-02-09 17:14:13', NULL, NULL, NULL, 1, '2fa', NULL),
(3, 2, '2026-02-09 17:16:51', NULL, NULL, NULL, 1, '2fa', NULL),
(4, 3, '2026-02-10 09:41:42', NULL, NULL, NULL, 1, 'password', NULL),
(5, 3, '2026-02-10 10:52:16', NULL, NULL, NULL, 1, 'password', NULL),
(6, 3, '2026-02-11 14:00:07', NULL, NULL, NULL, 1, 'password', NULL),
(7, 2, '2026-02-11 19:52:10', NULL, NULL, NULL, 1, '2fa', NULL),
(8, 2, '2026-02-11 20:24:53', NULL, NULL, NULL, 1, '2fa', NULL),
(9, 2, '2026-02-12 10:52:01', NULL, NULL, NULL, 0, '2fa', NULL),
(10, 2, '2026-02-12 10:52:20', NULL, NULL, NULL, 1, '2fa', NULL),
(11, 3, '2026-02-13 15:16:42', NULL, NULL, NULL, 1, 'password', NULL),
(12, 3, '2026-02-13 15:21:25', NULL, NULL, NULL, 1, 'password', NULL),
(14, 4, '2026-02-13 15:28:38', NULL, NULL, NULL, 1, 'password', NULL),
(15, 3, '2026-02-13 15:35:31', NULL, NULL, NULL, 1, 'password', NULL),
(16, 3, '2026-02-13 16:06:39', NULL, NULL, NULL, 1, 'password', NULL),
(17, 3, '2026-02-15 09:52:36', NULL, NULL, NULL, 1, 'password', NULL),
(18, 3, '2026-02-15 09:57:21', NULL, NULL, NULL, 1, 'password', NULL),
(19, 3, '2026-02-15 10:04:32', NULL, NULL, NULL, 1, 'password', NULL),
(20, 3, '2026-02-15 10:07:07', NULL, NULL, NULL, 1, 'password', NULL),
(21, 3, '2026-02-15 19:32:21', NULL, NULL, NULL, 1, 'password', NULL),
(22, 3, '2026-02-15 19:33:27', NULL, NULL, NULL, 1, 'password', NULL),
(23, 3, '2026-02-15 19:34:28', NULL, NULL, NULL, 1, 'password', NULL),
(24, 3, '2026-02-15 19:35:50', NULL, NULL, NULL, 1, 'password', NULL),
(25, 3, '2026-02-15 19:37:29', NULL, NULL, NULL, 1, 'password', NULL),
(26, 3, '2026-02-15 19:38:26', NULL, NULL, NULL, 1, 'password', NULL),
(27, 6, '2026-02-15 19:45:06', NULL, NULL, NULL, 1, 'password', NULL),
(28, 3, '2026-02-15 19:51:24', NULL, NULL, NULL, 1, 'password', NULL),
(29, 3, '2026-02-15 19:54:18', NULL, NULL, NULL, 1, 'password', NULL),
(30, 3, '2026-02-15 20:24:09', NULL, NULL, NULL, 1, 'password', NULL),
(31, 3, '2026-02-15 20:26:23', NULL, NULL, NULL, 1, 'password', NULL),
(32, 3, '2026-02-15 20:31:27', NULL, NULL, NULL, 1, 'password', NULL),
(33, 3, '2026-02-15 20:32:59', NULL, NULL, NULL, 1, 'password', NULL),
(34, 3, '2026-02-15 20:37:00', NULL, NULL, NULL, 1, 'password', NULL),
(35, 6, '2026-02-15 20:40:16', NULL, NULL, NULL, 1, 'password', NULL),
(36, 3, '2026-02-15 20:55:34', NULL, NULL, NULL, 1, 'password', NULL),
(37, 6, '2026-02-15 20:56:41', NULL, NULL, NULL, 1, 'password', NULL),
(38, 4, '2026-02-15 21:06:01', NULL, NULL, NULL, 1, 'password', NULL),
(39, 4, '2026-02-15 21:11:51', NULL, NULL, NULL, 1, 'password', NULL),
(40, 4, '2026-02-15 21:12:00', NULL, NULL, NULL, 1, 'password', NULL),
(41, 4, '2026-02-15 21:12:09', NULL, NULL, NULL, 1, 'password', NULL),
(42, 4, '2026-02-15 21:12:17', NULL, NULL, NULL, 1, 'password', NULL),
(43, 4, '2026-02-15 21:12:20', NULL, NULL, NULL, 1, 'password', NULL),
(44, 4, '2026-02-15 21:16:44', NULL, NULL, NULL, 1, 'password', NULL),
(45, 3, '2026-02-16 09:08:31', NULL, NULL, NULL, 1, 'password', NULL),
(46, 7, '2026-02-16 09:30:15', NULL, NULL, NULL, 1, 'password', NULL),
(47, 3, '2026-02-16 09:34:16', NULL, NULL, NULL, 1, 'password', NULL),
(48, 7, '2026-02-16 09:39:51', NULL, NULL, NULL, 1, '2fa', NULL),
(49, 7, '2026-02-16 09:39:51', NULL, NULL, NULL, 1, 'password', NULL),
(50, 3, '2026-02-18 11:42:23', NULL, NULL, NULL, 1, 'password', NULL),
(51, 3, '2026-02-21 11:38:17', NULL, NULL, NULL, 1, 'password', NULL),
(52, 2, '2026-02-21 11:43:23', NULL, NULL, NULL, 1, '2fa', NULL),
(53, 2, '2026-02-21 11:43:23', NULL, NULL, NULL, 1, 'password', NULL),
(54, 3, '2026-02-21 12:04:04', NULL, NULL, NULL, 1, 'password', NULL),
(55, 3, '2026-02-21 12:13:20', NULL, NULL, NULL, 1, 'password', NULL),
(56, 4, '2026-02-21 12:21:10', NULL, NULL, NULL, 1, 'password', NULL),
(57, 4, '2026-02-21 12:55:18', NULL, NULL, NULL, 1, 'password', NULL),
(58, 4, '2026-02-21 13:00:00', NULL, NULL, NULL, 1, 'password', NULL),
(59, 4, '2026-02-21 13:12:01', NULL, NULL, NULL, 1, 'password', NULL),
(60, 4, '2026-02-21 16:44:12', NULL, NULL, NULL, 1, 'password', NULL),
(61, 4, '2026-02-21 16:46:12', NULL, NULL, NULL, 1, 'password', NULL),
(62, 4, '2026-02-21 18:30:06', NULL, NULL, NULL, 1, 'password', NULL),
(63, 4, '2026-02-21 18:33:04', NULL, NULL, NULL, 1, 'password', NULL),
(64, 4, '2026-02-21 18:39:28', NULL, NULL, NULL, 1, 'password', NULL),
(65, 4, '2026-02-21 18:52:14', NULL, NULL, NULL, 1, 'password', NULL),
(66, 4, '2026-02-21 18:55:52', NULL, NULL, NULL, 1, 'password', NULL),
(67, 4, '2026-02-21 18:58:26', NULL, NULL, NULL, 1, 'password', NULL),
(68, 4, '2026-02-21 19:08:04', NULL, NULL, NULL, 1, 'password', NULL),
(69, 4, '2026-02-21 19:16:38', NULL, NULL, NULL, 1, 'password', NULL),
(70, 4, '2026-02-21 19:19:02', NULL, NULL, NULL, 1, 'password', NULL),
(71, 4, '2026-02-21 19:27:46', NULL, NULL, NULL, 1, 'password', NULL),
(72, 4, '2026-02-21 20:07:42', NULL, NULL, NULL, 1, 'password', NULL),
(73, 2, '2026-02-22 17:43:52', NULL, NULL, NULL, 1, '2fa', NULL),
(74, 2, '2026-02-22 17:43:54', NULL, NULL, NULL, 1, 'password', NULL),
(75, 3, '2026-02-22 17:54:23', NULL, NULL, NULL, 1, 'password', NULL),
(76, 3, '2026-02-22 17:55:26', NULL, NULL, NULL, 1, 'password', NULL),
(77, 3, '2026-02-22 17:58:54', NULL, NULL, NULL, 1, 'password', NULL),
(78, 6, '2026-02-22 19:26:03', NULL, NULL, NULL, 1, 'password', NULL),
(79, 4, '2026-02-23 08:36:08', NULL, NULL, NULL, 1, 'password', NULL),
(80, 3, '2026-02-24 00:14:41', NULL, NULL, NULL, 1, 'password', NULL),
(81, 2, '2026-02-26 13:52:51', NULL, NULL, NULL, 1, '2fa', NULL),
(82, 2, '2026-02-26 13:52:52', NULL, NULL, NULL, 1, 'password', NULL),
(83, 2, '2026-02-26 14:00:50', NULL, NULL, NULL, 0, '2fa', NULL),
(84, 2, '2026-02-26 14:01:30', NULL, NULL, NULL, 1, '2fa', NULL),
(85, 2, '2026-02-26 14:01:32', NULL, NULL, NULL, 1, 'password', NULL),
(86, 3, '2026-02-26 21:31:36', NULL, NULL, NULL, 1, 'password', NULL),
(87, 2, '2026-02-26 21:37:16', NULL, NULL, NULL, 1, '2fa', NULL),
(88, 2, '2026-02-26 21:37:18', NULL, NULL, NULL, 1, 'password', NULL),
(89, 2, '2026-02-26 21:41:53', NULL, NULL, NULL, 1, '2fa', NULL),
(90, 2, '2026-02-26 21:41:55', NULL, NULL, NULL, 1, 'password', NULL),
(91, 4, '2026-02-26 21:46:51', NULL, NULL, NULL, 1, 'password', NULL),
(92, 3, '2026-02-26 21:50:39', NULL, NULL, NULL, 1, 'password', NULL),
(93, 4, '2026-02-26 23:01:43', NULL, NULL, NULL, 1, 'password', NULL),
(94, 4, '2026-02-26 23:41:17', NULL, NULL, NULL, 1, 'password', NULL),
(95, 4, '2026-02-26 23:46:49', NULL, NULL, NULL, 1, 'password', NULL),
(96, 4, '2026-02-27 00:03:20', NULL, NULL, NULL, 1, 'password', NULL),
(97, 4, '2026-02-27 00:47:42', NULL, NULL, NULL, 1, 'password', NULL),
(98, 4, '2026-02-27 00:52:30', NULL, NULL, NULL, 1, 'password', NULL),
(99, 4, '2026-02-27 01:10:35', NULL, NULL, NULL, 1, 'password', NULL),
(100, 4, '2026-02-27 01:30:31', NULL, NULL, NULL, 1, 'password', NULL),
(101, 4, '2026-02-27 01:41:26', NULL, NULL, NULL, 1, 'password', NULL),
(102, 2, '2026-02-27 11:45:25', NULL, NULL, NULL, 1, '2fa', NULL),
(103, 2, '2026-02-27 11:45:26', NULL, NULL, NULL, 1, 'password', NULL),
(104, 2, '2026-02-27 11:48:54', NULL, NULL, NULL, 1, '2fa', NULL),
(105, 2, '2026-02-27 11:48:56', NULL, NULL, NULL, 1, 'password', NULL),
(106, 2, '2026-02-27 11:53:45', NULL, NULL, NULL, 1, '2fa', NULL),
(107, 2, '2026-02-27 11:53:47', NULL, NULL, NULL, 1, 'password', NULL),
(108, 4, '2026-02-27 12:45:43', NULL, NULL, NULL, 1, 'password', NULL),
(109, 4, '2026-02-27 13:03:50', NULL, NULL, NULL, 1, 'password', NULL),
(110, 3, '2026-02-27 14:16:21', NULL, NULL, NULL, 1, 'password', NULL),
(111, 3, '2026-02-27 14:24:25', NULL, NULL, NULL, 1, 'password', NULL),
(112, 3, '2026-02-27 14:34:28', NULL, NULL, NULL, 1, 'password', NULL),
(113, 3, '2026-02-27 14:38:12', NULL, NULL, NULL, 1, 'password', NULL),
(114, 2, '2026-02-27 15:48:10', NULL, NULL, NULL, 1, '2fa', NULL),
(115, 2, '2026-02-27 15:48:12', NULL, NULL, NULL, 1, 'password', NULL),
(116, 6, '2026-02-27 15:49:58', NULL, NULL, NULL, 1, 'password', NULL),
(117, 6, '2026-02-27 15:51:44', NULL, NULL, NULL, 1, 'password', NULL),
(118, 6, '2026-02-27 16:02:08', NULL, NULL, NULL, 1, 'password', NULL),
(119, 6, '2026-02-27 16:13:59', NULL, NULL, NULL, 1, 'password', NULL),
(120, 6, '2026-02-27 16:26:56', NULL, NULL, NULL, 1, 'password', NULL),
(121, 6, '2026-02-27 16:38:11', NULL, NULL, NULL, 1, 'password', NULL),
(122, 3, '2026-02-27 16:54:19', NULL, NULL, NULL, 1, 'password', NULL),
(123, 3, '2026-02-27 16:58:57', NULL, NULL, NULL, 1, 'password', NULL),
(124, 4, '2026-02-27 17:01:17', NULL, NULL, NULL, 1, 'password', NULL),
(125, 6, '2026-02-27 17:35:08', NULL, NULL, NULL, 1, 'password', NULL),
(126, 6, '2026-02-27 17:38:11', NULL, NULL, NULL, 1, 'password', NULL),
(127, 6, '2026-02-27 17:45:23', NULL, NULL, NULL, 1, 'password', NULL),
(128, 6, '2026-02-27 17:54:11', NULL, NULL, NULL, 1, 'password', NULL),
(129, 6, '2026-02-27 17:57:54', NULL, NULL, NULL, 1, 'password', NULL),
(130, 6, '2026-02-27 18:03:14', NULL, NULL, NULL, 1, 'password', NULL),
(131, 6, '2026-02-27 18:07:12', NULL, NULL, NULL, 1, 'password', NULL),
(132, 3, '2026-02-27 18:12:59', NULL, NULL, NULL, 1, 'password', NULL),
(133, 3, '2026-02-27 18:42:45', NULL, NULL, NULL, 1, 'password', NULL),
(134, 4, '2026-02-27 18:44:12', NULL, NULL, NULL, 1, 'password', NULL),
(135, 2, '2026-02-27 18:47:22', NULL, NULL, NULL, 1, '2fa', NULL),
(136, 2, '2026-02-27 18:47:23', NULL, NULL, NULL, 1, 'password', NULL),
(137, 3, '2026-02-27 18:49:34', NULL, NULL, NULL, 1, 'password', NULL),
(138, 6, '2026-02-27 19:10:02', NULL, NULL, NULL, 1, 'password', NULL),
(139, 3, '2026-02-27 21:18:35', NULL, NULL, NULL, 1, 'password', NULL),
(140, 3, '2026-02-27 21:24:08', NULL, NULL, NULL, 1, 'password', NULL),
(141, 3, '2026-02-27 21:41:46', NULL, NULL, NULL, 1, 'password', NULL),
(142, 3, '2026-02-27 21:45:27', NULL, NULL, NULL, 1, 'password', NULL),
(143, 3, '2026-02-27 22:13:06', NULL, NULL, NULL, 1, 'password', NULL),
(144, 2, '2026-02-27 22:18:25', NULL, NULL, NULL, 1, '2fa', NULL),
(145, 2, '2026-02-27 22:18:27', NULL, NULL, NULL, 1, 'password', NULL),
(146, 6, '2026-02-27 22:21:56', NULL, NULL, NULL, 1, 'password', NULL),
(147, 3, '2026-02-28 12:25:39', NULL, NULL, NULL, 1, 'password', NULL),
(148, 3, '2026-02-28 13:00:11', NULL, NULL, NULL, 1, 'password', NULL),
(149, 6, '2026-02-28 13:23:51', NULL, NULL, NULL, 1, 'password', NULL),
(150, 3, '2026-02-28 13:26:39', NULL, NULL, NULL, 1, 'password', NULL),
(151, 3, '2026-02-28 13:40:53', NULL, NULL, NULL, 1, 'password', NULL),
(152, 6, '2026-02-28 14:18:42', NULL, NULL, NULL, 1, 'password', NULL),
(153, 4, '2026-02-28 15:46:17', NULL, NULL, NULL, 1, 'password', NULL),
(154, 4, '2026-02-28 16:01:45', NULL, NULL, NULL, 1, 'password', NULL),
(155, 4, '2026-02-28 16:03:40', NULL, NULL, NULL, 1, 'password', NULL),
(156, 4, '2026-02-28 16:18:18', NULL, NULL, NULL, 1, 'password', NULL),
(157, 4, '2026-02-28 16:24:58', NULL, NULL, NULL, 1, 'password', NULL),
(158, 3, '2026-02-28 16:27:26', NULL, NULL, NULL, 1, 'password', NULL),
(159, 6, '2026-02-28 18:55:05', NULL, NULL, NULL, 1, 'password', NULL),
(160, 3, '2026-02-28 18:59:29', NULL, NULL, NULL, 1, 'password', NULL),
(161, 3, '2026-02-28 19:05:19', NULL, NULL, NULL, 1, 'password', NULL),
(162, 3, '2026-02-28 19:10:12', NULL, NULL, NULL, 1, 'password', NULL),
(163, 4, '2026-02-28 19:31:19', NULL, NULL, NULL, 1, 'password', NULL),
(164, 3, '2026-02-28 22:08:11', NULL, NULL, NULL, 1, 'password', NULL),
(165, 4, '2026-02-28 22:13:24', NULL, NULL, NULL, 1, 'password', NULL),
(166, 6, '2026-02-28 22:16:46', NULL, NULL, NULL, 1, 'password', NULL),
(167, 3, '2026-02-28 22:23:49', NULL, NULL, NULL, 1, 'password', NULL),
(168, 6, '2026-02-28 22:27:58', NULL, NULL, NULL, 1, 'password', NULL);

-- --------------------------------------------------------

--
-- Structure de la table `message`
--

CREATE TABLE `message` (
  `id` int(11) NOT NULL,
  `conversation_id` int(11) NOT NULL,
  `expediteur_id` int(11) NOT NULL,
  `contenu` text NOT NULL,
  `a_piece_jointe` tinyint(1) DEFAULT 0,
  `nb_pieces_jointes` int(11) DEFAULT 0,
  `date_envoi` datetime DEFAULT current_timestamp(),
  `date_modification` datetime DEFAULT NULL,
  `est_lu` tinyint(1) DEFAULT 0,
  `est_supprime` tinyint(1) DEFAULT 0,
  `date_lecture` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Déchargement des données de la table `message`
--

INSERT INTO `message` (`id`, `conversation_id`, `expediteur_id`, `contenu`, `a_piece_jointe`, `nb_pieces_jointes`, `date_envoi`, `date_modification`, `est_lu`, `est_supprime`, `date_lecture`) VALUES
(1, 1, 3, 'salut Samir', 0, 0, '2026-02-08 14:20:56', '2026-02-08 14:21:36', 1, 0, NULL),
(2, 1, 2, 'salut', 0, 0, '2026-02-08 14:23:14', NULL, 1, 1, NULL),
(3, 2, 3, 'hello', 0, 0, '2026-02-10 09:42:52', '2026-02-13 16:07:00', 1, 0, NULL),
(4, 2, 3, '🎵 [Audio]', 0, 0, '2026-02-10 10:53:05', NULL, 1, 1, NULL),
(5, 1, 3, 'bonsoir', 0, 0, '2026-02-11 19:55:48', NULL, 1, 1, NULL),
(6, 1, 3, '🖼️ Capture d\'écran 2026-02-10 164048.png', 1, 1, '2026-02-11 19:56:03', NULL, 1, 1, NULL),
(7, 1, 3, 'sou', 0, 0, '2026-02-12 10:42:59', '2026-02-12 10:43:17', 1, 0, NULL),
(8, 1, 3, 'salut', 0, 0, '2026-02-13 15:21:57', NULL, 1, 0, NULL),
(11, 2, 4, 'hello admin', 0, 0, '2026-02-13 15:29:36', NULL, 1, 0, NULL),
(12, 2, 3, 'how are you today', 0, 0, '2026-02-13 15:42:02', NULL, 1, 1, NULL),
(13, 1, 3, '📎 stb.png', 1, 1, '2026-02-13 16:19:56', NULL, 1, 1, NULL),
(14, 2, 3, 'how are you', 0, 0, '2026-02-15 09:53:32', NULL, 1, 0, NULL),
(15, 2, 3, 'smile', 0, 0, '2026-02-15 20:00:23', NULL, 1, 1, NULL),
(16, 2, 4, 'fine thnks', 0, 0, '2026-02-15 21:17:16', NULL, 1, 0, NULL),
(17, 4, 7, 'bonjour123', 0, 0, '2026-02-16 09:32:12', '2026-02-16 09:32:33', 1, 1, NULL),
(18, 1, 2, 'bonjour', 0, 0, '2026-02-21 11:49:06', NULL, 1, 0, NULL),
(19, 1, 2, 'bonjour', 0, 0, '2026-02-26 13:56:40', NULL, 1, 0, NULL),
(20, 1, 2, 'hello', 0, 0, '2026-02-26 21:42:36', NULL, 1, 0, NULL),
(21, 5, 2, 'hello banque', 0, 0, '2026-02-26 21:43:12', NULL, 1, 0, NULL),
(22, 5, 2, '😂', 0, 0, '2026-02-26 21:43:23', NULL, 1, 0, NULL),
(23, 5, 2, '📎 Capture d\'écran 2026-02-26 211617.png', 1, 1, '2026-02-26 21:43:36', NULL, 1, 0, NULL),
(24, 1, 3, 'salem', 0, 0, '2026-02-27 14:41:27', NULL, 0, 0, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `offre_financiere`
--

CREATE TABLE `offre_financiere` (
  `id_offre` int(11) NOT NULL,
  `nom_offre` varchar(100) NOT NULL,
  `conditions` text DEFAULT NULL,
  `statut` varchar(30) DEFAULT NULL,
  `id_produit` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `offre_financiere`
--

INSERT INTO `offre_financiere` (`id_offre`, `nom_offre`, `conditions`, `statut`, `id_produit`) VALUES
(1, 'aaaaa', 'hfpgodlkd,ior', 'Active', 1);

-- --------------------------------------------------------

--
-- Structure de la table `parametres2fa`
--

CREATE TABLE `parametres2fa` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `est_active` tinyint(1) DEFAULT 0,
  `methode_preferee` enum('email','sms','desactive') DEFAULT 'email',
  `telephone_2fa` varchar(20) DEFAULT NULL,
  `date_activation` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `parametres2fa`
--

INSERT INTO `parametres2fa` (`id`, `utilisateur_id`, `est_active`, `methode_preferee`, `telephone_2fa`, `date_activation`) VALUES
(1, 2, 1, 'email', '+21656328712', '2026-02-09 17:12:56'),
(2, 3, 0, 'email', NULL, '2026-02-13 15:18:37'),
(3, 7, 1, 'email', NULL, '2026-02-16 09:33:04');

-- --------------------------------------------------------

--
-- Structure de la table `piecejointe`
--

CREATE TABLE `piecejointe` (
  `id` int(11) NOT NULL,
  `message_id` int(11) NOT NULL,
  `type_fichier` enum('image','document','audio','video','autre') NOT NULL,
  `nom_original` varchar(255) NOT NULL,
  `nom_stockage` varchar(255) NOT NULL,
  `chemin_fichier` varchar(500) NOT NULL,
  `taille_octets` bigint(20) NOT NULL,
  `extension` varchar(10) DEFAULT NULL,
  `mime_type` varchar(100) DEFAULT NULL,
  `date_upload` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `piecejointe`
--

INSERT INTO `piecejointe` (`id`, `message_id`, `type_fichier`, `nom_original`, `nom_stockage`, `chemin_fichier`, `taille_octets`, `extension`, `mime_type`, `date_upload`) VALUES
(1, 6, 'image', 'Capture d\'écran 2026-02-10 164048.png', 'msg_6_img_20260211_195603.png', 'uploads/messagerie/images/msg_6_img_20260211_195603.png', 243095, 'png', NULL, '2026-02-11 19:56:03'),
(3, 13, 'image', 'stb.png', 'msg_13_img_20260213_161956.png', 'uploads/messagerie/images/msg_13_img_20260213_161956.png', 16296, 'png', NULL, '2026-02-13 16:19:56'),
(4, 23, 'image', 'Capture d\'écran 2026-02-26 211617.png', 'msg_23_img_20260226_214336.png', 'uploads/messagerie/images/msg_23_img_20260226_214336.png', 14709, 'png', NULL, '2026-02-26 21:43:36');

--
-- Déclencheurs `piecejointe`
--
DELIMITER $$
CREATE TRIGGER `after_piece_jointe_delete` AFTER DELETE ON `piecejointe` FOR EACH ROW BEGIN
    DECLARE nb INT;
    SELECT COUNT(*) INTO nb FROM PieceJointe WHERE message_id = OLD.message_id;
    
    UPDATE Message 
    SET a_piece_jointe = IF(nb > 0, TRUE, FALSE),
        nb_pieces_jointes = nb
    WHERE id = OLD.message_id;
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `after_piece_jointe_insert` AFTER INSERT ON `piecejointe` FOR EACH ROW BEGIN
    UPDATE Message 
    SET a_piece_jointe = TRUE,
        nb_pieces_jointes = (SELECT COUNT(*) FROM PieceJointe WHERE message_id = NEW.message_id)
    WHERE id = NEW.message_id;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Structure de la table `produit_financier`
--

CREATE TABLE `produit_financier` (
  `id_produit` int(11) NOT NULL,
  `nom_produit` varchar(100) NOT NULL,
  `type_financement` varchar(50) NOT NULL,
  `taux_interet` double NOT NULL,
  `montant_min` double NOT NULL,
  `montant_max` double NOT NULL,
  `regles_financieres` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `produit_financier`
--

INSERT INTO `produit_financier` (`id_produit`, `nom_produit`, `type_financement`, `taux_interet`, `montant_min`, `montant_max`, `regles_financieres`) VALUES
(1, 'prem', 'Credit', 5.7, 10000, 50000, 'ndkfnklcnl:c');

-- --------------------------------------------------------

--
-- Structure de la table `projectagricole`
--

CREATE TABLE `projectagricole` (
  `idproject` int(11) NOT NULL,
  `agriculteur_id` int(11) NOT NULL,
  `nomproject` varchar(100) NOT NULL,
  `surface` float NOT NULL,
  `budgetdemande` decimal(12,2) NOT NULL,
  `statut` enum('en cours','accepte','refuse') NOT NULL DEFAULT 'en cours',
  `datesoumission` date NOT NULL,
  `latitude` double DEFAULT NULL,
  `longitude` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `projectagricole`
--

INSERT INTO `projectagricole` (`idproject`, `agriculteur_id`, `nomproject`, `surface`, `budgetdemande`, `statut`, `datesoumission`, `latitude`, `longitude`) VALUES
(1, 1, 'Projet Agricole A', 50.5, 10000.00, 'en cours', '2026-02-15', NULL, NULL),
(2, 1, 'Projet Agricole B', 75, 15000.00, 'en cours', '2026-02-16', NULL, NULL),
(3, 1, 'Projet Agricole C', 100, 20000.00, 'accepte', '2026-02-10', NULL, NULL),
(4, 1, 'Projet Agricole D', 120.5, 25000.00, 'refuse', '2026-02-05', NULL, NULL),
(5, 4, 'Projet Agricole E', 90, 18000.00, 'en cours', '2026-02-17', NULL, NULL),
(30, 3, 'Projet Test', 10.5, 50000.00, 'en cours', '2026-02-21', NULL, NULL),
(31, 3, 'peche', 70, 450000.00, 'en cours', '2026-02-27', 36.454634, 10.174707),
(32, 3, 'olive', 40, 500000.00, 'refuse', '2026-02-27', 35.234917, 9.998926),
(33, 3, 'orange', 81, 470000.00, 'accepte', '2026-02-27', 33.914256, 9.86709),
(34, 3, 'rayen', 57, 870000.00, 'en cours', '2026-02-27', 33.383818, 8.922266),
(35, 3, 'Carotte', 50, 70000.00, 'en cours', '2026-02-28', 33.089768, 8.944238);

-- --------------------------------------------------------

--
-- Structure de la table `rapport_journalier`
--

CREATE TABLE `rapport_journalier` (
  `id_rapport` int(11) NOT NULL,
  `date_rapport` date NOT NULL,
  `type_mesure` varchar(50) NOT NULL,
  `moyenne` double NOT NULL,
  `min` double NOT NULL,
  `max` double NOT NULL,
  `id_capteur` int(11) NOT NULL,
  `idproject` int(11) DEFAULT NULL,
  `valeur_mesuree` double DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Déchargement des données de la table `rapport_journalier`
--

INSERT INTO `rapport_journalier` (`id_rapport`, `date_rapport`, `type_mesure`, `moyenne`, `min`, `max`, `id_capteur`, `idproject`, `valeur_mesuree`) VALUES
(1, '2026-02-21', 'Température', 25, 20, 30, 8, 1, 25),
(2, '2026-02-21', 'Humidité du sol', 60, 50, 70, 9, 1, 60),
(3, '2026-02-21', 'pH du sol', 6.8, 6, 7.5, 10, 1, 6.8),
(5, '2026-02-22', 'Température', 33, 18, 26, 11, 2, 22),
(6, '2026-02-22', 'Humidité du sol', 65, 60, 70, 12, 2, 75),
(7, '2026-02-22', 'pH du sol', 7, 6.5, 7.5, 13, 2, 7),
(8, '2026-02-22', 'Température', 30, 30, 40, 23, 3, 35),
(9, '2026-02-22', 'Humidité du sol', 30, 25, 35, 24, 3, 30),
(10, '2026-02-22', 'pH du sol', 7, 7, 6, 25, 3, 7),
(28, '2026-02-27', 'terrain', 50, 50, 50, 1, NULL, NULL),
(29, '2026-02-28', 'eeee', 58, 58, 58, 28, NULL, NULL),
(30, '2026-02-28', 'eeee', 58, 58, 58, 28, NULL, NULL),
(31, '2026-02-28', 'temp', 39.26419354838709, 5.8, 92.12, 28, NULL, NULL),
(33, '2026-02-28', 'temp', 46.035000000000004, 1.78, 90.29, 28, NULL, NULL),
(34, '2026-02-28', 'temp', 8.02, 8.02, 8.02, 28, NULL, NULL),
(35, '2026-02-28', 'temp', 48.5362271062271, 0.03, 99.9, 28, NULL, NULL),
(36, '2026-02-28', 'temp', 30.905, 16.14, 45.67, 28, NULL, NULL),
(37, '2026-02-28', 'temp', 42.97, 17.45, 68.49, 28, NULL, NULL),
(38, '2026-02-28', 'temp', 6.82, 6.82, 6.82, 28, NULL, NULL),
(39, '2026-02-28', 'relev1', 80, 80, 80, 29, NULL, NULL),
(40, '2026-02-28', 'temp', 51.0390642763445, 0.02, 99.97, 28, NULL, NULL),
(41, '2026-02-28', 'temp', 51.50555555555556, 1.7, 97.45, 29, NULL, NULL);

-- --------------------------------------------------------

--
-- Structure de la table `releve_terrain`
--

CREATE TABLE `releve_terrain` (
  `id_releve` int(11) NOT NULL,
  `type_mesure` varchar(50) NOT NULL,
  `valeur_mesuree` double NOT NULL,
  `unite` varchar(20) NOT NULL,
  `date_heure` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_capteur` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `releve_terrain`
--

INSERT INTO `releve_terrain` (`id_releve`, `type_mesure`, `valeur_mesuree`, `unite`, `date_heure`, `id_capteur`) VALUES
(15762, 'temp', 70, 'degré', '2026-02-27 20:31:16', 27),
(15763, 'aaa', 78, 'degré', '2026-02-27 20:32:14', 26),
(18948, 'temp', 73.37, 'unité', '2026-02-28 18:11:36', 28),
(18949, 'temp', 43.57, 'unité', '2026-02-28 18:11:36', 29),
(18950, 'temp', 59.54, 'unité', '2026-02-28 18:11:41', 28),
(18951, 'temp', 9.53, 'unité', '2026-02-28 18:11:41', 29),
(18952, 'temp', 83.98, 'unité', '2026-02-28 18:11:46', 28),
(18953, 'temp', 56.46, 'unité', '2026-02-28 18:11:46', 29),
(18954, 'temp', 29.57, 'unité', '2026-02-28 18:11:51', 28),
(18955, 'temp', 40.46, 'unité', '2026-02-28 18:11:51', 29),
(18956, 'temp', 46.02, 'unité', '2026-02-28 18:11:56', 28),
(18957, 'temp', 38.15, 'unité', '2026-02-28 18:11:56', 29),
(18958, 'temp', 16.24, 'unité', '2026-02-28 18:12:01', 28),
(18959, 'temp', 88.6, 'unité', '2026-02-28 18:12:01', 29),
(18960, 'temp', 80.22, 'unité', '2026-02-28 18:12:06', 28),
(18961, 'temp', 39.86, 'unité', '2026-02-28 18:12:06', 29),
(18962, 'temp', 16.24, 'unité', '2026-02-28 18:12:12', 28),
(18963, 'temp', 7.19, 'unité', '2026-02-28 18:12:12', 29),
(18964, 'temp', 36.22, 'unité', '2026-02-28 18:12:17', 28),
(18965, 'temp', 0.28, 'unité', '2026-02-28 18:12:17', 29),
(18966, 'temp', 58.64, 'unité', '2026-02-28 18:12:22', 28),
(18967, 'temp', 86.82, 'unité', '2026-02-28 18:12:22', 29),
(18968, 'temp', 79.21, 'unité', '2026-02-28 18:12:27', 28),
(18969, 'temp', 48.17, 'unité', '2026-02-28 18:12:27', 29),
(18970, 'temp', 71.39, 'unité', '2026-02-28 18:12:32', 28),
(18971, 'temp', 32.04, 'unité', '2026-02-28 18:12:32', 29),
(18972, 'temp', 55.47, 'unité', '2026-02-28 18:12:37', 28),
(18973, 'temp', 66.38, 'unité', '2026-02-28 18:12:37', 29),
(18974, 'temp', 60.71, 'unité', '2026-02-28 18:12:42', 28),
(18975, 'temp', 39.67, 'unité', '2026-02-28 18:12:42', 29),
(18976, 'temp', 35.48, 'unité', '2026-02-28 18:12:47', 28),
(18977, 'temp', 58.63, 'unité', '2026-02-28 18:12:47', 29),
(18978, 'temp', 28.79, 'unité', '2026-02-28 18:12:52', 28),
(18979, 'temp', 81.76, 'unité', '2026-02-28 18:12:52', 29),
(18980, 'temp', 63.41, 'unité', '2026-02-28 18:12:57', 28),
(18981, 'temp', 4, 'unité', '2026-02-28 18:12:57', 29),
(18982, 'temp', 91.72, 'unité', '2026-02-28 18:13:02', 28),
(18983, 'temp', 30.98, 'unité', '2026-02-28 18:13:02', 29),
(18984, 'temp', 97.87, 'unité', '2026-02-28 18:13:07', 28),
(18985, 'temp', 55.63, 'unité', '2026-02-28 18:13:07', 29),
(18986, 'temp', 13.2, 'unité', '2026-02-28 18:13:12', 28),
(18987, 'temp', 53.65, 'unité', '2026-02-28 18:13:12', 29),
(18988, 'temp', 58.3, 'unité', '2026-02-28 18:13:17', 28),
(18989, 'temp', 24.47, 'unité', '2026-02-28 18:13:17', 29),
(18990, 'temp', 67.25, 'unité', '2026-02-28 18:13:22', 28),
(18991, 'temp', 35.45, 'unité', '2026-02-28 18:13:22', 29),
(18992, 'temp', 88.17, 'unité', '2026-02-28 18:13:27', 28),
(18993, 'temp', 92.91, 'unité', '2026-02-28 18:13:27', 29),
(18994, 'temp', 37.42, 'unité', '2026-02-28 18:13:32', 28),
(18995, 'temp', 22.08, 'unité', '2026-02-28 18:13:32', 29),
(18996, 'temp', 37.45, 'unité', '2026-02-28 18:13:38', 28),
(18997, 'temp', 64.34, 'unité', '2026-02-28 18:13:38', 29),
(18998, 'temp', 65.28, 'unité', '2026-02-28 18:13:43', 28),
(18999, 'temp', 92.22, 'unité', '2026-02-28 18:13:43', 29),
(19000, 'temp', 53.04, 'unité', '2026-02-28 18:13:48', 28),
(19001, 'temp', 87.69, 'unité', '2026-02-28 18:13:48', 29),
(19002, 'temp', 96.57, 'unité', '2026-02-28 18:13:53', 28),
(19003, 'temp', 66.29, 'unité', '2026-02-28 18:13:53', 29),
(19004, 'temp', 50.23, 'unité', '2026-02-28 18:13:58', 28),
(19005, 'temp', 68.94, 'unité', '2026-02-28 18:13:58', 29),
(19006, 'temp', 77.48, 'unité', '2026-02-28 18:14:03', 28),
(19007, 'temp', 50.82, 'unité', '2026-02-28 18:14:03', 29),
(19008, 'temp', 50.02, 'unité', '2026-02-28 18:14:08', 28),
(19009, 'temp', 27.29, 'unité', '2026-02-28 18:14:08', 29),
(19010, 'temp', 39.03, 'unité', '2026-02-28 18:14:13', 28),
(19011, 'temp', 20.13, 'unité', '2026-02-28 18:14:13', 29),
(19012, 'temp', 3.86, 'unité', '2026-02-28 18:14:18', 28),
(19013, 'temp', 42.19, 'unité', '2026-02-28 18:14:18', 29),
(19014, 'temp', 71.64, 'unité', '2026-02-28 18:14:23', 28),
(19015, 'temp', 41.39, 'unité', '2026-02-28 18:14:23', 29),
(19016, 'temp', 54.2, 'unité', '2026-02-28 18:14:28', 28),
(19017, 'temp', 11.84, 'unité', '2026-02-28 18:14:28', 29),
(19018, 'temp', 1.59, 'unité', '2026-02-28 18:14:33', 28),
(19019, 'temp', 58.09, 'unité', '2026-02-28 18:14:33', 29),
(19020, 'temp', 7.25, 'unité', '2026-02-28 18:14:38', 28),
(19021, 'temp', 88.98, 'unité', '2026-02-28 18:14:38', 29),
(19022, 'temp', 76.94, 'unité', '2026-02-28 18:14:43', 28),
(19023, 'temp', 62.57, 'unité', '2026-02-28 18:14:43', 29),
(19024, 'temp', 93.75, 'unité', '2026-02-28 18:14:48', 28),
(19025, 'temp', 35.25, 'unité', '2026-02-28 18:14:48', 29),
(19026, 'temp', 22.23, 'unité', '2026-02-28 18:14:53', 28),
(19027, 'temp', 56.69, 'unité', '2026-02-28 18:14:53', 29),
(19028, 'temp', 36.17, 'unité', '2026-02-28 18:14:58', 28),
(19029, 'temp', 0.78, 'unité', '2026-02-28 18:14:58', 29),
(19030, 'temp', 88.37, 'unité', '2026-02-28 18:15:03', 28),
(19031, 'temp', 14.87, 'unité', '2026-02-28 18:15:03', 29),
(19032, 'temp', 32.92, 'unité', '2026-02-28 18:15:08', 28),
(19033, 'temp', 21.68, 'unité', '2026-02-28 18:15:09', 29),
(19034, 'temp', 57.77, 'unité', '2026-02-28 18:15:14', 28),
(19035, 'temp', 44.66, 'unité', '2026-02-28 18:15:14', 29),
(19036, 'temp', 77.39, 'unité', '2026-02-28 18:15:19', 28),
(19037, 'temp', 8.59, 'unité', '2026-02-28 18:15:19', 29),
(19038, 'temp', 30.32, 'unité', '2026-02-28 18:15:24', 28),
(19039, 'temp', 20.88, 'unité', '2026-02-28 18:15:24', 29),
(19040, 'temp', 24.15, 'unité', '2026-02-28 18:15:29', 28),
(19041, 'temp', 38.44, 'unité', '2026-02-28 18:15:29', 29),
(19042, 'temp', 24.55, 'unité', '2026-02-28 18:15:34', 28),
(19043, 'temp', 49.34, 'unité', '2026-02-28 18:15:34', 29),
(19044, 'temp', 1.73, 'unité', '2026-02-28 18:15:39', 28),
(19045, 'temp', 5.81, 'unité', '2026-02-28 18:15:39', 29),
(19046, 'temp', 23.56, 'unité', '2026-02-28 18:15:44', 28),
(19047, 'temp', 97.28, 'unité', '2026-02-28 18:15:44', 29),
(19048, 'temp', 23.53, 'unité', '2026-02-28 18:15:49', 28),
(19049, 'temp', 62.22, 'unité', '2026-02-28 18:15:49', 29),
(19050, 'temp', 53.6, 'unité', '2026-02-28 18:15:54', 28),
(19051, 'temp', 33.48, 'unité', '2026-02-28 18:15:54', 29),
(19052, 'temp', 16.19, 'unité', '2026-02-28 18:15:59', 28),
(19053, 'temp', 70.58, 'unité', '2026-02-28 18:15:59', 29),
(19054, 'temp', 74.85, 'unité', '2026-02-28 18:16:04', 28),
(19055, 'temp', 54.27, 'unité', '2026-02-28 18:16:04', 29),
(19056, 'temp', 55.19, 'unité', '2026-02-28 18:16:09', 28),
(19057, 'temp', 62.94, 'unité', '2026-02-28 18:16:09', 29),
(19058, 'temp', 20.45, 'unité', '2026-02-28 18:16:14', 28),
(19059, 'temp', 43.32, 'unité', '2026-02-28 18:16:14', 29),
(19060, 'temp', 28.63, 'unité', '2026-02-28 18:16:19', 28),
(19061, 'temp', 44.61, 'unité', '2026-02-28 18:16:19', 29),
(19062, 'temp', 15.83, 'unité', '2026-02-28 18:16:24', 28),
(19063, 'temp', 73.16, 'unité', '2026-02-28 18:16:24', 29),
(19064, 'temp', 36.03, 'unité', '2026-02-28 18:16:29', 28),
(19065, 'temp', 40.34, 'unité', '2026-02-28 18:16:29', 29),
(19066, 'temp', 29.5, 'unité', '2026-02-28 18:16:34', 28),
(19067, 'temp', 33.99, 'unité', '2026-02-28 18:16:34', 29),
(19068, 'temp', 32.95, 'unité', '2026-02-28 18:16:40', 28),
(19069, 'temp', 79.44, 'unité', '2026-02-28 18:16:40', 29),
(19070, 'temp', 73.25, 'unité', '2026-02-28 18:16:45', 28),
(19071, 'temp', 48.63, 'unité', '2026-02-28 18:16:45', 29),
(19072, 'temp', 36.84, 'unité', '2026-02-28 18:16:50', 28),
(19073, 'temp', 23.53, 'unité', '2026-02-28 18:16:50', 29),
(19074, 'temp', 17.66, 'unité', '2026-02-28 18:16:55', 28),
(19075, 'temp', 0.66, 'unité', '2026-02-28 18:16:55', 29),
(19076, 'temp', 41.27, 'unité', '2026-02-28 18:17:00', 28),
(19077, 'temp', 86.69, 'unité', '2026-02-28 18:17:00', 29),
(19078, 'temp', 58.44, 'unité', '2026-02-28 18:17:05', 28),
(19079, 'temp', 51.88, 'unité', '2026-02-28 18:17:05', 29),
(19080, 'temp', 8.95, 'unité', '2026-02-28 18:17:10', 28),
(19081, 'temp', 3.56, 'unité', '2026-02-28 18:17:10', 29),
(19082, 'temp', 44.02, 'unité', '2026-02-28 18:17:15', 28),
(19083, 'temp', 13.31, 'unité', '2026-02-28 18:17:15', 29),
(19084, 'temp', 56.18, 'unité', '2026-02-28 18:17:20', 28),
(19085, 'temp', 34.2, 'unité', '2026-02-28 18:17:20', 29),
(19086, 'temp', 97.59, 'unité', '2026-02-28 18:17:25', 28),
(19087, 'temp', 14.32, 'unité', '2026-02-28 18:17:25', 29),
(19088, 'temp', 72.44, 'unité', '2026-02-28 18:17:30', 28),
(19089, 'temp', 15.06, 'unité', '2026-02-28 18:17:30', 29),
(19090, 'temp', 8.83, 'unité', '2026-02-28 18:17:35', 28),
(19091, 'temp', 79.16, 'unité', '2026-02-28 18:17:35', 29),
(19092, 'temp', 5.96, 'unité', '2026-02-28 18:17:40', 28),
(19093, 'temp', 16.81, 'unité', '2026-02-28 18:17:40', 29),
(19094, 'temp', 85.19, 'unité', '2026-02-28 18:17:45', 28),
(19095, 'temp', 96.47, 'unité', '2026-02-28 18:17:45', 29),
(19096, 'temp', 21.09, 'unité', '2026-02-28 18:17:50', 28),
(19097, 'temp', 47.1, 'unité', '2026-02-28 18:17:50', 29),
(19098, 'temp', 94.05, 'unité', '2026-02-28 18:17:55', 28),
(19099, 'temp', 48.02, 'unité', '2026-02-28 18:17:56', 29),
(19100, 'temp', 7.36, 'unité', '2026-02-28 18:18:01', 28),
(19101, 'temp', 70.42, 'unité', '2026-02-28 18:18:01', 29),
(19102, 'temp', 50.62, 'unité', '2026-02-28 18:18:06', 28),
(19103, 'temp', 20.54, 'unité', '2026-02-28 18:18:06', 29),
(19104, 'temp', 75.28, 'unité', '2026-02-28 18:18:11', 28),
(19105, 'temp', 16.24, 'unité', '2026-02-28 18:18:11', 29),
(19106, 'temp', 8.89, 'unité', '2026-02-28 18:18:16', 28),
(19107, 'temp', 51.2, 'unité', '2026-02-28 18:18:16', 29),
(19108, 'temp', 70.86, 'unité', '2026-02-28 18:18:21', 28),
(19109, 'temp', 14.71, 'unité', '2026-02-28 18:18:21', 29),
(19110, 'temp', 51.23, 'unité', '2026-02-28 18:18:26', 28),
(19111, 'temp', 64.91, 'unité', '2026-02-28 18:18:26', 29),
(19112, 'temp', 78.66, 'unité', '2026-02-28 18:18:31', 28),
(19113, 'temp', 90.37, 'unité', '2026-02-28 18:18:31', 29),
(19114, 'temp', 11.81, 'unité', '2026-02-28 18:18:36', 28),
(19115, 'temp', 29.28, 'unité', '2026-02-28 18:18:36', 29),
(19116, 'temp', 63.06, 'unité', '2026-02-28 18:18:41', 28),
(19117, 'temp', 0.29, 'unité', '2026-02-28 18:18:41', 29),
(19118, 'temp', 80.88, 'unité', '2026-02-28 18:18:46', 28),
(19119, 'temp', 39.88, 'unité', '2026-02-28 18:18:46', 29),
(19120, 'temp', 3.68, 'unité', '2026-02-28 18:18:51', 28),
(19121, 'temp', 50.27, 'unité', '2026-02-28 18:18:51', 29),
(19122, 'temp', 66.84, 'unité', '2026-02-28 18:18:56', 28),
(19123, 'temp', 52.86, 'unité', '2026-02-28 18:18:56', 29),
(19124, 'temp', 69.71, 'unité', '2026-02-28 18:19:01', 28),
(19125, 'temp', 16.32, 'unité', '2026-02-28 18:19:01', 29),
(19126, 'temp', 97.93, 'unité', '2026-02-28 18:19:06', 28),
(19127, 'temp', 68.05, 'unité', '2026-02-28 18:19:06', 29),
(19128, 'temp', 84.16, 'unité', '2026-02-28 18:19:12', 28),
(19129, 'temp', 24.35, 'unité', '2026-02-28 18:19:12', 29),
(19130, 'temp', 33.61, 'unité', '2026-02-28 18:19:17', 28),
(19131, 'temp', 80.42, 'unité', '2026-02-28 18:19:17', 29),
(19132, 'temp', 21.59, 'unité', '2026-02-28 18:19:22', 28),
(19133, 'temp', 70.14, 'unité', '2026-02-28 18:19:22', 29),
(19134, 'temp', 89.98, 'unité', '2026-02-28 18:19:27', 28),
(19135, 'temp', 62.37, 'unité', '2026-02-28 18:19:27', 29),
(19136, 'temp', 6.71, 'unité', '2026-02-28 18:19:32', 28),
(19137, 'temp', 91.92, 'unité', '2026-02-28 18:19:32', 29),
(19138, 'temp', 82.73, 'unité', '2026-02-28 18:19:37', 28),
(19139, 'temp', 85.33, 'unité', '2026-02-28 18:19:37', 29),
(19140, 'temp', 26.56, 'unité', '2026-02-28 18:19:42', 28),
(19141, 'temp', 35.58, 'unité', '2026-02-28 18:19:42', 29),
(19142, 'temp', 57.86, 'unité', '2026-02-28 18:19:47', 28),
(19143, 'temp', 75.1, 'unité', '2026-02-28 18:19:47', 29),
(19144, 'temp', 21.63, 'unité', '2026-02-28 18:19:52', 28),
(19145, 'temp', 48.34, 'unité', '2026-02-28 18:19:52', 29),
(19146, 'temp', 72.38, 'unité', '2026-02-28 18:19:57', 28),
(19147, 'temp', 59.42, 'unité', '2026-02-28 18:19:57', 29),
(19148, 'temp', 49.36, 'unité', '2026-02-28 18:20:02', 28),
(19149, 'temp', 96.74, 'unité', '2026-02-28 18:20:02', 29),
(19150, 'temp', 39.57, 'unité', '2026-02-28 18:20:07', 28),
(19151, 'temp', 47.85, 'unité', '2026-02-28 18:20:08', 29),
(19152, 'temp', 34.17, 'unité', '2026-02-28 18:20:13', 28),
(19153, 'temp', 21.43, 'unité', '2026-02-28 18:20:13', 29),
(19154, 'temp', 52.48, 'unité', '2026-02-28 18:20:18', 28),
(19155, 'temp', 94.56, 'unité', '2026-02-28 18:20:18', 29),
(19156, 'temp', 31.57, 'unité', '2026-02-28 18:20:23', 28),
(19157, 'temp', 16.24, 'unité', '2026-02-28 18:20:23', 29),
(19158, 'temp', 35.09, 'unité', '2026-02-28 18:20:28', 28),
(19159, 'temp', 3.99, 'unité', '2026-02-28 18:20:28', 29),
(19160, 'temp', 8.45, 'unité', '2026-02-28 18:20:33', 28),
(19161, 'temp', 69.7, 'unité', '2026-02-28 18:20:33', 29),
(19162, 'temp', 91.05, 'unité', '2026-02-28 18:20:38', 28),
(19163, 'temp', 25.2, 'unité', '2026-02-28 18:20:38', 29),
(19164, 'temp', 35.02, 'unité', '2026-02-28 18:20:43', 28),
(19165, 'temp', 22.08, 'unité', '2026-02-28 18:20:43', 29),
(19166, 'temp', 87.15, 'unité', '2026-02-28 18:20:48', 28),
(19167, 'temp', 22.79, 'unité', '2026-02-28 18:20:48', 29),
(19168, 'temp', 61.48, 'unité', '2026-02-28 18:20:53', 28),
(19169, 'temp', 54.49, 'unité', '2026-02-28 18:20:53', 29),
(19170, 'temp', 63.49, 'unité', '2026-02-28 18:20:58', 28),
(19171, 'temp', 84.9, 'unité', '2026-02-28 18:20:58', 29),
(19172, 'temp', 6.15, 'unité', '2026-02-28 18:21:03', 28),
(19173, 'temp', 13.73, 'unité', '2026-02-28 18:21:03', 29),
(19174, 'temp', 97.34, 'unité', '2026-02-28 18:21:08', 28),
(19175, 'temp', 20.44, 'unité', '2026-02-28 18:21:08', 29),
(19176, 'temp', 75.41, 'unité', '2026-02-28 18:21:14', 28),
(19177, 'temp', 38.23, 'unité', '2026-02-28 18:21:14', 29),
(19178, 'temp', 52.12, 'unité', '2026-02-28 18:21:19', 28),
(19179, 'temp', 9.89, 'unité', '2026-02-28 18:21:19', 29),
(19180, 'temp', 28.22, 'unité', '2026-02-28 18:21:24', 28),
(19181, 'temp', 16.91, 'unité', '2026-02-28 18:21:24', 29),
(19182, 'temp', 1.84, 'unité', '2026-02-28 18:21:29', 28),
(19183, 'temp', 40.56, 'unité', '2026-02-28 18:21:29', 29),
(19184, 'temp', 20.02, 'unité', '2026-02-28 18:21:34', 28),
(19185, 'temp', 92.35, 'unité', '2026-02-28 18:21:34', 29),
(19186, 'temp', 41.87, 'unité', '2026-02-28 18:21:39', 28),
(19187, 'temp', 63.47, 'unité', '2026-02-28 18:21:39', 29),
(19188, 'temp', 87.07, 'unité', '2026-02-28 18:21:44', 28),
(19189, 'temp', 85.19, 'unité', '2026-02-28 18:21:44', 29),
(19190, 'temp', 11.24, 'unité', '2026-02-28 18:21:49', 28),
(19191, 'temp', 30.31, 'unité', '2026-02-28 18:21:49', 29),
(19192, 'temp', 86.54, 'unité', '2026-02-28 18:21:54', 28),
(19193, 'temp', 24.01, 'unité', '2026-02-28 18:21:54', 29),
(19194, 'temp', 2.06, 'unité', '2026-02-28 18:21:59', 28),
(19195, 'temp', 77.23, 'unité', '2026-02-28 18:21:59', 29),
(19196, 'temp', 61.6, 'unité', '2026-02-28 18:22:04', 28),
(19197, 'temp', 89.29, 'unité', '2026-02-28 18:22:04', 29),
(19198, 'temp', 5.16, 'unité', '2026-02-28 18:22:09', 28),
(19199, 'temp', 1.9, 'unité', '2026-02-28 18:22:09', 29),
(19200, 'temp', 81.12, 'unité', '2026-02-28 18:22:14', 28),
(19201, 'temp', 18.5, 'unité', '2026-02-28 18:22:14', 29),
(19202, 'temp', 32.93, 'unité', '2026-02-28 18:22:19', 28),
(19203, 'temp', 48.8, 'unité', '2026-02-28 18:22:19', 29),
(19204, 'temp', 14.46, 'unité', '2026-02-28 18:22:25', 28),
(19205, 'temp', 7.87, 'unité', '2026-02-28 18:22:25', 29),
(19206, 'temp', 34.44, 'unité', '2026-02-28 18:22:30', 28),
(19207, 'temp', 50.46, 'unité', '2026-02-28 18:22:30', 29),
(19208, 'temp', 57.83, 'unité', '2026-02-28 18:22:35', 28),
(19209, 'temp', 81.84, 'unité', '2026-02-28 18:22:35', 29),
(19210, 'temp', 32.16, 'unité', '2026-02-28 18:22:40', 28),
(19211, 'temp', 30.53, 'unité', '2026-02-28 18:22:40', 29),
(19212, 'temp', 53.12, 'unité', '2026-02-28 18:22:45', 28),
(19213, 'temp', 34.08, 'unité', '2026-02-28 18:22:45', 29),
(19214, 'temp', 36.62, 'unité', '2026-02-28 18:22:50', 28),
(19215, 'temp', 9.89, 'unité', '2026-02-28 18:22:50', 29),
(19216, 'temp', 25.53, 'unité', '2026-02-28 18:22:55', 28),
(19217, 'temp', 61.28, 'unité', '2026-02-28 18:22:55', 29),
(19218, 'temp', 62.53, 'unité', '2026-02-28 18:23:00', 28),
(19219, 'temp', 98.61, 'unité', '2026-02-28 18:23:00', 29),
(19220, 'temp', 24.09, 'unité', '2026-02-28 18:23:05', 28),
(19221, 'temp', 2.33, 'unité', '2026-02-28 18:23:05', 29),
(19222, 'temp', 16.15, 'unité', '2026-02-28 18:23:10', 28),
(19223, 'temp', 33.53, 'unité', '2026-02-28 18:23:10', 29),
(19224, 'temp', 96.74, 'unité', '2026-02-28 18:23:15', 28),
(19225, 'temp', 46.43, 'unité', '2026-02-28 18:23:15', 29),
(19226, 'temp', 88.81, 'unité', '2026-02-28 18:23:20', 28),
(19227, 'temp', 36.31, 'unité', '2026-02-28 18:23:20', 29),
(19228, 'temp', 11.18, 'unité', '2026-02-28 18:23:25', 28),
(19229, 'temp', 94.47, 'unité', '2026-02-28 18:23:25', 29),
(19230, 'temp', 23.1, 'unité', '2026-02-28 18:23:30', 28),
(19231, 'temp', 96.61, 'unité', '2026-02-28 18:23:30', 29),
(19232, 'temp', 36.05, 'unité', '2026-02-28 18:23:36', 28),
(19233, 'temp', 8.67, 'unité', '2026-02-28 18:23:36', 29),
(19234, 'temp', 82.82, 'unité', '2026-02-28 18:23:41', 28),
(19235, 'temp', 53.04, 'unité', '2026-02-28 18:23:41', 29),
(19236, 'temp', 92.08, 'unité', '2026-02-28 18:23:46', 28),
(19237, 'temp', 9.82, 'unité', '2026-02-28 18:23:46', 29),
(19238, 'temp', 57.32, 'unité', '2026-02-28 18:23:51', 28),
(19239, 'temp', 61.84, 'unité', '2026-02-28 18:23:51', 29),
(19240, 'temp', 91.13, 'unité', '2026-02-28 18:23:56', 28),
(19241, 'temp', 34.38, 'unité', '2026-02-28 18:23:56', 29),
(19242, 'temp', 32.75, 'unité', '2026-02-28 18:24:01', 28),
(19243, 'temp', 65.1, 'unité', '2026-02-28 18:24:01', 29),
(19244, 'temp', 82.5, 'unité', '2026-02-28 18:24:06', 28),
(19245, 'temp', 59, 'unité', '2026-02-28 18:24:06', 29),
(19246, 'temp', 33.92, 'unité', '2026-02-28 18:24:11', 28),
(19247, 'temp', 24.92, 'unité', '2026-02-28 18:24:11', 29),
(19248, 'temp', 46.11, 'unité', '2026-02-28 18:24:16', 28),
(19249, 'temp', 76.03, 'unité', '2026-02-28 18:24:16', 29),
(19250, 'temp', 40.64, 'unité', '2026-02-28 18:24:21', 28),
(19251, 'temp', 71.94, 'unité', '2026-02-28 18:24:21', 29),
(19252, 'temp', 99.57, 'unité', '2026-02-28 18:24:26', 28),
(19253, 'temp', 71.85, 'unité', '2026-02-28 18:24:26', 29),
(19254, 'temp', 87.89, 'unité', '2026-02-28 18:24:31', 28),
(19255, 'temp', 56.23, 'unité', '2026-02-28 18:24:31', 29),
(19256, 'temp', 85.57, 'unité', '2026-02-28 18:24:36', 28),
(19257, 'temp', 4.27, 'unité', '2026-02-28 18:24:36', 29),
(19258, 'temp', 89.67, 'unité', '2026-02-28 18:24:41', 28),
(19259, 'temp', 59.7, 'unité', '2026-02-28 18:24:41', 29),
(19260, 'temp', 35.8, 'unité', '2026-02-28 18:24:46', 28),
(19261, 'temp', 77.85, 'unité', '2026-02-28 18:24:46', 29),
(19262, 'temp', 8.85, 'unité', '2026-02-28 18:24:52', 28),
(19263, 'temp', 91.53, 'unité', '2026-02-28 18:24:52', 29),
(19264, 'temp', 74.79, 'unité', '2026-02-28 18:24:57', 28),
(19265, 'temp', 47.73, 'unité', '2026-02-28 18:24:57', 29),
(19266, 'temp', 98.56, 'unité', '2026-02-28 18:25:02', 28),
(19267, 'temp', 54.61, 'unité', '2026-02-28 18:25:02', 29),
(19268, 'temp', 6.53, 'unité', '2026-02-28 18:25:07', 28),
(19269, 'temp', 5.84, 'unité', '2026-02-28 18:25:07', 29),
(19270, 'temp', 15.56, 'unité', '2026-02-28 18:25:12', 28),
(19271, 'temp', 79.38, 'unité', '2026-02-28 18:25:12', 29),
(19272, 'temp', 89.64, 'unité', '2026-02-28 18:25:17', 28),
(19273, 'temp', 84.76, 'unité', '2026-02-28 18:25:17', 29),
(19274, 'temp', 98.08, 'unité', '2026-02-28 18:25:22', 28),
(19275, 'temp', 50.32, 'unité', '2026-02-28 18:25:22', 29),
(19276, 'temp', 21.9, 'unité', '2026-02-28 18:25:27', 28),
(19277, 'temp', 29.89, 'unité', '2026-02-28 18:25:27', 29),
(19278, 'temp', 34.55, 'unité', '2026-02-28 18:25:32', 28),
(19279, 'temp', 15.17, 'unité', '2026-02-28 18:25:32', 29),
(19280, 'temp', 87.9, 'unité', '2026-02-28 18:25:37', 28),
(19281, 'temp', 65.77, 'unité', '2026-02-28 18:25:37', 29),
(19282, 'temp', 58.7, 'unité', '2026-02-28 18:25:42', 28),
(19283, 'temp', 81.22, 'unité', '2026-02-28 18:25:42', 29),
(19284, 'temp', 35.39, 'unité', '2026-02-28 18:25:47', 28),
(19285, 'temp', 40.84, 'unité', '2026-02-28 18:25:47', 29),
(19286, 'temp', 41.89, 'unité', '2026-02-28 18:25:52', 28),
(19287, 'temp', 34.81, 'unité', '2026-02-28 18:25:52', 29),
(19288, 'temp', 66.05, 'unité', '2026-02-28 18:25:57', 28),
(19289, 'temp', 66.72, 'unité', '2026-02-28 18:25:57', 29),
(19290, 'temp', 26.65, 'unité', '2026-02-28 18:26:02', 28),
(19291, 'temp', 28.3, 'unité', '2026-02-28 18:26:02', 29),
(19292, 'temp', 96.33, 'unité', '2026-02-28 18:26:08', 28),
(19293, 'temp', 89.26, 'unité', '2026-02-28 18:26:08', 29),
(19294, 'temp', 83.91, 'unité', '2026-02-28 18:26:13', 28),
(19295, 'temp', 3.95, 'unité', '2026-02-28 18:26:13', 29),
(19296, 'temp', 89.17, 'unité', '2026-02-28 18:26:18', 28),
(19297, 'temp', 44.04, 'unité', '2026-02-28 18:26:18', 29),
(19298, 'temp', 71.5, 'unité', '2026-02-28 18:26:23', 28),
(19299, 'temp', 55.63, 'unité', '2026-02-28 18:26:23', 29),
(19300, 'temp', 94.23, 'unité', '2026-02-28 18:26:28', 28),
(19301, 'temp', 46.94, 'unité', '2026-02-28 18:26:28', 29),
(19302, 'temp', 36.02, 'unité', '2026-02-28 18:26:33', 28),
(19303, 'temp', 57.27, 'unité', '2026-02-28 18:26:33', 29),
(19304, 'temp', 46.48, 'unité', '2026-02-28 18:26:38', 28),
(19305, 'temp', 82.06, 'unité', '2026-02-28 18:26:38', 29),
(19306, 'temp', 76.3, 'unité', '2026-02-28 18:26:43', 28),
(19307, 'temp', 56.69, 'unité', '2026-02-28 18:26:43', 29),
(19308, 'temp', 46.5, 'unité', '2026-02-28 18:26:48', 28),
(19309, 'temp', 61.01, 'unité', '2026-02-28 18:26:48', 29),
(19310, 'temp', 5.69, 'unité', '2026-02-28 18:26:53', 28),
(19311, 'temp', 32.54, 'unité', '2026-02-28 18:26:53', 29),
(19312, 'temp', 87.38, 'unité', '2026-02-28 18:26:58', 28),
(19313, 'temp', 39.26, 'unité', '2026-02-28 18:26:58', 29),
(19314, 'temp', 96.75, 'unité', '2026-02-28 18:27:03', 28),
(19315, 'temp', 30.3, 'unité', '2026-02-28 18:27:03', 29),
(19316, 'temp', 71.21, 'unité', '2026-02-28 18:27:08', 28),
(19317, 'temp', 56.94, 'unité', '2026-02-28 18:27:08', 29),
(19318, 'temp', 31.14, 'unité', '2026-02-28 18:27:13', 28),
(19319, 'temp', 76.56, 'unité', '2026-02-28 18:27:13', 29),
(19320, 'temp', 42.78, 'unité', '2026-02-28 18:27:18', 28),
(19321, 'temp', 76.48, 'unité', '2026-02-28 18:27:18', 29),
(19322, 'temp', 44.25, 'unité', '2026-02-28 18:27:24', 28),
(19323, 'temp', 85.58, 'unité', '2026-02-28 18:27:24', 29),
(19324, 'temp', 71.54, 'unité', '2026-02-28 18:27:29', 28),
(19325, 'temp', 31.47, 'unité', '2026-02-28 18:27:29', 29),
(19326, 'temp', 86.08, 'unité', '2026-02-28 18:27:34', 28),
(19327, 'temp', 10.61, 'unité', '2026-02-28 18:27:34', 29),
(19328, 'temp', 26.2, 'unité', '2026-02-28 18:27:39', 28),
(19329, 'temp', 2.15, 'unité', '2026-02-28 18:27:39', 29),
(19330, 'temp', 14.38, 'unité', '2026-02-28 18:27:44', 28),
(19331, 'temp', 53.47, 'unité', '2026-02-28 18:27:44', 29),
(19332, 'temp', 15.97, 'unité', '2026-02-28 18:27:49', 28),
(19333, 'temp', 38.79, 'unité', '2026-02-28 18:27:49', 29),
(19334, 'temp', 64.84, 'unité', '2026-02-28 18:27:54', 28),
(19335, 'temp', 54.62, 'unité', '2026-02-28 18:27:54', 29),
(19336, 'temp', 99.09, 'unité', '2026-02-28 18:27:59', 28),
(19337, 'temp', 71.01, 'unité', '2026-02-28 18:27:59', 29),
(19338, 'temp', 14.46, 'unité', '2026-02-28 18:28:04', 28),
(19339, 'temp', 61.62, 'unité', '2026-02-28 18:28:04', 29),
(19340, 'temp', 16.17, 'unité', '2026-02-28 18:28:09', 28),
(19341, 'temp', 54.95, 'unité', '2026-02-28 18:28:09', 29),
(19342, 'temp', 71.17, 'unité', '2026-02-28 18:28:14', 28),
(19343, 'temp', 49.6, 'unité', '2026-02-28 18:28:14', 29),
(19344, 'temp', 79.81, 'unité', '2026-02-28 18:28:19', 28),
(19345, 'temp', 20.52, 'unité', '2026-02-28 18:28:19', 29),
(19346, 'temp', 43.99, 'unité', '2026-02-28 18:28:24', 28),
(19347, 'temp', 68.8, 'unité', '2026-02-28 18:28:24', 29),
(19348, 'temp', 85.33, 'unité', '2026-02-28 18:28:29', 28),
(19349, 'temp', 19.72, 'unité', '2026-02-28 18:28:30', 29),
(19350, 'temp', 16.85, 'unité', '2026-02-28 18:28:35', 28),
(19351, 'temp', 19.81, 'unité', '2026-02-28 18:28:35', 29),
(19352, 'temp', 46.99, 'unité', '2026-02-28 18:28:40', 28),
(19353, 'temp', 66.52, 'unité', '2026-02-28 18:28:40', 29),
(19354, 'temp', 95.5, 'unité', '2026-02-28 18:28:45', 28),
(19355, 'temp', 37.94, 'unité', '2026-02-28 18:28:45', 29),
(19356, 'temp', 59.43, 'unité', '2026-02-28 18:28:50', 28),
(19357, 'temp', 18.44, 'unité', '2026-02-28 18:28:50', 29),
(19358, 'temp', 70.45, 'unité', '2026-02-28 18:28:55', 28),
(19359, 'temp', 84.28, 'unité', '2026-02-28 18:28:55', 29),
(19360, 'temp', 33.7, 'unité', '2026-02-28 18:29:00', 28),
(19361, 'temp', 90.92, 'unité', '2026-02-28 18:29:00', 29),
(19362, 'temp', 66.81, 'unité', '2026-02-28 18:29:05', 28),
(19363, 'temp', 56.36, 'unité', '2026-02-28 18:29:05', 29),
(19364, 'temp', 37.36, 'unité', '2026-02-28 18:29:10', 28),
(19365, 'temp', 25.14, 'unité', '2026-02-28 18:29:10', 29),
(19366, 'temp', 23.4, 'unité', '2026-02-28 18:29:15', 28),
(19367, 'temp', 21.12, 'unité', '2026-02-28 18:29:15', 29),
(19368, 'temp', 98.74, 'unité', '2026-02-28 18:29:20', 28),
(19369, 'temp', 17.07, 'unité', '2026-02-28 18:29:20', 29),
(19370, 'temp', 35.21, 'unité', '2026-02-28 18:29:25', 28),
(19371, 'temp', 25.02, 'unité', '2026-02-28 18:29:25', 29),
(19372, 'temp', 90.19, 'unité', '2026-02-28 18:29:30', 28),
(19373, 'temp', 22.45, 'unité', '2026-02-28 18:29:30', 29),
(19374, 'temp', 14.03, 'unité', '2026-02-28 18:29:35', 28),
(19375, 'temp', 35.49, 'unité', '2026-02-28 18:29:36', 29),
(19376, 'temp', 35.37, 'unité', '2026-02-28 18:29:41', 28),
(19377, 'temp', 58.54, 'unité', '2026-02-28 18:29:41', 29),
(19378, 'temp', 73.17, 'unité', '2026-02-28 18:29:46', 28),
(19379, 'temp', 34.16, 'unité', '2026-02-28 18:29:46', 29),
(19380, 'temp', 40.35, 'unité', '2026-02-28 18:29:51', 28),
(19381, 'temp', 17.45, 'unité', '2026-02-28 18:29:51', 29),
(19382, 'temp', 24.67, 'unité', '2026-02-28 18:29:56', 28),
(19383, 'temp', 51.27, 'unité', '2026-02-28 18:29:56', 29),
(19384, 'temp', 78.49, 'unité', '2026-02-28 18:30:01', 28),
(19385, 'temp', 40.41, 'unité', '2026-02-28 18:30:01', 29),
(19386, 'temp', 36.95, 'unité', '2026-02-28 18:30:06', 28),
(19387, 'temp', 75.76, 'unité', '2026-02-28 18:30:06', 29),
(19388, 'temp', 94, 'unité', '2026-02-28 18:30:11', 28),
(19389, 'temp', 10.39, 'unité', '2026-02-28 18:30:11', 29),
(19390, 'temp', 9.91, 'unité', '2026-02-28 18:30:16', 28),
(19391, 'temp', 2.69, 'unité', '2026-02-28 18:30:16', 29),
(19392, 'temp', 19.17, 'unité', '2026-02-28 18:30:21', 28),
(19393, 'temp', 63.91, 'unité', '2026-02-28 18:30:21', 29),
(19394, 'temp', 49.88, 'unité', '2026-02-28 18:30:26', 28),
(19395, 'temp', 59.46, 'unité', '2026-02-28 18:30:26', 29),
(19396, 'temp', 78.38, 'unité', '2026-02-28 18:30:31', 28),
(19397, 'temp', 5.44, 'unité', '2026-02-28 18:30:31', 29),
(19398, 'temp', 73.14, 'unité', '2026-02-28 18:30:36', 28),
(19399, 'temp', 75.71, 'unité', '2026-02-28 18:30:36', 29),
(19400, 'temp', 32.64, 'unité', '2026-02-28 18:30:41', 28),
(19401, 'temp', 31.54, 'unité', '2026-02-28 18:30:41', 29),
(19402, 'temp', 18.18, 'unité', '2026-02-28 18:30:46', 28),
(19403, 'temp', 75.24, 'unité', '2026-02-28 18:30:46', 29),
(19404, 'temp', 20.67, 'unité', '2026-02-28 18:30:51', 28),
(19405, 'temp', 9.41, 'unité', '2026-02-28 18:30:52', 29),
(19406, 'temp', 39.66, 'unité', '2026-02-28 18:30:57', 28),
(19407, 'temp', 67.47, 'unité', '2026-02-28 18:30:57', 29),
(19408, 'temp', 60.49, 'unité', '2026-02-28 18:31:02', 28),
(19409, 'temp', 39.68, 'unité', '2026-02-28 18:31:02', 29),
(19410, 'temp', 47.26, 'unité', '2026-02-28 18:31:07', 28),
(19411, 'temp', 67.26, 'unité', '2026-02-28 18:31:07', 29),
(19412, 'temp', 62.37, 'unité', '2026-02-28 18:31:12', 28),
(19413, 'temp', 2.97, 'unité', '2026-02-28 18:31:12', 29),
(19414, 'temp', 90.27, 'unité', '2026-02-28 18:31:17', 28),
(19415, 'temp', 35.9, 'unité', '2026-02-28 18:31:17', 29),
(19416, 'temp', 57.76, 'unité', '2026-02-28 18:31:22', 28),
(19417, 'temp', 6.41, 'unité', '2026-02-28 18:31:22', 29),
(19418, 'temp', 41.89, 'unité', '2026-02-28 18:31:27', 28),
(19419, 'temp', 52.5, 'unité', '2026-02-28 18:31:27', 29),
(19420, 'temp', 35.11, 'unité', '2026-02-28 18:31:32', 28),
(19421, 'temp', 68.57, 'unité', '2026-02-28 18:31:32', 29),
(19422, 'temp', 27.32, 'unité', '2026-02-28 18:31:37', 28),
(19423, 'temp', 36.64, 'unité', '2026-02-28 18:31:37', 29),
(19424, 'temp', 80.95, 'unité', '2026-02-28 18:31:42', 28),
(19425, 'temp', 13.51, 'unité', '2026-02-28 18:31:42', 29),
(19426, 'temp', 30.09, 'unité', '2026-02-28 18:31:47', 28),
(19427, 'temp', 53.08, 'unité', '2026-02-28 18:31:47', 29),
(19428, 'temp', 38.28, 'unité', '2026-02-28 18:31:52', 28),
(19429, 'temp', 89.39, 'unité', '2026-02-28 18:31:52', 29),
(19430, 'temp', 19.95, 'unité', '2026-02-28 18:31:57', 28),
(19431, 'temp', 42.55, 'unité', '2026-02-28 18:31:57', 29),
(19432, 'temp', 25.4, 'unité', '2026-02-28 18:32:02', 28),
(19433, 'temp', 4.94, 'unité', '2026-02-28 18:32:03', 29),
(19434, 'temp', 67.21, 'unité', '2026-02-28 18:32:08', 28),
(19435, 'temp', 26.08, 'unité', '2026-02-28 18:32:08', 29),
(19436, 'temp', 42.94, 'unité', '2026-02-28 18:32:13', 28),
(19437, 'temp', 86.66, 'unité', '2026-02-28 18:32:13', 29),
(19438, 'temp', 84.72, 'unité', '2026-02-28 18:32:18', 28),
(19439, 'temp', 88.73, 'unité', '2026-02-28 18:32:18', 29),
(19440, 'temp', 87.71, 'unité', '2026-02-28 18:32:23', 28),
(19441, 'temp', 18.28, 'unité', '2026-02-28 18:32:23', 29),
(19442, 'temp', 4.24, 'unité', '2026-02-28 18:32:28', 28),
(19443, 'temp', 18.66, 'unité', '2026-02-28 18:32:28', 29),
(19444, 'temp', 0.24, 'unité', '2026-02-28 18:32:33', 28),
(19445, 'temp', 74.98, 'unité', '2026-02-28 18:32:33', 29),
(19446, 'temp', 43.36, 'unité', '2026-02-28 18:32:38', 28),
(19447, 'temp', 3.1, 'unité', '2026-02-28 18:32:38', 29),
(19448, 'temp', 34.48, 'unité', '2026-02-28 18:32:43', 28),
(19449, 'temp', 40.03, 'unité', '2026-02-28 18:32:43', 29),
(19450, 'temp', 35.49, 'unité', '2026-02-28 18:32:48', 28),
(19451, 'temp', 10.7, 'unité', '2026-02-28 18:32:48', 29),
(19452, 'temp', 5.2, 'unité', '2026-02-28 18:32:53', 28),
(19453, 'temp', 80.45, 'unité', '2026-02-28 18:32:53', 29),
(19454, 'temp', 83.93, 'unité', '2026-02-28 18:32:58', 28),
(19455, 'temp', 23.02, 'unité', '2026-02-28 18:32:58', 29),
(19456, 'temp', 9.66, 'unité', '2026-02-28 18:33:03', 28),
(19457, 'temp', 76.06, 'unité', '2026-02-28 18:33:03', 29),
(19458, 'temp', 19.67, 'unité', '2026-02-28 18:33:08', 28),
(19459, 'temp', 7.29, 'unité', '2026-02-28 18:33:09', 29),
(19460, 'temp', 74.28, 'unité', '2026-02-28 18:33:14', 28),
(19461, 'temp', 47.06, 'unité', '2026-02-28 18:33:14', 29),
(19462, 'temp', 9.32, 'unité', '2026-02-28 18:33:19', 28),
(19463, 'temp', 47.56, 'unité', '2026-02-28 18:33:19', 29),
(19464, 'temp', 23.56, 'unité', '2026-02-28 18:33:24', 28),
(19465, 'temp', 90.06, 'unité', '2026-02-28 18:33:24', 29),
(19466, 'temp', 44.07, 'unité', '2026-02-28 18:33:29', 28),
(19467, 'temp', 54.14, 'unité', '2026-02-28 18:33:29', 29),
(19468, 'temp', 25.45, 'unité', '2026-02-28 18:33:34', 28),
(19469, 'temp', 84.55, 'unité', '2026-02-28 18:33:34', 29),
(19470, 'temp', 58.57, 'unité', '2026-02-28 18:33:39', 28),
(19471, 'temp', 90.12, 'unité', '2026-02-28 18:33:39', 29),
(19472, 'temp', 38.37, 'unité', '2026-02-28 18:33:44', 28),
(19473, 'temp', 89.78, 'unité', '2026-02-28 18:33:44', 29),
(19474, 'temp', 70.43, 'unité', '2026-02-28 18:33:49', 28),
(19475, 'temp', 49.45, 'unité', '2026-02-28 18:33:49', 29),
(19476, 'temp', 61.26, 'unité', '2026-02-28 18:33:54', 28),
(19477, 'temp', 1.42, 'unité', '2026-02-28 18:33:54', 29),
(19478, 'temp', 55.39, 'unité', '2026-02-28 18:33:59', 28),
(19479, 'temp', 96.96, 'unité', '2026-02-28 18:33:59', 29),
(19480, 'temp', 86.49, 'unité', '2026-02-28 18:34:04', 28),
(19481, 'temp', 57.77, 'unité', '2026-02-28 18:34:04', 29),
(19482, 'temp', 71.08, 'unité', '2026-02-28 18:34:09', 28),
(19483, 'temp', 55.83, 'unité', '2026-02-28 18:34:09', 29),
(19484, 'temp', 30.72, 'unité', '2026-02-28 18:34:15', 28),
(19485, 'temp', 7.84, 'unité', '2026-02-28 18:34:15', 29),
(19486, 'temp', 9.81, 'unité', '2026-02-28 18:34:20', 28),
(19487, 'temp', 16.03, 'unité', '2026-02-28 18:34:20', 29),
(19488, 'temp', 30.51, 'unité', '2026-02-28 18:34:25', 28),
(19489, 'temp', 47.7, 'unité', '2026-02-28 18:34:25', 29),
(19490, 'temp', 32.74, 'unité', '2026-02-28 18:34:30', 28),
(19491, 'temp', 96.61, 'unité', '2026-02-28 18:34:30', 29),
(19492, 'temp', 35.12, 'unité', '2026-02-28 18:34:35', 28),
(19493, 'temp', 7.3, 'unité', '2026-02-28 18:34:35', 29),
(19494, 'temp', 93.45, 'unité', '2026-02-28 18:34:40', 28),
(19495, 'temp', 82.3, 'unité', '2026-02-28 18:34:40', 29),
(19496, 'temp', 21.17, 'unité', '2026-02-28 18:34:45', 28),
(19497, 'temp', 11.19, 'unité', '2026-02-28 18:34:45', 29),
(19498, 'temp', 40.02, 'unité', '2026-02-28 18:34:50', 28),
(19499, 'temp', 31.62, 'unité', '2026-02-28 18:34:50', 29),
(19500, 'temp', 98.69, 'unité', '2026-02-28 18:34:55', 28),
(19501, 'temp', 76.2, 'unité', '2026-02-28 18:34:55', 29),
(19502, 'temp', 23.24, 'unité', '2026-02-28 18:35:00', 28),
(19503, 'temp', 25.37, 'unité', '2026-02-28 18:35:00', 29),
(19504, 'temp', 54.31, 'unité', '2026-02-28 18:35:05', 28),
(19505, 'temp', 7.83, 'unité', '2026-02-28 18:35:05', 29),
(19506, 'temp', 83.77, 'unité', '2026-02-28 18:35:10', 28),
(19507, 'temp', 9.14, 'unité', '2026-02-28 18:35:10', 29),
(19508, 'temp', 27.01, 'unité', '2026-02-28 18:35:15', 28),
(19509, 'temp', 69.9, 'unité', '2026-02-28 18:35:15', 29),
(19510, 'temp', 65.45, 'unité', '2026-02-28 18:35:20', 28),
(19511, 'temp', 2, 'unité', '2026-02-28 18:35:20', 29),
(19512, 'temp', 40.66, 'unité', '2026-02-28 18:35:25', 28),
(19513, 'temp', 19.17, 'unité', '2026-02-28 18:35:25', 29),
(19514, 'temp', 79, 'unité', '2026-02-28 18:35:31', 28),
(19515, 'temp', 20.61, 'unité', '2026-02-28 18:35:31', 29),
(19516, 'temp', 0.94, 'unité', '2026-02-28 18:35:36', 28),
(19517, 'temp', 36.25, 'unité', '2026-02-28 18:35:36', 29),
(19518, 'temp', 59.43, 'unité', '2026-02-28 18:35:41', 28),
(19519, 'temp', 2.04, 'unité', '2026-02-28 18:35:41', 29),
(19520, 'temp', 47.51, 'unité', '2026-02-28 18:35:46', 28),
(19521, 'temp', 79.95, 'unité', '2026-02-28 18:35:46', 29),
(19522, 'temp', 76.83, 'unité', '2026-02-28 18:35:51', 28),
(19523, 'temp', 38.03, 'unité', '2026-02-28 18:35:51', 29),
(19524, 'temp', 60.23, 'unité', '2026-02-28 18:35:56', 28),
(19525, 'temp', 87.35, 'unité', '2026-02-28 18:35:56', 29),
(19526, 'temp', 10.59, 'unité', '2026-02-28 18:36:01', 28),
(19527, 'temp', 38.78, 'unité', '2026-02-28 18:36:01', 29),
(19528, 'temp', 16.12, 'unité', '2026-02-28 18:36:06', 28),
(19529, 'temp', 84.8, 'unité', '2026-02-28 18:36:06', 29),
(19530, 'temp', 29.69, 'unité', '2026-02-28 18:36:11', 28),
(19531, 'temp', 14.52, 'unité', '2026-02-28 18:36:11', 29),
(19532, 'temp', 13.39, 'unité', '2026-02-28 18:36:16', 28),
(19533, 'temp', 75.66, 'unité', '2026-02-28 18:36:16', 29),
(19534, 'temp', 30.72, 'unité', '2026-02-28 18:36:21', 28),
(19535, 'temp', 68.76, 'unité', '2026-02-28 18:36:21', 29),
(19536, 'temp', 40.82, 'unité', '2026-02-28 18:36:26', 28),
(19537, 'temp', 22.2, 'unité', '2026-02-28 18:36:26', 29),
(19538, 'temp', 31.95, 'unité', '2026-02-28 18:36:31', 28),
(19539, 'temp', 23.92, 'unité', '2026-02-28 18:36:31', 29),
(19540, 'temp', 27.49, 'unité', '2026-02-28 18:36:36', 28),
(19541, 'temp', 47.88, 'unité', '2026-02-28 18:36:36', 29),
(19542, 'temp', 29.34, 'unité', '2026-02-28 18:36:42', 28),
(19543, 'temp', 51.93, 'unité', '2026-02-28 18:36:42', 29),
(19544, 'temp', 16.92, 'unité', '2026-02-28 18:36:47', 28),
(19545, 'temp', 52.58, 'unité', '2026-02-28 18:36:47', 29),
(19546, 'temp', 29.68, 'unité', '2026-02-28 18:36:52', 28),
(19547, 'temp', 12.99, 'unité', '2026-02-28 18:36:52', 29),
(19548, 'temp', 3.55, 'unité', '2026-02-28 18:36:57', 28),
(19549, 'temp', 69.7, 'unité', '2026-02-28 18:36:57', 29),
(19550, 'temp', 69.02, 'unité', '2026-02-28 18:37:02', 28),
(19551, 'temp', 31.2, 'unité', '2026-02-28 18:37:02', 29),
(19552, 'temp', 88.25, 'unité', '2026-02-28 18:37:07', 28),
(19553, 'temp', 52.91, 'unité', '2026-02-28 18:37:07', 29),
(19554, 'temp', 28.33, 'unité', '2026-02-28 18:37:12', 28),
(19555, 'temp', 7.85, 'unité', '2026-02-28 18:37:12', 29),
(19556, 'temp', 44.09, 'unité', '2026-02-28 18:37:17', 28),
(19557, 'temp', 57.75, 'unité', '2026-02-28 18:37:17', 29),
(19558, 'temp', 8.39, 'unité', '2026-02-28 18:37:22', 28),
(19559, 'temp', 64.04, 'unité', '2026-02-28 18:37:22', 29),
(19560, 'temp', 54.85, 'unité', '2026-02-28 18:37:27', 28),
(19561, 'temp', 39.64, 'unité', '2026-02-28 18:37:27', 29),
(19562, 'temp', 27.56, 'unité', '2026-02-28 18:37:32', 28),
(19563, 'temp', 85.61, 'unité', '2026-02-28 18:37:32', 29),
(19564, 'temp', 78.61, 'unité', '2026-02-28 18:37:37', 28),
(19565, 'temp', 32.92, 'unité', '2026-02-28 18:37:37', 29),
(19566, 'temp', 39.49, 'unité', '2026-02-28 18:37:42', 28),
(19567, 'temp', 0.16, 'unité', '2026-02-28 18:37:42', 29),
(19568, 'temp', 46.42, 'unité', '2026-02-28 18:37:48', 28),
(19569, 'temp', 76.48, 'unité', '2026-02-28 18:37:48', 29),
(19570, 'temp', 48.31, 'unité', '2026-02-28 18:37:53', 28),
(19571, 'temp', 91.54, 'unité', '2026-02-28 18:37:53', 29),
(19572, 'temp', 77.53, 'unité', '2026-02-28 18:37:58', 28),
(19573, 'temp', 68.62, 'unité', '2026-02-28 18:37:59', 29),
(19574, 'temp', 33.95, 'unité', '2026-02-28 18:38:04', 28),
(19575, 'temp', 5.89, 'unité', '2026-02-28 18:38:04', 29),
(19576, 'temp', 23.94, 'unité', '2026-02-28 18:38:09', 28),
(19577, 'temp', 57.31, 'unité', '2026-02-28 18:38:09', 29),
(19578, 'temp', 16.89, 'unité', '2026-02-28 18:38:14', 28),
(19579, 'temp', 40.66, 'unité', '2026-02-28 18:38:15', 29),
(19580, 'temp', 44.01, 'unité', '2026-02-28 18:38:20', 28),
(19581, 'temp', 18.59, 'unité', '2026-02-28 18:38:20', 29),
(19582, 'temp', 39.91, 'unité', '2026-02-28 18:38:25', 28),
(19583, 'temp', 44.63, 'unité', '2026-02-28 18:38:25', 29),
(19584, 'temp', 78.44, 'unité', '2026-02-28 18:38:30', 28),
(19585, 'temp', 48.33, 'unité', '2026-02-28 18:38:30', 29),
(19586, 'temp', 85.79, 'unité', '2026-02-28 18:38:35', 28),
(19587, 'temp', 21.17, 'unité', '2026-02-28 18:38:35', 29),
(19588, 'temp', 38.42, 'unité', '2026-02-28 18:38:40', 28),
(19589, 'temp', 43.63, 'unité', '2026-02-28 18:38:40', 29),
(19590, 'temp', 13.24, 'unité', '2026-02-28 18:38:45', 28),
(19591, 'temp', 43.32, 'unité', '2026-02-28 18:38:45', 29),
(19592, 'temp', 3.6, 'unité', '2026-02-28 18:38:50', 28),
(19593, 'temp', 59.8, 'unité', '2026-02-28 18:38:50', 29),
(19594, 'temp', 96.81, 'unité', '2026-02-28 18:38:56', 28),
(19595, 'temp', 63.08, 'unité', '2026-02-28 18:38:56', 29),
(19596, 'temp', 36.48, 'unité', '2026-02-28 18:39:01', 28),
(19597, 'temp', 48.61, 'unité', '2026-02-28 18:39:01', 29),
(19598, 'temp', 85.18, 'unité', '2026-02-28 18:39:06', 28),
(19599, 'temp', 12.76, 'unité', '2026-02-28 18:39:06', 29),
(19600, 'temp', 71.25, 'unité', '2026-02-28 18:39:12', 28),
(19601, 'temp', 95.28, 'unité', '2026-02-28 18:39:12', 29),
(19602, 'temp', 10.04, 'unité', '2026-02-28 18:39:17', 28),
(19603, 'temp', 70.43, 'unité', '2026-02-28 18:39:17', 29),
(19604, 'temp', 2.54, 'unité', '2026-02-28 18:39:22', 28),
(19605, 'temp', 65.08, 'unité', '2026-02-28 18:39:22', 29),
(19606, 'temp', 92.35, 'unité', '2026-02-28 18:39:27', 28),
(19607, 'temp', 85.7, 'unité', '2026-02-28 18:39:27', 29),
(19608, 'temp', 83.08, 'unité', '2026-02-28 18:39:32', 28),
(19609, 'temp', 64.52, 'unité', '2026-02-28 18:39:32', 29),
(19610, 'temp', 41.49, 'unité', '2026-02-28 18:39:38', 28),
(19611, 'temp', 22.08, 'unité', '2026-02-28 18:39:38', 29),
(19612, 'temp', 0.21, 'unité', '2026-02-28 18:39:43', 28),
(19613, 'temp', 90.69, 'unité', '2026-02-28 18:39:43', 29),
(19614, 'temp', 95.01, 'unité', '2026-02-28 18:39:48', 28),
(19615, 'temp', 22.62, 'unité', '2026-02-28 18:39:48', 29),
(19616, 'temp', 39.28, 'unité', '2026-02-28 18:39:53', 28),
(19617, 'temp', 41.31, 'unité', '2026-02-28 18:39:53', 29),
(19618, 'temp', 28.63, 'unité', '2026-02-28 18:39:58', 28),
(19619, 'temp', 96.85, 'unité', '2026-02-28 18:39:58', 29),
(19620, 'temp', 16.49, 'unité', '2026-02-28 18:40:03', 28),
(19621, 'temp', 96.18, 'unité', '2026-02-28 18:40:03', 29),
(19622, 'temp', 42.34, 'unité', '2026-02-28 18:40:08', 28),
(19623, 'temp', 60.36, 'unité', '2026-02-28 18:40:08', 29),
(19624, 'temp', 68.3, 'unité', '2026-02-28 18:40:13', 28),
(19625, 'temp', 69.95, 'unité', '2026-02-28 18:40:13', 29),
(19626, 'temp', 7.57, 'unité', '2026-02-28 18:40:19', 28),
(19627, 'temp', 66.4, 'unité', '2026-02-28 18:40:19', 29),
(19628, 'temp', 67.81, 'unité', '2026-02-28 18:40:24', 28),
(19629, 'temp', 2.04, 'unité', '2026-02-28 18:40:24', 29),
(19630, 'temp', 20.74, 'unité', '2026-02-28 18:40:29', 28),
(19631, 'temp', 72.83, 'unité', '2026-02-28 18:40:29', 29),
(19632, 'temp', 88.97, 'unité', '2026-02-28 18:40:34', 28),
(19633, 'temp', 96.37, 'unité', '2026-02-28 18:40:34', 29),
(19634, 'temp', 39.54, 'unité', '2026-02-28 18:40:39', 28),
(19635, 'temp', 38.29, 'unité', '2026-02-28 18:40:39', 29),
(19636, 'temp', 43.96, 'unité', '2026-02-28 18:40:44', 28),
(19637, 'temp', 53.28, 'unité', '2026-02-28 18:40:44', 29),
(19638, 'temp', 48.71, 'unité', '2026-02-28 18:40:49', 28),
(19639, 'temp', 33.78, 'unité', '2026-02-28 18:40:49', 29),
(19640, 'temp', 17.99, 'unité', '2026-02-28 18:40:54', 28),
(19641, 'temp', 45.25, 'unité', '2026-02-28 18:40:54', 29),
(19642, 'temp', 3.02, 'unité', '2026-02-28 18:40:59', 28),
(19643, 'temp', 50.37, 'unité', '2026-02-28 18:41:00', 29),
(19644, 'temp', 40.47, 'unité', '2026-02-28 18:44:53', 28),
(19645, 'temp', 83.64, 'unité', '2026-02-28 18:44:54', 29),
(19646, 'temp', 89.4, 'unité', '2026-02-28 18:44:59', 28),
(19647, 'temp', 2.56, 'unité', '2026-02-28 18:44:59', 29),
(19648, 'temp', 41.91, 'unité', '2026-02-28 18:45:04', 28),
(19649, 'temp', 65.25, 'unité', '2026-02-28 18:45:04', 29),
(19650, 'temp', 26.3, 'unité', '2026-02-28 18:45:09', 28),
(19651, 'temp', 84.6, 'unité', '2026-02-28 18:45:09', 29),
(19652, 'temp', 44.23, 'unité', '2026-02-28 18:45:14', 28),
(19653, 'temp', 79.15, 'unité', '2026-02-28 18:45:14', 29),
(19654, 'temp', 80.77, 'unité', '2026-02-28 18:45:19', 28),
(19655, 'temp', 50.31, 'unité', '2026-02-28 18:45:19', 29),
(19656, 'temp', 26.95, 'unité', '2026-02-28 18:45:24', 28),
(19657, 'temp', 35.81, 'unité', '2026-02-28 18:45:24', 29),
(19658, 'temp', 76.87, 'unité', '2026-02-28 18:45:29', 28),
(19659, 'temp', 22.46, 'unité', '2026-02-28 18:45:29', 29),
(19660, 'temp', 40.47, 'unité', '2026-02-28 18:45:34', 28),
(19661, 'temp', 91.23, 'unité', '2026-02-28 18:45:34', 29),
(19662, 'temp', 55.63, 'unité', '2026-02-28 20:23:03', 28),
(19663, 'temp', 17.88, 'unité', '2026-02-28 20:23:03', 29),
(19664, 'temp', 55.33, 'unité', '2026-02-28 20:23:09', 28),
(19665, 'temp', 38.88, 'unité', '2026-02-28 20:23:09', 29),
(19666, 'temp', 65.65, 'unité', '2026-02-28 20:23:34', 28),
(19667, 'temp', 29.03, 'unité', '2026-02-28 20:23:35', 29),
(19668, 'temp', 60.17, 'unité', '2026-02-28 20:24:06', 28),
(19669, 'temp', 85.3, 'unité', '2026-02-28 20:24:06', 29),
(19670, 'temp', 57.93, 'unité', '2026-02-28 20:24:12', 28),
(19671, 'temp', 29.64, 'unité', '2026-02-28 20:24:12', 29),
(19672, 'temp', 58.01, 'unité', '2026-02-28 20:24:18', 28),
(19673, 'temp', 57.13, 'unité', '2026-02-28 20:24:18', 29),
(19674, 'temp', 49.32, 'unité', '2026-02-28 20:24:23', 28),
(19675, 'temp', 35.14, 'unité', '2026-02-28 20:24:23', 29),
(19676, 'temp', 97.12, 'unité', '2026-02-28 20:24:28', 28),
(19677, 'temp', 24.33, 'unité', '2026-02-28 20:24:28', 29),
(19678, 'temp', 87.08, 'unité', '2026-02-28 20:24:33', 28),
(19679, 'temp', 32.09, 'unité', '2026-02-28 20:24:33', 29),
(19680, 'temp', 98.5, 'unité', '2026-02-28 20:24:39', 28),
(19681, 'temp', 36.21, 'unité', '2026-02-28 20:24:39', 29),
(19682, 'temp', 81.49, 'unité', '2026-02-28 20:24:44', 28),
(19683, 'temp', 68.19, 'unité', '2026-02-28 20:24:44', 29),
(19684, 'temp', 12.09, 'unité', '2026-02-28 20:24:49', 28),
(19685, 'temp', 76.18, 'unité', '2026-02-28 20:24:49', 29),
(19686, 'temp', 45.8, 'unité', '2026-02-28 20:24:54', 28),
(19687, 'temp', 71.7, 'unité', '2026-02-28 20:24:54', 29),
(19688, 'temp', 78.22, 'unité', '2026-02-28 20:24:59', 28),
(19689, 'temp', 66.12, 'unité', '2026-02-28 20:24:59', 29),
(19690, 'temp', 81.26, 'unité', '2026-02-28 20:25:04', 28),
(19691, 'temp', 18.15, 'unité', '2026-02-28 20:25:04', 29),
(19692, 'temp', 74.98, 'unité', '2026-02-28 20:25:09', 28),
(19693, 'temp', 80.6, 'unité', '2026-02-28 20:25:09', 29),
(19694, 'temp', 15.02, 'unité', '2026-02-28 20:25:14', 28),
(19695, 'temp', 98.81, 'unité', '2026-02-28 20:25:14', 29),
(19696, 'temp', 47.13, 'unité', '2026-02-28 20:25:19', 28),
(19697, 'temp', 43.06, 'unité', '2026-02-28 20:25:19', 29),
(19698, 'temp', 30.48, 'unité', '2026-02-28 20:25:24', 28),
(19699, 'temp', 84.46, 'unité', '2026-02-28 20:25:24', 29),
(19700, 'temp', 85.02, 'unité', '2026-02-28 20:25:29', 28),
(19701, 'temp', 96.91, 'unité', '2026-02-28 20:25:29', 29),
(19702, 'temp', 99.94, 'unité', '2026-02-28 20:25:35', 28),
(19703, 'temp', 56.84, 'unité', '2026-02-28 20:25:35', 29),
(19704, 'temp', 7.77, 'unité', '2026-02-28 20:25:40', 28),
(19705, 'temp', 54.81, 'unité', '2026-02-28 20:25:40', 29),
(19706, 'temp', 62.26, 'unité', '2026-02-28 20:25:45', 28),
(19707, 'temp', 94.67, 'unité', '2026-02-28 20:25:45', 29),
(19708, 'temp', 93.9, 'unité', '2026-02-28 20:25:50', 28),
(19709, 'temp', 7.69, 'unité', '2026-02-28 20:25:50', 29),
(19710, 'temp', 97.16, 'unité', '2026-02-28 20:25:55', 28),
(19711, 'temp', 32.83, 'unité', '2026-02-28 20:25:55', 29),
(19712, 'temp', 20.62, 'unité', '2026-02-28 20:26:00', 28),
(19713, 'temp', 21.7, 'unité', '2026-02-28 20:26:00', 29),
(19714, 'temp', 52.93, 'unité', '2026-02-28 20:26:05', 28),
(19715, 'temp', 11.26, 'unité', '2026-02-28 20:26:05', 29),
(19716, 'temp', 12.36, 'unité', '2026-02-28 20:26:10', 28),
(19717, 'temp', 26.14, 'unité', '2026-02-28 20:26:10', 29),
(19718, 'temp', 25, 'unité', '2026-02-28 20:26:15', 28),
(19719, 'temp', 75.64, 'unité', '2026-02-28 20:26:15', 29),
(19720, 'temp', 79.49, 'unité', '2026-02-28 20:26:20', 28),
(19721, 'temp', 36.25, 'unité', '2026-02-28 20:26:20', 29),
(19722, 'temp', 2.99, 'unité', '2026-02-28 20:26:25', 28),
(19723, 'temp', 23.68, 'unité', '2026-02-28 20:26:25', 29),
(19724, 'temp', 88.45, 'unité', '2026-02-28 20:26:30', 28),
(19725, 'temp', 7.41, 'unité', '2026-02-28 20:26:30', 29),
(19726, 'temp', 77.07, 'unité', '2026-02-28 20:26:36', 28),
(19727, 'temp', 48.56, 'unité', '2026-02-28 20:26:36', 29),
(19728, 'temp', 79.47, 'unité', '2026-02-28 20:26:41', 28),
(19729, 'temp', 78.75, 'unité', '2026-02-28 20:26:41', 29),
(19730, 'temp', 84.07, 'unité', '2026-02-28 20:26:46', 28),
(19731, 'temp', 82.68, 'unité', '2026-02-28 20:26:46', 29),
(19732, 'temp', 94.7, 'unité', '2026-02-28 20:26:51', 28),
(19733, 'temp', 74.28, 'unité', '2026-02-28 20:26:51', 29),
(19734, 'temp', 12.23, 'unité', '2026-02-28 20:26:56', 28),
(19735, 'temp', 35.38, 'unité', '2026-02-28 20:26:56', 29),
(19736, 'temp', 65.65, 'unité', '2026-02-28 20:27:01', 28),
(19737, 'temp', 71.29, 'unité', '2026-02-28 20:27:01', 29),
(19738, 'temp', 61.78, 'unité', '2026-02-28 20:27:06', 28),
(19739, 'temp', 43.67, 'unité', '2026-02-28 20:27:06', 29),
(19740, 'temp', 24.08, 'unité', '2026-02-28 20:27:11', 28),
(19741, 'temp', 49.5, 'unité', '2026-02-28 20:27:11', 29),
(19742, 'temp', 40.97, 'unité', '2026-02-28 20:27:16', 28),
(19743, 'temp', 35.87, 'unité', '2026-02-28 20:27:16', 29),
(19744, 'temp', 29.12, 'unité', '2026-02-28 20:27:21', 28),
(19745, 'temp', 39.77, 'unité', '2026-02-28 20:27:21', 29),
(19746, 'temp', 77.44, 'unité', '2026-02-28 20:27:26', 28),
(19747, 'temp', 74.51, 'unité', '2026-02-28 20:27:26', 29),
(19748, 'temp', 58.17, 'unité', '2026-02-28 20:27:31', 28),
(19749, 'temp', 28.21, 'unité', '2026-02-28 20:27:31', 29),
(19750, 'temp', 3.7, 'unité', '2026-02-28 20:27:36', 28),
(19751, 'temp', 28.37, 'unité', '2026-02-28 20:27:36', 29),
(19752, 'temp', 22.13, 'unité', '2026-02-28 20:27:41', 28),
(19753, 'temp', 4.76, 'unité', '2026-02-28 20:27:41', 29),
(19754, 'temp', 25.98, 'unité', '2026-02-28 20:27:46', 28),
(19755, 'temp', 0.64, 'unité', '2026-02-28 20:27:47', 29),
(19756, 'temp', 18.78, 'unité', '2026-02-28 20:27:52', 28),
(19757, 'temp', 54.88, 'unité', '2026-02-28 20:27:52', 29),
(19758, 'temp', 18.96, 'unité', '2026-02-28 20:27:57', 28),
(19759, 'temp', 26.63, 'unité', '2026-02-28 20:27:57', 29),
(19760, 'temp', 52.57, 'unité', '2026-02-28 20:28:02', 28),
(19761, 'temp', 27.67, 'unité', '2026-02-28 20:28:02', 29),
(19762, 'temp', 3.46, 'unité', '2026-02-28 20:28:07', 28),
(19763, 'temp', 19.77, 'unité', '2026-02-28 20:28:07', 29),
(19764, 'temp', 95.23, 'unité', '2026-02-28 20:28:12', 28),
(19765, 'temp', 24.98, 'unité', '2026-02-28 20:28:12', 29),
(19766, 'temp', 96.64, 'unité', '2026-02-28 20:28:17', 28),
(19767, 'temp', 58.17, 'unité', '2026-02-28 20:28:17', 29),
(19768, 'temp', 83.38, 'unité', '2026-02-28 20:28:22', 28),
(19769, 'temp', 87.83, 'unité', '2026-02-28 20:28:22', 29),
(19770, 'temp', 18.49, 'unité', '2026-02-28 20:28:27', 28),
(19771, 'temp', 89.44, 'unité', '2026-02-28 20:28:27', 29),
(19772, 'temp', 41.74, 'unité', '2026-02-28 20:28:32', 28),
(19773, 'temp', 96.47, 'unité', '2026-02-28 20:28:32', 29),
(19774, 'temp', 27.77, 'unité', '2026-02-28 20:28:37', 28),
(19775, 'temp', 52.01, 'unité', '2026-02-28 20:28:37', 29),
(19776, 'temp', 17.58, 'unité', '2026-02-28 20:28:42', 28),
(19777, 'temp', 52.08, 'unité', '2026-02-28 20:28:42', 29),
(19778, 'temp', 21.65, 'unité', '2026-02-28 20:28:47', 28),
(19779, 'temp', 25.02, 'unité', '2026-02-28 20:28:47', 29),
(19780, 'temp', 82, 'unité', '2026-02-28 20:28:52', 28),
(19781, 'temp', 75.63, 'unité', '2026-02-28 20:28:52', 29),
(19782, 'temp', 90.17, 'unité', '2026-02-28 20:28:57', 28),
(19783, 'temp', 46.09, 'unité', '2026-02-28 20:28:57', 29),
(19784, 'temp', 79.24, 'unité', '2026-02-28 20:29:02', 28),
(19785, 'temp', 55.61, 'unité', '2026-02-28 20:29:02', 29),
(19786, 'temp', 65.47, 'unité', '2026-02-28 20:29:07', 28),
(19787, 'temp', 9.67, 'unité', '2026-02-28 20:29:07', 29),
(19788, 'temp', 55.11, 'unité', '2026-02-28 20:29:13', 28),
(19789, 'temp', 77.85, 'unité', '2026-02-28 20:29:13', 29),
(19790, 'temp', 83.13, 'unité', '2026-02-28 20:29:18', 28),
(19791, 'temp', 89.29, 'unité', '2026-02-28 20:29:18', 29),
(19792, 'temp', 30.76, 'unité', '2026-02-28 20:29:23', 28),
(19793, 'temp', 57.48, 'unité', '2026-02-28 20:29:23', 29),
(19794, 'temp', 35.25, 'unité', '2026-02-28 20:29:28', 28),
(19795, 'temp', 77.52, 'unité', '2026-02-28 20:29:28', 29),
(19796, 'temp', 5.29, 'unité', '2026-02-28 20:29:33', 28),
(19797, 'temp', 67.66, 'unité', '2026-02-28 20:29:33', 29),
(19798, 'temp', 9.37, 'unité', '2026-02-28 20:29:38', 28),
(19799, 'temp', 38.22, 'unité', '2026-02-28 20:29:38', 29),
(19800, 'temp', 13.25, 'unité', '2026-02-28 20:29:43', 28),
(19801, 'temp', 22.49, 'unité', '2026-02-28 20:29:43', 29),
(19802, 'temp', 12.9, 'unité', '2026-02-28 20:29:48', 28),
(19803, 'temp', 77.81, 'unité', '2026-02-28 20:29:48', 29),
(19804, 'temp', 95.43, 'unité', '2026-02-28 20:29:53', 28),
(19805, 'temp', 66.2, 'unité', '2026-02-28 20:29:53', 29),
(19806, 'temp', 15.56, 'unité', '2026-02-28 20:29:58', 28),
(19807, 'temp', 41.9, 'unité', '2026-02-28 20:29:58', 29),
(19808, 'temp', 79.65, 'unité', '2026-02-28 20:30:03', 28);
INSERT INTO `releve_terrain` (`id_releve`, `type_mesure`, `valeur_mesuree`, `unite`, `date_heure`, `id_capteur`) VALUES
(19809, 'temp', 11.45, 'unité', '2026-02-28 20:30:03', 29),
(19810, 'temp', 86.85, 'unité', '2026-02-28 20:30:08', 28),
(19811, 'temp', 83.15, 'unité', '2026-02-28 20:30:08', 29),
(19812, 'temp', 95.22, 'unité', '2026-02-28 20:30:13', 28),
(19813, 'temp', 43.62, 'unité', '2026-02-28 20:30:13', 29),
(19814, 'temp', 48.34, 'unité', '2026-02-28 20:30:18', 28),
(19815, 'temp', 62.18, 'unité', '2026-02-28 20:30:18', 29),
(19816, 'temp', 40.59, 'unité', '2026-02-28 20:30:23', 28),
(19817, 'temp', 89.8, 'unité', '2026-02-28 20:30:23', 29),
(19818, 'temp', 3.52, 'unité', '2026-02-28 20:30:29', 28),
(19819, 'temp', 50.5, 'unité', '2026-02-28 20:30:29', 29),
(19820, 'temp', 9.13, 'unité', '2026-02-28 20:30:34', 28),
(19821, 'temp', 34.49, 'unité', '2026-02-28 20:30:34', 29),
(19822, 'temp', 71.76, 'unité', '2026-02-28 20:30:39', 28),
(19823, 'temp', 32.71, 'unité', '2026-02-28 20:30:39', 29),
(19824, 'temp', 75.95, 'unité', '2026-02-28 20:30:44', 28),
(19825, 'temp', 32.15, 'unité', '2026-02-28 20:30:44', 29),
(19826, 'temp', 16.23, 'unité', '2026-02-28 20:30:49', 28),
(19827, 'temp', 32.75, 'unité', '2026-02-28 20:30:49', 29),
(19828, 'temp', 48.53, 'unité', '2026-02-28 20:30:54', 28),
(19829, 'temp', 34.9, 'unité', '2026-02-28 20:30:54', 29),
(19830, 'temp', 60.84, 'unité', '2026-02-28 20:30:59', 28),
(19831, 'temp', 44.28, 'unité', '2026-02-28 20:30:59', 29),
(19832, 'temp', 99.52, 'unité', '2026-02-28 20:31:04', 28),
(19833, 'temp', 88.26, 'unité', '2026-02-28 20:31:04', 29),
(19834, 'temp', 92.88, 'unité', '2026-02-28 20:31:09', 28),
(19835, 'temp', 48.01, 'unité', '2026-02-28 20:31:09', 29),
(19836, 'temp', 26.72, 'unité', '2026-02-28 20:31:14', 28),
(19837, 'temp', 26.08, 'unité', '2026-02-28 20:31:14', 29),
(19838, 'temp', 41.37, 'unité', '2026-02-28 20:31:19', 28),
(19839, 'temp', 91.73, 'unité', '2026-02-28 20:31:19', 29),
(19840, 'temp', 55.74, 'unité', '2026-02-28 20:31:25', 28),
(19841, 'temp', 49.45, 'unité', '2026-02-28 20:31:25', 29),
(19842, 'temp', 38.3, 'unité', '2026-02-28 20:31:30', 28),
(19843, 'temp', 76.59, 'unité', '2026-02-28 20:31:30', 29),
(19844, 'temp', 33.8, 'unité', '2026-02-28 20:31:35', 28),
(19845, 'temp', 13.14, 'unité', '2026-02-28 20:31:35', 29),
(19846, 'temp', 77.44, 'unité', '2026-02-28 20:31:40', 28),
(19847, 'temp', 93.68, 'unité', '2026-02-28 20:31:40', 29),
(19848, 'temp', 30.16, 'unité', '2026-02-28 20:31:45', 28),
(19849, 'temp', 91.6, 'unité', '2026-02-28 20:31:45', 29),
(19850, 'temp', 51.07, 'unité', '2026-02-28 20:31:50', 28),
(19851, 'temp', 83.91, 'unité', '2026-02-28 20:31:50', 29),
(19852, 'temp', 61.65, 'unité', '2026-02-28 20:31:55', 28),
(19853, 'temp', 87.64, 'unité', '2026-02-28 20:31:55', 29),
(19854, 'temp', 98.02, 'unité', '2026-02-28 20:32:00', 28),
(19855, 'temp', 73.96, 'unité', '2026-02-28 20:32:00', 29),
(19856, 'temp', 75.42, 'unité', '2026-02-28 20:32:05', 28),
(19857, 'temp', 39.68, 'unité', '2026-02-28 20:32:05', 29),
(19858, 'temp', 88.74, 'unité', '2026-02-28 20:32:10', 28),
(19859, 'temp', 50.52, 'unité', '2026-02-28 20:32:10', 29),
(19860, 'temp', 79.89, 'unité', '2026-02-28 20:32:15', 28),
(19861, 'temp', 9.93, 'unité', '2026-02-28 20:32:15', 29),
(19862, 'temp', 59.23, 'unité', '2026-02-28 20:32:20', 28),
(19863, 'temp', 11.3, 'unité', '2026-02-28 20:32:20', 29),
(19864, 'temp', 58.04, 'unité', '2026-02-28 20:32:25', 28),
(19865, 'temp', 34.2, 'unité', '2026-02-28 20:32:25', 29),
(19866, 'temp', 40.71, 'unité', '2026-02-28 20:32:30', 28),
(19867, 'temp', 66.03, 'unité', '2026-02-28 20:32:30', 29),
(19868, 'temp', 2.66, 'unité', '2026-02-28 20:32:35', 28),
(19869, 'temp', 54.08, 'unité', '2026-02-28 20:32:35', 29),
(19870, 'temp', 73.96, 'unité', '2026-02-28 20:32:40', 28),
(19871, 'temp', 86.02, 'unité', '2026-02-28 20:32:41', 29),
(19872, 'temp', 85.72, 'unité', '2026-02-28 20:32:46', 28),
(19873, 'temp', 29.14, 'unité', '2026-02-28 20:32:46', 29),
(19874, 'temp', 54.75, 'unité', '2026-02-28 20:32:51', 28),
(19875, 'temp', 94.78, 'unité', '2026-02-28 20:32:51', 29),
(19876, 'temp', 93.57, 'unité', '2026-02-28 20:32:56', 28),
(19877, 'temp', 40.71, 'unité', '2026-02-28 20:32:56', 29),
(19878, 'temp', 72.93, 'unité', '2026-02-28 20:33:01', 28),
(19879, 'temp', 12.97, 'unité', '2026-02-28 20:33:01', 29),
(19880, 'temp', 58.27, 'unité', '2026-02-28 20:33:06', 28),
(19881, 'temp', 52.93, 'unité', '2026-02-28 20:33:06', 29),
(19882, 'temp', 84.47, 'unité', '2026-02-28 20:33:11', 28),
(19883, 'temp', 25.98, 'unité', '2026-02-28 20:33:11', 29),
(19884, 'temp', 26.66, 'unité', '2026-02-28 20:33:16', 28),
(19885, 'temp', 15.09, 'unité', '2026-02-28 20:33:16', 29),
(19886, 'temp', 46.9, 'unité', '2026-02-28 20:33:21', 28),
(19887, 'temp', 92.33, 'unité', '2026-02-28 20:33:21', 29),
(19888, 'temp', 86.41, 'unité', '2026-02-28 20:33:26', 28),
(19889, 'temp', 51.96, 'unité', '2026-02-28 20:33:26', 29),
(19890, 'temp', 96.13, 'unité', '2026-02-28 20:33:31', 28),
(19891, 'temp', 8.92, 'unité', '2026-02-28 20:33:31', 29),
(19892, 'temp', 90.39, 'unité', '2026-02-28 20:33:36', 28),
(19893, 'temp', 61.91, 'unité', '2026-02-28 20:33:36', 29),
(19894, 'temp', 67.23, 'unité', '2026-02-28 20:33:41', 28),
(19895, 'temp', 44.49, 'unité', '2026-02-28 20:33:41', 29),
(19896, 'temp', 59.58, 'unité', '2026-02-28 20:33:47', 28),
(19897, 'temp', 11.29, 'unité', '2026-02-28 20:33:47', 29),
(19898, 'temp', 63.92, 'unité', '2026-02-28 20:33:52', 28),
(19899, 'temp', 62.03, 'unité', '2026-02-28 20:33:52', 29),
(19900, 'temp', 50.74, 'unité', '2026-02-28 20:33:57', 28),
(19901, 'temp', 34.07, 'unité', '2026-02-28 20:33:57', 29),
(19902, 'temp', 96.13, 'unité', '2026-02-28 20:34:02', 28),
(19903, 'temp', 94.82, 'unité', '2026-02-28 20:34:02', 29),
(19904, 'temp', 1.86, 'unité', '2026-02-28 20:34:08', 28),
(19905, 'temp', 44.56, 'unité', '2026-02-28 20:34:08', 29),
(19906, 'temp', 80.17, 'unité', '2026-02-28 20:34:13', 28),
(19907, 'temp', 44.47, 'unité', '2026-02-28 20:34:13', 29),
(19908, 'temp', 5.96, 'unité', '2026-02-28 20:34:18', 28),
(19909, 'temp', 11.41, 'unité', '2026-02-28 20:34:18', 29),
(19910, 'temp', 77.02, 'unité', '2026-02-28 20:34:23', 28),
(19911, 'temp', 64.88, 'unité', '2026-02-28 20:34:23', 29),
(19912, 'temp', 57.1, 'unité', '2026-02-28 20:34:28', 28),
(19913, 'temp', 97.02, 'unité', '2026-02-28 20:34:28', 29),
(19914, 'temp', 0.91, 'unité', '2026-02-28 20:34:33', 28),
(19915, 'temp', 14.36, 'unité', '2026-02-28 20:34:33', 29),
(19916, 'temp', 53.98, 'unité', '2026-02-28 20:34:38', 28),
(19917, 'temp', 47.74, 'unité', '2026-02-28 20:34:38', 29),
(19918, 'temp', 52.89, 'unité', '2026-02-28 20:34:43', 28),
(19919, 'temp', 76.39, 'unité', '2026-02-28 20:34:43', 29),
(19920, 'temp', 94.71, 'unité', '2026-02-28 20:34:48', 28),
(19921, 'temp', 99.59, 'unité', '2026-02-28 20:34:48', 29),
(19922, 'temp', 45.77, 'unité', '2026-02-28 20:34:53', 28),
(19923, 'temp', 86.8, 'unité', '2026-02-28 20:34:53', 29),
(19924, 'temp', 51.32, 'unité', '2026-02-28 20:34:58', 28),
(19925, 'temp', 74.42, 'unité', '2026-02-28 20:34:58', 29),
(19926, 'temp', 27.34, 'unité', '2026-02-28 20:35:03', 28),
(19927, 'temp', 61.84, 'unité', '2026-02-28 20:35:03', 29),
(19928, 'temp', 5.74, 'unité', '2026-02-28 20:35:08', 28),
(19929, 'temp', 5.79, 'unité', '2026-02-28 20:35:08', 29),
(19930, 'temp', 31.47, 'unité', '2026-02-28 20:35:13', 28),
(19931, 'temp', 83.88, 'unité', '2026-02-28 20:35:13', 29),
(19932, 'temp', 55.4, 'unité', '2026-02-28 20:35:18', 28),
(19933, 'temp', 92.16, 'unité', '2026-02-28 20:35:18', 29),
(19934, 'temp', 72.43, 'unité', '2026-02-28 20:35:23', 28),
(19935, 'temp', 39.71, 'unité', '2026-02-28 20:35:23', 29),
(19936, 'temp', 83.13, 'unité', '2026-02-28 20:35:28', 28),
(19937, 'temp', 28.39, 'unité', '2026-02-28 20:35:28', 29),
(19938, 'temp', 9.09, 'unité', '2026-02-28 20:35:34', 28),
(19939, 'temp', 23, 'unité', '2026-02-28 20:35:34', 29),
(19940, 'temp', 45.95, 'unité', '2026-02-28 20:35:39', 28),
(19941, 'temp', 91.37, 'unité', '2026-02-28 20:35:39', 29),
(19942, 'temp', 83.99, 'unité', '2026-02-28 20:35:44', 28),
(19943, 'temp', 63.68, 'unité', '2026-02-28 20:35:44', 29),
(19944, 'temp', 10, 'unité', '2026-02-28 20:35:49', 28),
(19945, 'temp', 5.62, 'unité', '2026-02-28 20:35:49', 29),
(19946, 'temp', 27.35, 'unité', '2026-02-28 20:35:54', 28),
(19947, 'temp', 93.39, 'unité', '2026-02-28 20:35:54', 29),
(19948, 'temp', 12.37, 'unité', '2026-02-28 20:35:59', 28),
(19949, 'temp', 57.73, 'unité', '2026-02-28 20:35:59', 29),
(19950, 'temp', 3.42, 'unité', '2026-02-28 20:36:04', 28),
(19951, 'temp', 45.08, 'unité', '2026-02-28 20:36:04', 29),
(19952, 'temp', 0.33, 'unité', '2026-02-28 20:36:09', 28),
(19953, 'temp', 60.28, 'unité', '2026-02-28 20:36:09', 29),
(19954, 'temp', 95.2, 'unité', '2026-02-28 20:36:14', 28),
(19955, 'temp', 83.34, 'unité', '2026-02-28 20:36:14', 29),
(19956, 'temp', 81.43, 'unité', '2026-02-28 20:36:19', 28),
(19957, 'temp', 63.39, 'unité', '2026-02-28 20:36:19', 29),
(19958, 'temp', 93.21, 'unité', '2026-02-28 20:36:24', 28),
(19959, 'temp', 96.34, 'unité', '2026-02-28 20:36:24', 29),
(19960, 'temp', 63.74, 'unité', '2026-02-28 20:36:29', 28),
(19961, 'temp', 37.52, 'unité', '2026-02-28 20:36:29', 29),
(19962, 'temp', 40.29, 'unité', '2026-02-28 20:36:34', 28),
(19963, 'temp', 77.43, 'unité', '2026-02-28 20:36:34', 29),
(19964, 'temp', 97.95, 'unité', '2026-02-28 20:36:39', 28),
(19965, 'temp', 48.18, 'unité', '2026-02-28 20:36:39', 29),
(19966, 'temp', 99.31, 'unité', '2026-02-28 20:36:44', 28),
(19967, 'temp', 96.47, 'unité', '2026-02-28 20:36:44', 29),
(19968, 'temp', 45.05, 'unité', '2026-02-28 20:36:49', 28),
(19969, 'temp', 93.87, 'unité', '2026-02-28 20:36:49', 29),
(19970, 'temp', 27.68, 'unité', '2026-02-28 20:36:54', 28),
(19971, 'temp', 70.84, 'unité', '2026-02-28 20:36:54', 29),
(19972, 'temp', 5.59, 'unité', '2026-02-28 20:37:00', 28),
(19973, 'temp', 75.49, 'unité', '2026-02-28 20:37:00', 29),
(19974, 'temp', 35.39, 'unité', '2026-02-28 20:37:05', 28),
(19975, 'temp', 34.54, 'unité', '2026-02-28 20:37:05', 29),
(19976, 'temp', 10.85, 'unité', '2026-02-28 20:37:10', 28),
(19977, 'temp', 40.97, 'unité', '2026-02-28 20:37:10', 29),
(19978, 'temp', 72.59, 'unité', '2026-02-28 20:37:15', 28),
(19979, 'temp', 76.11, 'unité', '2026-02-28 20:37:15', 29),
(19980, 'temp', 97.77, 'unité', '2026-02-28 20:37:20', 28),
(19981, 'temp', 80.58, 'unité', '2026-02-28 20:37:20', 29),
(19982, 'temp', 52.71, 'unité', '2026-02-28 20:37:25', 28),
(19983, 'temp', 36.78, 'unité', '2026-02-28 20:37:25', 29),
(19984, 'temp', 17.45, 'unité', '2026-02-28 20:37:30', 28),
(19985, 'temp', 70.54, 'unité', '2026-02-28 20:37:30', 29),
(19986, 'temp', 56.09, 'unité', '2026-02-28 20:37:35', 28),
(19987, 'temp', 28.53, 'unité', '2026-02-28 20:37:35', 29),
(19988, 'temp', 50.72, 'unité', '2026-02-28 20:37:40', 28),
(19989, 'temp', 35.82, 'unité', '2026-02-28 20:37:40', 29),
(19990, 'temp', 25.45, 'unité', '2026-02-28 20:37:45', 28),
(19991, 'temp', 28.68, 'unité', '2026-02-28 20:37:45', 29),
(19992, 'temp', 23.49, 'unité', '2026-02-28 20:37:50', 28),
(19993, 'temp', 88.58, 'unité', '2026-02-28 20:37:50', 29),
(19994, 'temp', 69.69, 'unité', '2026-02-28 20:37:55', 28),
(19995, 'temp', 17.18, 'unité', '2026-02-28 20:37:55', 29),
(19996, 'temp', 33.07, 'unité', '2026-02-28 20:38:01', 28),
(19997, 'temp', 7.66, 'unité', '2026-02-28 20:38:01', 29),
(19998, 'temp', 48.54, 'unité', '2026-02-28 20:38:06', 28),
(19999, 'temp', 57.74, 'unité', '2026-02-28 20:38:06', 29),
(20000, 'temp', 50.24, 'unité', '2026-02-28 20:38:11', 28),
(20001, 'temp', 87.9, 'unité', '2026-02-28 20:38:11', 29),
(20002, 'temp', 55.13, 'unité', '2026-02-28 20:38:16', 28),
(20003, 'temp', 99.05, 'unité', '2026-02-28 20:38:16', 29),
(20004, 'temp', 11.61, 'unité', '2026-02-28 20:38:21', 28),
(20005, 'temp', 80.23, 'unité', '2026-02-28 20:38:21', 29),
(20006, 'temp', 3.17, 'unité', '2026-02-28 20:38:26', 28),
(20007, 'temp', 95.67, 'unité', '2026-02-28 20:38:26', 29),
(20008, 'temp', 34.14, 'unité', '2026-02-28 20:38:31', 28),
(20009, 'temp', 83.39, 'unité', '2026-02-28 20:38:31', 29),
(20010, 'temp', 42.88, 'unité', '2026-02-28 20:38:36', 28),
(20011, 'temp', 99.82, 'unité', '2026-02-28 20:38:36', 29),
(20012, 'temp', 47.1, 'unité', '2026-02-28 20:38:41', 28),
(20013, 'temp', 24.72, 'unité', '2026-02-28 20:38:41', 29),
(20014, 'temp', 70.5, 'unité', '2026-02-28 20:38:46', 28),
(20015, 'temp', 76, 'unité', '2026-02-28 20:38:46', 29),
(20016, 'temp', 23.83, 'unité', '2026-02-28 20:38:51', 28),
(20017, 'temp', 96.93, 'unité', '2026-02-28 20:38:51', 29),
(20018, 'temp', 70.89, 'unité', '2026-02-28 20:38:56', 28),
(20019, 'temp', 64.12, 'unité', '2026-02-28 20:38:56', 29),
(20020, 'temp', 89.36, 'unité', '2026-02-28 20:39:01', 28),
(20021, 'temp', 66.58, 'unité', '2026-02-28 20:39:01', 29),
(20022, 'temp', 72.37, 'unité', '2026-02-28 20:39:06', 28),
(20023, 'temp', 19.71, 'unité', '2026-02-28 20:39:06', 29),
(20024, 'temp', 13.78, 'unité', '2026-02-28 20:39:12', 28),
(20025, 'temp', 53.32, 'unité', '2026-02-28 20:39:12', 29),
(20026, 'temp', 78.96, 'unité', '2026-02-28 20:39:17', 28),
(20027, 'temp', 23.98, 'unité', '2026-02-28 20:39:17', 29),
(20028, 'temp', 58.01, 'unité', '2026-02-28 20:39:22', 28),
(20029, 'temp', 21.27, 'unité', '2026-02-28 20:39:22', 29),
(20030, 'temp', 94.45, 'unité', '2026-02-28 20:39:27', 28),
(20031, 'temp', 64.32, 'unité', '2026-02-28 20:39:27', 29),
(20032, 'temp', 18.43, 'unité', '2026-02-28 20:39:32', 28),
(20033, 'temp', 68, 'unité', '2026-02-28 20:39:32', 29),
(20034, 'temp', 39.81, 'unité', '2026-02-28 20:39:37', 28),
(20035, 'temp', 38.9, 'unité', '2026-02-28 20:39:37', 29),
(20036, 'temp', 63.76, 'unité', '2026-02-28 20:39:42', 28),
(20037, 'temp', 52.28, 'unité', '2026-02-28 20:39:42', 29),
(20038, 'temp', 9.43, 'unité', '2026-02-28 20:39:47', 28),
(20039, 'temp', 93.15, 'unité', '2026-02-28 20:39:47', 29),
(20040, 'temp', 43.33, 'unité', '2026-02-28 20:39:52', 28),
(20041, 'temp', 22.71, 'unité', '2026-02-28 20:39:52', 29),
(20042, 'temp', 45.01, 'unité', '2026-02-28 20:39:57', 28),
(20043, 'temp', 13.26, 'unité', '2026-02-28 20:39:57', 29),
(20044, 'temp', 57.7, 'unité', '2026-02-28 20:40:02', 28),
(20045, 'temp', 71.95, 'unité', '2026-02-28 20:40:02', 29),
(20046, 'temp', 24.99, 'unité', '2026-02-28 20:40:07', 28),
(20047, 'temp', 25.73, 'unité', '2026-02-28 20:40:07', 29),
(20048, 'temp', 65.81, 'unité', '2026-02-28 20:40:12', 28),
(20049, 'temp', 61.09, 'unité', '2026-02-28 20:40:12', 29),
(20050, 'temp', 33.04, 'unité', '2026-02-28 20:40:17', 28),
(20051, 'temp', 63.16, 'unité', '2026-02-28 20:40:17', 29),
(20052, 'temp', 30.73, 'unité', '2026-02-28 20:40:22', 28),
(20053, 'temp', 20.13, 'unité', '2026-02-28 20:40:22', 29),
(20054, 'temp', 72.68, 'unité', '2026-02-28 20:40:27', 28),
(20055, 'temp', 86.51, 'unité', '2026-02-28 20:40:27', 29),
(20056, 'temp', 43.55, 'unité', '2026-02-28 20:40:32', 28),
(20057, 'temp', 66.52, 'unité', '2026-02-28 20:40:32', 29),
(20058, 'temp', 43.12, 'unité', '2026-02-28 20:40:37', 28),
(20059, 'temp', 26.13, 'unité', '2026-02-28 20:40:37', 29),
(20060, 'temp', 70.59, 'unité', '2026-02-28 20:40:43', 28),
(20061, 'temp', 33.91, 'unité', '2026-02-28 20:40:43', 29),
(20062, 'temp', 13.5, 'unité', '2026-02-28 20:40:48', 28),
(20063, 'temp', 86.44, 'unité', '2026-02-28 20:40:48', 29),
(20064, 'temp', 12.97, 'unité', '2026-02-28 20:40:53', 28),
(20065, 'temp', 29.94, 'unité', '2026-02-28 20:40:53', 29),
(20066, 'temp', 29.56, 'unité', '2026-02-28 20:40:58', 28),
(20067, 'temp', 83.77, 'unité', '2026-02-28 20:40:58', 29),
(20068, 'temp', 97.18, 'unité', '2026-02-28 20:41:03', 28),
(20069, 'temp', 78.07, 'unité', '2026-02-28 20:41:03', 29),
(20070, 'temp', 55.36, 'unité', '2026-02-28 20:41:08', 28),
(20071, 'temp', 56.26, 'unité', '2026-02-28 20:41:08', 29),
(20072, 'temp', 50.08, 'unité', '2026-02-28 20:41:13', 28),
(20073, 'temp', 24.82, 'unité', '2026-02-28 20:41:13', 29),
(20074, 'temp', 98.4, 'unité', '2026-02-28 20:41:18', 28),
(20075, 'temp', 23.29, 'unité', '2026-02-28 20:41:18', 29),
(20076, 'temp', 43.5, 'unité', '2026-02-28 20:41:23', 28),
(20077, 'temp', 45.05, 'unité', '2026-02-28 20:41:23', 29),
(20078, 'temp', 86.14, 'unité', '2026-02-28 20:41:28', 28),
(20079, 'temp', 41.74, 'unité', '2026-02-28 20:41:28', 29),
(20080, 'temp', 1.65, 'unité', '2026-02-28 20:41:33', 28),
(20081, 'temp', 7.28, 'unité', '2026-02-28 20:41:33', 29),
(20082, 'temp', 64.86, 'unité', '2026-02-28 20:41:38', 28),
(20083, 'temp', 65.95, 'unité', '2026-02-28 20:41:38', 29),
(20084, 'temp', 55.07, 'unité', '2026-02-28 20:41:43', 28),
(20085, 'temp', 16.88, 'unité', '2026-02-28 20:41:43', 29),
(20086, 'temp', 93.49, 'unité', '2026-02-28 20:41:48', 28),
(20087, 'temp', 12.76, 'unité', '2026-02-28 20:41:48', 29),
(20088, 'temp', 4.96, 'unité', '2026-02-28 20:41:53', 28),
(20089, 'temp', 62.06, 'unité', '2026-02-28 20:41:53', 29),
(20090, 'temp', 41.31, 'unité', '2026-02-28 20:41:58', 28),
(20091, 'temp', 23.26, 'unité', '2026-02-28 20:41:58', 29),
(20092, 'temp', 48.24, 'unité', '2026-02-28 20:42:03', 28),
(20093, 'temp', 17.17, 'unité', '2026-02-28 20:42:03', 29),
(20094, 'temp', 64.55, 'unité', '2026-02-28 20:42:09', 28),
(20095, 'temp', 5.02, 'unité', '2026-02-28 20:42:09', 29),
(20096, 'temp', 56.46, 'unité', '2026-02-28 20:42:14', 28),
(20097, 'temp', 86.38, 'unité', '2026-02-28 20:42:14', 29),
(20098, 'temp', 90.32, 'unité', '2026-02-28 20:42:19', 28),
(20099, 'temp', 62.63, 'unité', '2026-02-28 20:42:19', 29),
(20100, 'temp', 42.02, 'unité', '2026-02-28 20:42:24', 28),
(20101, 'temp', 86.59, 'unité', '2026-02-28 20:42:24', 29),
(20102, 'temp', 2.96, 'unité', '2026-02-28 20:42:29', 28),
(20103, 'temp', 2.86, 'unité', '2026-02-28 20:42:29', 29),
(20104, 'temp', 65.15, 'unité', '2026-02-28 20:42:34', 28),
(20105, 'temp', 26.2, 'unité', '2026-02-28 20:42:34', 29),
(20106, 'temp', 21.27, 'unité', '2026-02-28 20:42:39', 28),
(20107, 'temp', 89.77, 'unité', '2026-02-28 20:42:39', 29),
(20108, 'temp', 61.35, 'unité', '2026-02-28 20:42:44', 28),
(20109, 'temp', 95.42, 'unité', '2026-02-28 20:42:44', 29),
(20110, 'temp', 42.64, 'unité', '2026-02-28 20:42:49', 28),
(20111, 'temp', 68.88, 'unité', '2026-02-28 20:42:49', 29),
(20112, 'temp', 25.58, 'unité', '2026-02-28 20:42:54', 28),
(20113, 'temp', 84.5, 'unité', '2026-02-28 20:42:54', 29),
(20114, 'temp', 43.19, 'unité', '2026-02-28 20:42:59', 28),
(20115, 'temp', 1.13, 'unité', '2026-02-28 20:42:59', 29),
(20116, 'temp', 24.56, 'unité', '2026-02-28 20:43:04', 28),
(20117, 'temp', 47.75, 'unité', '2026-02-28 20:43:04', 29),
(20118, 'temp', 82.98, 'unité', '2026-02-28 20:43:09', 28),
(20119, 'temp', 48.15, 'unité', '2026-02-28 20:43:09', 29),
(20120, 'temp', 22.16, 'unité', '2026-02-28 20:43:14', 28),
(20121, 'temp', 27.63, 'unité', '2026-02-28 20:43:14', 29),
(20122, 'temp', 68.92, 'unité', '2026-02-28 20:43:19', 28),
(20123, 'temp', 18.91, 'unité', '2026-02-28 20:43:19', 29),
(20124, 'temp', 45.5, 'unité', '2026-02-28 20:43:24', 28),
(20125, 'temp', 17.92, 'unité', '2026-02-28 20:43:24', 29),
(20126, 'temp', 31.72, 'unité', '2026-02-28 20:43:30', 28),
(20127, 'temp', 82.38, 'unité', '2026-02-28 20:43:30', 29),
(20128, 'temp', 89.99, 'unité', '2026-02-28 20:43:35', 28),
(20129, 'temp', 47.47, 'unité', '2026-02-28 20:43:35', 29),
(20130, 'temp', 24.8, 'unité', '2026-02-28 20:43:40', 28),
(20131, 'temp', 53.26, 'unité', '2026-02-28 20:43:40', 29),
(20132, 'temp', 81.55, 'unité', '2026-02-28 20:43:45', 28),
(20133, 'temp', 32.07, 'unité', '2026-02-28 20:43:45', 29),
(20134, 'temp', 42.73, 'unité', '2026-02-28 20:43:50', 28),
(20135, 'temp', 15.59, 'unité', '2026-02-28 20:43:50', 29),
(20136, 'temp', 50.18, 'unité', '2026-02-28 20:43:55', 28),
(20137, 'temp', 77.36, 'unité', '2026-02-28 20:43:55', 29),
(20138, 'temp', 62.96, 'unité', '2026-02-28 20:44:00', 28),
(20139, 'temp', 87.59, 'unité', '2026-02-28 20:44:00', 29),
(20140, 'temp', 36.3, 'unité', '2026-02-28 20:44:05', 28),
(20141, 'temp', 70.97, 'unité', '2026-02-28 20:44:05', 29),
(20142, 'temp', 14.7, 'unité', '2026-02-28 20:44:10', 28),
(20143, 'temp', 69.63, 'unité', '2026-02-28 20:44:10', 29),
(20144, 'temp', 2.84, 'unité', '2026-02-28 20:44:15', 28),
(20145, 'temp', 30.74, 'unité', '2026-02-28 20:44:15', 29),
(20146, 'temp', 80.55, 'unité', '2026-02-28 20:44:20', 28),
(20147, 'temp', 58.04, 'unité', '2026-02-28 20:44:20', 29),
(20148, 'temp', 54.67, 'unité', '2026-02-28 20:44:25', 28),
(20149, 'temp', 93.68, 'unité', '2026-02-28 20:44:25', 29),
(20150, 'temp', 37.06, 'unité', '2026-02-28 20:44:30', 28),
(20151, 'temp', 57.23, 'unité', '2026-02-28 20:44:30', 29),
(20152, 'temp', 27.43, 'unité', '2026-02-28 20:44:35', 28),
(20153, 'temp', 65.24, 'unité', '2026-02-28 20:44:35', 29),
(20154, 'temp', 13.22, 'unité', '2026-02-28 20:44:40', 28),
(20155, 'temp', 99.87, 'unité', '2026-02-28 20:44:40', 29),
(20156, 'temp', 86.99, 'unité', '2026-02-28 20:44:45', 28),
(20157, 'temp', 51.77, 'unité', '2026-02-28 20:44:46', 29),
(20158, 'temp', 6.94, 'unité', '2026-02-28 20:44:51', 28),
(20159, 'temp', 37.08, 'unité', '2026-02-28 20:44:51', 29),
(20160, 'temp', 17.27, 'unité', '2026-02-28 20:44:56', 28),
(20161, 'temp', 78.17, 'unité', '2026-02-28 20:44:56', 29),
(20162, 'temp', 48.2, 'unité', '2026-02-28 20:45:01', 28),
(20163, 'temp', 34.63, 'unité', '2026-02-28 20:45:01', 29),
(20164, 'temp', 41.99, 'unité', '2026-02-28 20:45:06', 28),
(20165, 'temp', 95.96, 'unité', '2026-02-28 20:45:06', 29),
(20166, 'temp', 17.98, 'unité', '2026-02-28 20:45:11', 28),
(20167, 'temp', 39.34, 'unité', '2026-02-28 20:45:11', 29),
(20168, 'temp', 24.87, 'unité', '2026-02-28 20:45:16', 28),
(20169, 'temp', 57.3, 'unité', '2026-02-28 20:45:16', 29),
(20170, 'temp', 9.17, 'unité', '2026-02-28 20:45:21', 28),
(20171, 'temp', 88.15, 'unité', '2026-02-28 20:45:21', 29),
(20172, 'temp', 10.43, 'unité', '2026-02-28 20:45:26', 28),
(20173, 'temp', 6.07, 'unité', '2026-02-28 20:45:26', 29),
(20174, 'temp', 56.08, 'unité', '2026-02-28 20:45:31', 28),
(20175, 'temp', 77.06, 'unité', '2026-02-28 20:45:31', 29),
(20176, 'temp', 33.28, 'unité', '2026-02-28 20:45:36', 28),
(20177, 'temp', 50.61, 'unité', '2026-02-28 20:45:36', 29),
(20178, 'temp', 37.05, 'unité', '2026-02-28 20:45:41', 28),
(20179, 'temp', 72.25, 'unité', '2026-02-28 20:45:41', 29),
(20180, 'temp', 97.62, 'unité', '2026-02-28 20:45:46', 28),
(20181, 'temp', 65.68, 'unité', '2026-02-28 20:45:46', 29),
(20182, 'temp', 40.29, 'unité', '2026-02-28 20:45:51', 28),
(20183, 'temp', 9.23, 'unité', '2026-02-28 20:45:51', 29),
(20184, 'temp', 22.07, 'unité', '2026-02-28 20:45:57', 28),
(20185, 'temp', 30.47, 'unité', '2026-02-28 20:45:57', 29),
(20186, 'temp', 21.15, 'unité', '2026-02-28 20:46:02', 28),
(20187, 'temp', 52.31, 'unité', '2026-02-28 20:46:02', 29),
(20188, 'temp', 66.21, 'unité', '2026-02-28 20:46:07', 28),
(20189, 'temp', 48.5, 'unité', '2026-02-28 20:46:07', 29),
(20190, 'temp', 68.12, 'unité', '2026-02-28 20:46:12', 28),
(20191, 'temp', 37.03, 'unité', '2026-02-28 20:46:12', 29),
(20192, 'temp', 64.04, 'unité', '2026-02-28 20:46:17', 28),
(20193, 'temp', 42.92, 'unité', '2026-02-28 20:46:17', 29),
(20194, 'temp', 72.55, 'unité', '2026-02-28 20:46:22', 28),
(20195, 'temp', 85.94, 'unité', '2026-02-28 20:46:22', 29),
(20196, 'temp', 33.44, 'unité', '2026-02-28 20:46:27', 28),
(20197, 'temp', 81.68, 'unité', '2026-02-28 20:46:27', 29),
(20198, 'temp', 99.29, 'unité', '2026-02-28 20:46:32', 28),
(20199, 'temp', 10.62, 'unité', '2026-02-28 20:46:32', 29),
(20200, 'temp', 64.38, 'unité', '2026-02-28 20:46:37', 28),
(20201, 'temp', 24.14, 'unité', '2026-02-28 20:46:37', 29),
(20202, 'temp', 6.79, 'unité', '2026-02-28 20:46:42', 28),
(20203, 'temp', 81.31, 'unité', '2026-02-28 20:46:42', 29),
(20204, 'temp', 53.76, 'unité', '2026-02-28 20:46:47', 28),
(20205, 'temp', 69.57, 'unité', '2026-02-28 20:46:47', 29),
(20206, 'temp', 54.13, 'unité', '2026-02-28 20:46:52', 28),
(20207, 'temp', 79.74, 'unité', '2026-02-28 20:46:52', 29),
(20208, 'temp', 88.82, 'unité', '2026-02-28 20:46:57', 28),
(20209, 'temp', 32.08, 'unité', '2026-02-28 20:46:57', 29),
(20210, 'temp', 77.56, 'unité', '2026-02-28 20:47:02', 28),
(20211, 'temp', 49.94, 'unité', '2026-02-28 20:47:02', 29),
(20212, 'temp', 75.56, 'unité', '2026-02-28 20:47:07', 28),
(20213, 'temp', 11.25, 'unité', '2026-02-28 20:47:07', 29),
(20214, 'temp', 68.75, 'unité', '2026-02-28 20:47:12', 28),
(20215, 'temp', 73.2, 'unité', '2026-02-28 20:47:12', 29),
(20216, 'temp', 30.23, 'unité', '2026-02-28 20:47:17', 28),
(20217, 'temp', 21.82, 'unité', '2026-02-28 20:47:17', 29),
(20218, 'temp', 16.16, 'unité', '2026-02-28 20:47:22', 28),
(20219, 'temp', 16.23, 'unité', '2026-02-28 20:47:22', 29),
(20220, 'temp', 61.63, 'unité', '2026-02-28 20:47:28', 28),
(20221, 'temp', 83.32, 'unité', '2026-02-28 20:47:28', 29),
(20222, 'temp', 73.64, 'unité', '2026-02-28 20:47:33', 28),
(20223, 'temp', 32.11, 'unité', '2026-02-28 20:47:33', 29),
(20224, 'temp', 54.77, 'unité', '2026-02-28 20:47:38', 28),
(20225, 'temp', 63.11, 'unité', '2026-02-28 20:47:38', 29),
(20226, 'temp', 33.96, 'unité', '2026-02-28 20:47:43', 28),
(20227, 'temp', 24.09, 'unité', '2026-02-28 20:47:43', 29),
(20228, 'temp', 78.66, 'unité', '2026-02-28 20:47:48', 28),
(20229, 'temp', 95.21, 'unité', '2026-02-28 20:47:48', 29),
(20230, 'temp', 81.98, 'unité', '2026-02-28 20:47:53', 28),
(20231, 'temp', 96.31, 'unité', '2026-02-28 20:47:53', 29),
(20232, 'temp', 51.62, 'unité', '2026-02-28 20:47:58', 28),
(20233, 'temp', 62.78, 'unité', '2026-02-28 20:47:58', 29),
(20234, 'temp', 99.23, 'unité', '2026-02-28 20:48:03', 28),
(20235, 'temp', 33.06, 'unité', '2026-02-28 20:48:03', 29),
(20236, 'temp', 94.68, 'unité', '2026-02-28 20:48:08', 28),
(20237, 'temp', 5.65, 'unité', '2026-02-28 20:48:08', 29),
(20238, 'temp', 91.75, 'unité', '2026-02-28 20:48:13', 28),
(20239, 'temp', 16.13, 'unité', '2026-02-28 20:48:13', 29),
(20240, 'temp', 32.05, 'unité', '2026-02-28 20:48:18', 28),
(20241, 'temp', 69.13, 'unité', '2026-02-28 20:48:18', 29),
(20242, 'temp', 46.01, 'unité', '2026-02-28 20:48:23', 28),
(20243, 'temp', 34.57, 'unité', '2026-02-28 20:48:23', 29),
(20244, 'temp', 5.85, 'unité', '2026-02-28 20:48:28', 28),
(20245, 'temp', 12.56, 'unité', '2026-02-28 20:48:28', 29),
(20246, 'temp', 71.95, 'unité', '2026-02-28 20:48:33', 28),
(20247, 'temp', 72.18, 'unité', '2026-02-28 20:48:33', 29),
(20248, 'temp', 96.9, 'unité', '2026-02-28 20:48:38', 28),
(20249, 'temp', 39.44, 'unité', '2026-02-28 20:48:38', 29),
(20250, 'temp', 56.24, 'unité', '2026-02-28 20:48:44', 28),
(20251, 'temp', 90.13, 'unité', '2026-02-28 20:48:44', 29),
(20252, 'temp', 44, 'unité', '2026-02-28 20:48:49', 28),
(20253, 'temp', 5.03, 'unité', '2026-02-28 20:48:49', 29),
(20254, 'temp', 43.64, 'unité', '2026-02-28 20:48:54', 28),
(20255, 'temp', 33.47, 'unité', '2026-02-28 20:48:54', 29),
(20256, 'temp', 46.26, 'unité', '2026-02-28 20:48:59', 28),
(20257, 'temp', 0.02, 'unité', '2026-02-28 20:48:59', 29),
(20258, 'temp', 57.11, 'unité', '2026-02-28 20:49:04', 28),
(20259, 'temp', 50.32, 'unité', '2026-02-28 20:49:04', 29),
(20260, 'temp', 52.46, 'unité', '2026-02-28 20:49:09', 28),
(20261, 'temp', 68.98, 'unité', '2026-02-28 20:49:09', 29),
(20262, 'temp', 95.07, 'unité', '2026-02-28 20:49:14', 28),
(20263, 'temp', 44.39, 'unité', '2026-02-28 20:49:14', 29),
(20264, 'temp', 77.7, 'unité', '2026-02-28 20:49:19', 28),
(20265, 'temp', 17, 'unité', '2026-02-28 20:49:19', 29),
(20266, 'temp', 3.45, 'unité', '2026-02-28 20:49:24', 28),
(20267, 'temp', 69.06, 'unité', '2026-02-28 20:49:24', 29),
(20268, 'temp', 5.28, 'unité', '2026-02-28 20:49:29', 28),
(20269, 'temp', 70.81, 'unité', '2026-02-28 20:49:29', 29),
(20270, 'temp', 15.54, 'unité', '2026-02-28 20:49:34', 28),
(20271, 'temp', 6.26, 'unité', '2026-02-28 20:49:34', 29),
(20272, 'temp', 37.76, 'unité', '2026-02-28 20:49:39', 28),
(20273, 'temp', 51.33, 'unité', '2026-02-28 20:49:39', 29),
(20274, 'temp', 9.97, 'unité', '2026-02-28 20:49:44', 28),
(20275, 'temp', 77.71, 'unité', '2026-02-28 20:49:44', 29),
(20276, 'temp', 6.68, 'unité', '2026-02-28 20:49:50', 28),
(20277, 'temp', 29.61, 'unité', '2026-02-28 20:49:50', 29),
(20278, 'temp', 80.47, 'unité', '2026-02-28 20:49:55', 28),
(20279, 'temp', 81.22, 'unité', '2026-02-28 20:49:55', 29),
(20280, 'temp', 78.8, 'unité', '2026-02-28 20:50:00', 28),
(20281, 'temp', 18.05, 'unité', '2026-02-28 20:50:00', 29),
(20282, 'temp', 80.33, 'unité', '2026-02-28 20:50:05', 28),
(20283, 'temp', 85.8, 'unité', '2026-02-28 20:50:05', 29),
(20284, 'temp', 10.05, 'unité', '2026-02-28 20:50:10', 28),
(20285, 'temp', 54.16, 'unité', '2026-02-28 20:50:10', 29),
(20286, 'temp', 17.79, 'unité', '2026-02-28 20:50:15', 28),
(20287, 'temp', 9.07, 'unité', '2026-02-28 20:50:15', 29),
(20288, 'temp', 27.1, 'unité', '2026-02-28 20:50:20', 28),
(20289, 'temp', 17.68, 'unité', '2026-02-28 20:50:20', 29),
(20290, 'temp', 91.23, 'unité', '2026-02-28 20:50:25', 28),
(20291, 'temp', 39.98, 'unité', '2026-02-28 20:50:25', 29),
(20292, 'temp', 34.11, 'unité', '2026-02-28 20:50:30', 28),
(20293, 'temp', 40.02, 'unité', '2026-02-28 20:50:30', 29),
(20294, 'temp', 5.05, 'unité', '2026-02-28 20:50:35', 28),
(20295, 'temp', 97.56, 'unité', '2026-02-28 20:50:35', 29),
(20296, 'temp', 39.06, 'unité', '2026-02-28 20:50:40', 28),
(20297, 'temp', 58, 'unité', '2026-02-28 20:50:40', 29),
(20298, 'temp', 62.81, 'unité', '2026-02-28 20:50:45', 28),
(20299, 'temp', 73.09, 'unité', '2026-02-28 20:50:45', 29),
(20300, 'temp', 91.19, 'unité', '2026-02-28 20:50:50', 28),
(20301, 'temp', 54.35, 'unité', '2026-02-28 20:50:50', 29),
(20302, 'temp', 86.32, 'unité', '2026-02-28 20:50:55', 28),
(20303, 'temp', 35.07, 'unité', '2026-02-28 20:50:55', 29),
(20304, 'temp', 18.97, 'unité', '2026-02-28 20:51:00', 28),
(20305, 'temp', 55.08, 'unité', '2026-02-28 20:51:00', 29),
(20306, 'temp', 76.84, 'unité', '2026-02-28 20:51:05', 28),
(20307, 'temp', 75.56, 'unité', '2026-02-28 20:51:05', 29),
(20308, 'temp', 84.55, 'unité', '2026-02-28 20:51:11', 28),
(20309, 'temp', 82.6, 'unité', '2026-02-28 20:51:11', 29),
(20310, 'temp', 41.07, 'unité', '2026-02-28 20:51:16', 28),
(20311, 'temp', 65.1, 'unité', '2026-02-28 20:51:16', 29),
(20312, 'temp', 71.32, 'unité', '2026-02-28 20:51:21', 28),
(20313, 'temp', 75.7, 'unité', '2026-02-28 20:51:21', 29),
(20314, 'temp', 54.6, 'unité', '2026-02-28 20:51:26', 28),
(20315, 'temp', 0.22, 'unité', '2026-02-28 20:51:26', 29),
(20316, 'temp', 70.34, 'unité', '2026-02-28 20:51:31', 28),
(20317, 'temp', 15.7, 'unité', '2026-02-28 20:51:31', 29),
(20318, 'temp', 66.82, 'unité', '2026-02-28 20:51:36', 28),
(20319, 'temp', 50.53, 'unité', '2026-02-28 20:51:36', 29),
(20320, 'temp', 87.26, 'unité', '2026-02-28 20:51:41', 28),
(20321, 'temp', 48.81, 'unité', '2026-02-28 20:51:41', 29),
(20322, 'temp', 52.35, 'unité', '2026-02-28 20:51:46', 28),
(20323, 'temp', 16.15, 'unité', '2026-02-28 20:51:46', 29),
(20324, 'temp', 40.11, 'unité', '2026-02-28 20:51:51', 28),
(20325, 'temp', 96.26, 'unité', '2026-02-28 20:51:51', 29),
(20326, 'temp', 52, 'unité', '2026-02-28 20:51:56', 28),
(20327, 'temp', 68.64, 'unité', '2026-02-28 20:51:56', 29),
(20328, 'temp', 90.84, 'unité', '2026-02-28 20:52:01', 28),
(20329, 'temp', 18.57, 'unité', '2026-02-28 20:52:01', 29),
(20330, 'temp', 98.22, 'unité', '2026-02-28 20:52:06', 28),
(20331, 'temp', 86.92, 'unité', '2026-02-28 20:52:06', 29),
(20332, 'temp', 66.63, 'unité', '2026-02-28 20:52:11', 28),
(20333, 'temp', 91.24, 'unité', '2026-02-28 20:52:11', 29),
(20334, 'temp', 6.79, 'unité', '2026-02-28 20:52:16', 28),
(20335, 'temp', 29.11, 'unité', '2026-02-28 20:52:16', 29),
(20336, 'temp', 75.56, 'unité', '2026-02-28 20:52:21', 28),
(20337, 'temp', 21.07, 'unité', '2026-02-28 20:52:21', 29),
(20338, 'temp', 43.33, 'unité', '2026-02-28 20:52:26', 28),
(20339, 'temp', 61.74, 'unité', '2026-02-28 20:52:26', 29),
(20340, 'temp', 2.43, 'unité', '2026-02-28 20:52:31', 28),
(20341, 'temp', 48.03, 'unité', '2026-02-28 20:52:32', 29),
(20342, 'temp', 28.83, 'unité', '2026-02-28 20:52:37', 28),
(20343, 'temp', 63.34, 'unité', '2026-02-28 20:52:37', 29),
(20344, 'temp', 86.74, 'unité', '2026-02-28 20:52:42', 28),
(20345, 'temp', 35.72, 'unité', '2026-02-28 20:52:42', 29),
(20346, 'temp', 70.17, 'unité', '2026-02-28 20:52:47', 28),
(20347, 'temp', 70.78, 'unité', '2026-02-28 20:52:47', 29),
(20348, 'temp', 63.21, 'unité', '2026-02-28 20:52:52', 28),
(20349, 'temp', 54.57, 'unité', '2026-02-28 20:52:52', 29),
(20350, 'temp', 71.71, 'unité', '2026-02-28 20:52:57', 28),
(20351, 'temp', 8.38, 'unité', '2026-02-28 20:52:57', 29),
(20352, 'temp', 29.76, 'unité', '2026-02-28 20:53:02', 28),
(20353, 'temp', 3.63, 'unité', '2026-02-28 20:53:02', 29),
(20354, 'temp', 81.45, 'unité', '2026-02-28 20:53:07', 28),
(20355, 'temp', 60.73, 'unité', '2026-02-28 20:53:07', 29),
(20356, 'temp', 92.27, 'unité', '2026-02-28 20:53:12', 28),
(20357, 'temp', 56.38, 'unité', '2026-02-28 20:53:12', 29),
(20358, 'temp', 9.55, 'unité', '2026-02-28 20:53:17', 28),
(20359, 'temp', 16.95, 'unité', '2026-02-28 20:53:17', 29),
(20360, 'temp', 34.95, 'unité', '2026-02-28 20:53:22', 28),
(20361, 'temp', 62.58, 'unité', '2026-02-28 20:53:22', 29),
(20362, 'temp', 10.76, 'unité', '2026-02-28 20:53:27', 28),
(20363, 'temp', 82.81, 'unité', '2026-02-28 20:53:27', 29),
(20364, 'temp', 85.91, 'unité', '2026-02-28 20:53:32', 28),
(20365, 'temp', 46.8, 'unité', '2026-02-28 20:53:32', 29),
(20366, 'temp', 89.4, 'unité', '2026-02-28 20:53:37', 28),
(20367, 'temp', 20.29, 'unité', '2026-02-28 20:53:37', 29),
(20368, 'temp', 73.87, 'unité', '2026-02-28 20:53:42', 28),
(20369, 'temp', 2.61, 'unité', '2026-02-28 20:53:42', 29),
(20370, 'temp', 80.76, 'unité', '2026-02-28 20:53:47', 28),
(20371, 'temp', 22.74, 'unité', '2026-02-28 20:53:47', 29),
(20372, 'temp', 23.72, 'unité', '2026-02-28 20:53:52', 28),
(20373, 'temp', 2, 'unité', '2026-02-28 20:53:52', 29),
(20374, 'temp', 61.53, 'unité', '2026-02-28 20:53:58', 28),
(20375, 'temp', 23.88, 'unité', '2026-02-28 20:53:58', 29),
(20376, 'temp', 33.36, 'unité', '2026-02-28 20:54:03', 28),
(20377, 'temp', 38.03, 'unité', '2026-02-28 20:54:03', 29),
(20378, 'temp', 73.18, 'unité', '2026-02-28 20:54:08', 28),
(20379, 'temp', 93.8, 'unité', '2026-02-28 20:54:08', 29),
(20380, 'temp', 75.9, 'unité', '2026-02-28 20:54:13', 28),
(20381, 'temp', 36.94, 'unité', '2026-02-28 20:54:13', 29),
(20382, 'temp', 42.64, 'unité', '2026-02-28 20:54:18', 28),
(20383, 'temp', 19.9, 'unité', '2026-02-28 20:54:18', 29),
(20384, 'temp', 23.27, 'unité', '2026-02-28 20:54:23', 28),
(20385, 'temp', 71.26, 'unité', '2026-02-28 20:54:23', 29),
(20386, 'temp', 60.43, 'unité', '2026-02-28 20:54:28', 28),
(20387, 'temp', 38.65, 'unité', '2026-02-28 20:54:28', 29),
(20388, 'temp', 35.53, 'unité', '2026-02-28 20:54:33', 28),
(20389, 'temp', 92.85, 'unité', '2026-02-28 20:54:33', 29),
(20390, 'temp', 29.22, 'unité', '2026-02-28 20:54:38', 28),
(20391, 'temp', 16.19, 'unité', '2026-02-28 20:54:38', 29),
(20392, 'temp', 91.46, 'unité', '2026-02-28 20:54:43', 28),
(20393, 'temp', 36.91, 'unité', '2026-02-28 20:54:43', 29),
(20394, 'temp', 35, 'unité', '2026-02-28 20:54:48', 28),
(20395, 'temp', 61.16, 'unité', '2026-02-28 20:54:48', 29),
(20396, 'temp', 79.5, 'unité', '2026-02-28 20:54:53', 28),
(20397, 'temp', 20.16, 'unité', '2026-02-28 20:54:53', 29),
(20398, 'temp', 15.7, 'unité', '2026-02-28 20:54:58', 28),
(20399, 'temp', 43.04, 'unité', '2026-02-28 20:54:58', 29),
(20400, 'temp', 23.83, 'unité', '2026-02-28 20:55:03', 28),
(20401, 'temp', 41.33, 'unité', '2026-02-28 20:55:04', 29),
(20402, 'temp', 9.09, 'unité', '2026-02-28 20:55:09', 28),
(20403, 'temp', 36.86, 'unité', '2026-02-28 20:55:09', 29),
(20404, 'temp', 41.16, 'unité', '2026-02-28 20:55:14', 28),
(20405, 'temp', 55.93, 'unité', '2026-02-28 20:55:14', 29),
(20406, 'temp', 70.05, 'unité', '2026-02-28 20:55:19', 28),
(20407, 'temp', 19.95, 'unité', '2026-02-28 20:55:19', 29),
(20408, 'temp', 58.88, 'unité', '2026-02-28 20:55:24', 28),
(20409, 'temp', 3.18, 'unité', '2026-02-28 20:55:24', 29),
(20410, 'temp', 6.34, 'unité', '2026-02-28 20:55:29', 28),
(20411, 'temp', 49.14, 'unité', '2026-02-28 20:55:29', 29),
(20412, 'temp', 76.58, 'unité', '2026-02-28 20:55:34', 28),
(20413, 'temp', 55.17, 'unité', '2026-02-28 20:55:34', 29),
(20414, 'temp', 19.38, 'unité', '2026-02-28 20:55:39', 28),
(20415, 'temp', 76.93, 'unité', '2026-02-28 20:55:39', 29),
(20416, 'temp', 94.67, 'unité', '2026-02-28 20:55:44', 28),
(20417, 'temp', 19.04, 'unité', '2026-02-28 20:55:44', 29),
(20418, 'temp', 85.91, 'unité', '2026-02-28 20:55:49', 28),
(20419, 'temp', 97.7, 'unité', '2026-02-28 20:55:49', 29),
(20420, 'temp', 75.41, 'unité', '2026-02-28 20:55:54', 28),
(20421, 'temp', 1.73, 'unité', '2026-02-28 20:55:54', 29),
(20422, 'temp', 82.57, 'unité', '2026-02-28 20:55:59', 28),
(20423, 'temp', 97.31, 'unité', '2026-02-28 20:55:59', 29),
(20424, 'temp', 18.92, 'unité', '2026-02-28 20:56:04', 28),
(20425, 'temp', 7.38, 'unité', '2026-02-28 20:56:04', 29),
(20426, 'temp', 82.78, 'unité', '2026-02-28 20:56:09', 28),
(20427, 'temp', 12.22, 'unité', '2026-02-28 20:56:09', 29),
(20428, 'temp', 25.46, 'unité', '2026-02-28 20:56:14', 28),
(20429, 'temp', 71.81, 'unité', '2026-02-28 20:56:14', 29),
(20430, 'temp', 14.48, 'unité', '2026-02-28 20:56:19', 28),
(20431, 'temp', 70.43, 'unité', '2026-02-28 20:56:20', 29),
(20432, 'temp', 2.72, 'unité', '2026-02-28 20:56:25', 28),
(20433, 'temp', 83.26, 'unité', '2026-02-28 20:56:25', 29),
(20434, 'temp', 92.04, 'unité', '2026-02-28 20:56:30', 28),
(20435, 'temp', 39.69, 'unité', '2026-02-28 20:56:30', 29),
(20436, 'temp', 13.62, 'unité', '2026-02-28 20:56:35', 28),
(20437, 'temp', 53.73, 'unité', '2026-02-28 20:56:35', 29),
(20438, 'temp', 27.41, 'unité', '2026-02-28 20:56:40', 28),
(20439, 'temp', 39.45, 'unité', '2026-02-28 20:56:40', 29),
(20440, 'temp', 9.51, 'unité', '2026-02-28 20:56:45', 28),
(20441, 'temp', 31.98, 'unité', '2026-02-28 20:56:45', 29),
(20442, 'temp', 33.96, 'unité', '2026-02-28 20:56:50', 28),
(20443, 'temp', 85.75, 'unité', '2026-02-28 20:56:50', 29),
(20444, 'temp', 64.4, 'unité', '2026-02-28 20:56:55', 28),
(20445, 'temp', 64.24, 'unité', '2026-02-28 20:56:55', 29),
(20446, 'temp', 37.98, 'unité', '2026-02-28 20:57:00', 28),
(20447, 'temp', 69.77, 'unité', '2026-02-28 20:57:00', 29),
(20448, 'temp', 69.35, 'unité', '2026-02-28 20:57:05', 28),
(20449, 'temp', 41.68, 'unité', '2026-02-28 20:57:05', 29),
(20450, 'temp', 26.93, 'unité', '2026-02-28 20:57:11', 28),
(20451, 'temp', 94.33, 'unité', '2026-02-28 20:57:11', 29),
(20452, 'temp', 42.35, 'unité', '2026-02-28 20:57:16', 28),
(20453, 'temp', 41.87, 'unité', '2026-02-28 20:57:16', 29),
(20454, 'temp', 50.07, 'unité', '2026-02-28 20:57:21', 28),
(20455, 'temp', 83.94, 'unité', '2026-02-28 20:57:21', 29),
(20456, 'temp', 70.22, 'unité', '2026-02-28 20:57:26', 28),
(20457, 'temp', 24.63, 'unité', '2026-02-28 20:57:26', 29),
(20458, 'temp', 77.78, 'unité', '2026-02-28 20:57:31', 28),
(20459, 'temp', 66.25, 'unité', '2026-02-28 20:57:31', 29),
(20460, 'temp', 41.03, 'unité', '2026-02-28 20:57:36', 28),
(20461, 'temp', 50.95, 'unité', '2026-02-28 20:57:36', 29),
(20462, 'temp', 2.04, 'unité', '2026-02-28 20:57:41', 28),
(20463, 'temp', 10.05, 'unité', '2026-02-28 20:57:41', 29),
(20464, 'temp', 20.3, 'unité', '2026-02-28 20:57:46', 28),
(20465, 'temp', 17.35, 'unité', '2026-02-28 20:57:46', 29),
(20466, 'temp', 34.78, 'unité', '2026-02-28 20:57:51', 28),
(20467, 'temp', 46.19, 'unité', '2026-02-28 20:57:51', 29),
(20468, 'temp', 69.19, 'unité', '2026-02-28 20:57:57', 28),
(20469, 'temp', 85.04, 'unité', '2026-02-28 20:57:57', 29),
(20470, 'temp', 17.53, 'unité', '2026-02-28 20:58:02', 28),
(20471, 'temp', 13.67, 'unité', '2026-02-28 20:58:02', 29),
(20472, 'temp', 47.27, 'unité', '2026-02-28 20:58:07', 28),
(20473, 'temp', 66.53, 'unité', '2026-02-28 20:58:07', 29),
(20474, 'temp', 7.27, 'unité', '2026-02-28 20:58:12', 28),
(20475, 'temp', 76.29, 'unité', '2026-02-28 20:58:12', 29),
(20476, 'temp', 88.44, 'unité', '2026-02-28 20:58:17', 28),
(20477, 'temp', 29.46, 'unité', '2026-02-28 20:58:17', 29),
(20478, 'temp', 1.76, 'unité', '2026-02-28 20:58:22', 28),
(20479, 'temp', 7.67, 'unité', '2026-02-28 20:58:22', 29),
(20480, 'temp', 62.53, 'unité', '2026-02-28 20:58:27', 28),
(20481, 'temp', 63.8, 'unité', '2026-02-28 20:58:27', 29),
(20482, 'temp', 59.37, 'unité', '2026-02-28 20:58:32', 28),
(20483, 'temp', 5.36, 'unité', '2026-02-28 20:58:32', 29),
(20484, 'temp', 93.7, 'unité', '2026-02-28 20:58:37', 28),
(20485, 'temp', 92.7, 'unité', '2026-02-28 20:58:38', 29),
(20486, 'temp', 35.06, 'unité', '2026-02-28 20:58:43', 28),
(20487, 'temp', 71.39, 'unité', '2026-02-28 20:58:43', 29),
(20488, 'temp', 15.12, 'unité', '2026-02-28 20:58:48', 28),
(20489, 'temp', 45.51, 'unité', '2026-02-28 20:58:48', 29),
(20490, 'temp', 62.67, 'unité', '2026-02-28 20:58:53', 28),
(20491, 'temp', 26.14, 'unité', '2026-02-28 20:58:53', 29),
(20492, 'temp', 63.02, 'unité', '2026-02-28 20:58:58', 28),
(20493, 'temp', 91.52, 'unité', '2026-02-28 20:58:58', 29),
(20494, 'temp', 87.55, 'unité', '2026-02-28 20:59:03', 28),
(20495, 'temp', 78.05, 'unité', '2026-02-28 20:59:03', 29),
(20496, 'temp', 94.4, 'unité', '2026-02-28 20:59:08', 28),
(20497, 'temp', 17.47, 'unité', '2026-02-28 20:59:08', 29),
(20498, 'temp', 43.12, 'unité', '2026-02-28 20:59:13', 28),
(20499, 'temp', 81.17, 'unité', '2026-02-28 20:59:13', 29),
(20500, 'temp', 43.21, 'unité', '2026-02-28 20:59:18', 28),
(20501, 'temp', 0.17, 'unité', '2026-02-28 20:59:18', 29),
(20502, 'temp', 46.41, 'unité', '2026-02-28 20:59:24', 28),
(20503, 'temp', 22.41, 'unité', '2026-02-28 20:59:24', 29),
(20504, 'temp', 12.17, 'unité', '2026-02-28 20:59:29', 28),
(20505, 'temp', 95.39, 'unité', '2026-02-28 20:59:29', 29),
(20506, 'temp', 52.47, 'unité', '2026-02-28 20:59:34', 28),
(20507, 'temp', 6.89, 'unité', '2026-02-28 20:59:34', 29),
(20508, 'temp', 99.41, 'unité', '2026-02-28 20:59:39', 28),
(20509, 'temp', 40.35, 'unité', '2026-02-28 20:59:39', 29),
(20510, 'temp', 23.71, 'unité', '2026-02-28 20:59:44', 28),
(20511, 'temp', 78.6, 'unité', '2026-02-28 20:59:44', 29),
(20512, 'temp', 33.63, 'unité', '2026-02-28 20:59:49', 28),
(20513, 'temp', 1.08, 'unité', '2026-02-28 20:59:49', 29),
(20514, 'temp', 22.53, 'unité', '2026-02-28 20:59:54', 28),
(20515, 'temp', 77.32, 'unité', '2026-02-28 20:59:54', 29),
(20516, 'temp', 27.08, 'unité', '2026-02-28 21:00:00', 28),
(20517, 'temp', 10.29, 'unité', '2026-02-28 21:00:00', 29),
(20518, 'temp', 30.7, 'unité', '2026-02-28 21:00:05', 28),
(20519, 'temp', 95.4, 'unité', '2026-02-28 21:00:05', 29),
(20520, 'temp', 64.15, 'unité', '2026-02-28 21:00:10', 28),
(20521, 'temp', 98.62, 'unité', '2026-02-28 21:00:10', 29),
(20522, 'temp', 11.89, 'unité', '2026-02-28 21:00:15', 28),
(20523, 'temp', 42.34, 'unité', '2026-02-28 21:00:15', 29),
(20524, 'temp', 48.31, 'unité', '2026-02-28 21:00:20', 28),
(20525, 'temp', 55.92, 'unité', '2026-02-28 21:00:20', 29),
(20526, 'temp', 51.21, 'unité', '2026-02-28 21:00:25', 28),
(20527, 'temp', 67.61, 'unité', '2026-02-28 21:00:25', 29),
(20528, 'temp', 10.53, 'unité', '2026-02-28 21:00:30', 28),
(20529, 'temp', 13.34, 'unité', '2026-02-28 21:00:30', 29),
(20530, 'temp', 93.63, 'unité', '2026-02-28 21:00:35', 28),
(20531, 'temp', 21.14, 'unité', '2026-02-28 21:00:36', 29),
(20532, 'temp', 13.84, 'unité', '2026-02-28 21:00:41', 28),
(20533, 'temp', 87.04, 'unité', '2026-02-28 21:00:41', 29),
(20534, 'temp', 34.76, 'unité', '2026-02-28 21:00:46', 28),
(20535, 'temp', 28.5, 'unité', '2026-02-28 21:00:46', 29),
(20536, 'temp', 38.5, 'unité', '2026-02-28 21:00:51', 28),
(20537, 'temp', 44.03, 'unité', '2026-02-28 21:00:51', 29),
(20538, 'temp', 75.27, 'unité', '2026-02-28 21:00:56', 28),
(20539, 'temp', 61.49, 'unité', '2026-02-28 21:00:56', 29),
(20540, 'temp', 65.55, 'unité', '2026-02-28 21:01:01', 28),
(20541, 'temp', 66.42, 'unité', '2026-02-28 21:01:01', 29),
(20542, 'temp', 53.38, 'unité', '2026-02-28 21:01:06', 28),
(20543, 'temp', 77.74, 'unité', '2026-02-28 21:01:06', 29),
(20544, 'temp', 77.14, 'unité', '2026-02-28 21:01:11', 28),
(20545, 'temp', 81.39, 'unité', '2026-02-28 21:01:11', 29),
(20546, 'temp', 10.91, 'unité', '2026-02-28 21:01:17', 28),
(20547, 'temp', 9.78, 'unité', '2026-02-28 21:01:17', 29),
(20548, 'temp', 20.49, 'unité', '2026-02-28 21:01:22', 28),
(20549, 'temp', 11.61, 'unité', '2026-02-28 21:01:22', 29),
(20550, 'temp', 74.51, 'unité', '2026-02-28 21:01:27', 28),
(20551, 'temp', 66.58, 'unité', '2026-02-28 21:01:27', 29),
(20552, 'temp', 61.55, 'unité', '2026-02-28 21:01:32', 28),
(20553, 'temp', 60.75, 'unité', '2026-02-28 21:01:32', 29),
(20554, 'temp', 48.48, 'unité', '2026-02-28 21:01:37', 28),
(20555, 'temp', 12.23, 'unité', '2026-02-28 21:01:37', 29),
(20556, 'temp', 48.37, 'unité', '2026-02-28 21:01:42', 28),
(20557, 'temp', 18.62, 'unité', '2026-02-28 21:01:42', 29),
(20558, 'temp', 94.53, 'unité', '2026-02-28 21:01:47', 28),
(20559, 'temp', 37.78, 'unité', '2026-02-28 21:01:47', 29),
(20560, 'temp', 36.9, 'unité', '2026-02-28 21:01:53', 28),
(20561, 'temp', 69.83, 'unité', '2026-02-28 21:01:53', 29),
(20562, 'temp', 83.55, 'unité', '2026-02-28 21:01:58', 28),
(20563, 'temp', 31.94, 'unité', '2026-02-28 21:01:58', 29),
(20564, 'temp', 2.5, 'unité', '2026-02-28 21:02:03', 28),
(20565, 'temp', 96.13, 'unité', '2026-02-28 21:02:03', 29),
(20566, 'temp', 47.94, 'unité', '2026-02-28 21:02:08', 28),
(20567, 'temp', 52.04, 'unité', '2026-02-28 21:02:08', 29),
(20568, 'temp', 64.16, 'unité', '2026-02-28 21:02:13', 28),
(20569, 'temp', 45.66, 'unité', '2026-02-28 21:02:13', 29),
(20570, 'temp', 39.5, 'unité', '2026-02-28 21:02:18', 28),
(20571, 'temp', 13.11, 'unité', '2026-02-28 21:02:18', 29),
(20572, 'temp', 68.41, 'unité', '2026-02-28 21:02:23', 28),
(20573, 'temp', 36.31, 'unité', '2026-02-28 21:02:23', 29),
(20574, 'temp', 50.4, 'unité', '2026-02-28 21:02:28', 28),
(20575, 'temp', 65.32, 'unité', '2026-02-28 21:02:28', 29),
(20576, 'temp', 30.99, 'unité', '2026-02-28 21:02:34', 28),
(20577, 'temp', 20.37, 'unité', '2026-02-28 21:02:34', 29),
(20578, 'temp', 52.87, 'unité', '2026-02-28 21:02:39', 28),
(20579, 'temp', 26.06, 'unité', '2026-02-28 21:02:39', 29),
(20580, 'temp', 59.09, 'unité', '2026-02-28 21:02:44', 28),
(20581, 'temp', 70.26, 'unité', '2026-02-28 21:02:44', 29),
(20582, 'temp', 65.89, 'unité', '2026-02-28 21:02:49', 28),
(20583, 'temp', 35.41, 'unité', '2026-02-28 21:02:49', 29),
(20584, 'temp', 93.34, 'unité', '2026-02-28 21:02:54', 28),
(20585, 'temp', 62.94, 'unité', '2026-02-28 21:02:54', 29),
(20586, 'temp', 15.93, 'unité', '2026-02-28 21:02:59', 28),
(20587, 'temp', 15.87, 'unité', '2026-02-28 21:02:59', 29),
(20588, 'temp', 26.57, 'unité', '2026-02-28 21:03:04', 28),
(20589, 'temp', 34.79, 'unité', '2026-02-28 21:03:04', 29),
(20590, 'temp', 47.81, 'unité', '2026-02-28 21:03:10', 28),
(20591, 'temp', 82.15, 'unité', '2026-02-28 21:03:10', 29),
(20592, 'temp', 0.15, 'unité', '2026-02-28 21:03:15', 28),
(20593, 'temp', 85.03, 'unité', '2026-02-28 21:03:15', 29),
(20594, 'temp', 5.31, 'unité', '2026-02-28 21:03:20', 28),
(20595, 'temp', 69, 'unité', '2026-02-28 21:03:20', 29),
(20596, 'temp', 69.92, 'unité', '2026-02-28 21:03:25', 28),
(20597, 'temp', 27.21, 'unité', '2026-02-28 21:03:25', 29),
(20598, 'temp', 74.95, 'unité', '2026-02-28 21:03:30', 28),
(20599, 'temp', 22.31, 'unité', '2026-02-28 21:03:30', 29),
(20600, 'temp', 86.84, 'unité', '2026-02-28 21:03:35', 28),
(20601, 'temp', 7.08, 'unité', '2026-02-28 21:03:35', 29),
(20602, 'temp', 95.73, 'unité', '2026-02-28 21:03:40', 28),
(20603, 'temp', 86.93, 'unité', '2026-02-28 21:03:40', 29),
(20604, 'temp', 38.66, 'unité', '2026-02-28 21:03:45', 28),
(20605, 'temp', 64.87, 'unité', '2026-02-28 21:03:45', 29),
(20606, 'temp', 18.96, 'unité', '2026-02-28 21:03:51', 28),
(20607, 'temp', 11.5, 'unité', '2026-02-28 21:03:51', 29),
(20608, 'temp', 0.57, 'unité', '2026-02-28 21:03:56', 28),
(20609, 'temp', 70.36, 'unité', '2026-02-28 21:03:56', 29),
(20610, 'temp', 32.68, 'unité', '2026-02-28 21:04:01', 28),
(20611, 'temp', 38.74, 'unité', '2026-02-28 21:04:01', 29),
(20612, 'temp', 87.57, 'unité', '2026-02-28 21:04:06', 28),
(20613, 'temp', 79.87, 'unité', '2026-02-28 21:04:06', 29),
(20614, 'temp', 20.97, 'unité', '2026-02-28 21:04:11', 28),
(20615, 'temp', 47.03, 'unité', '2026-02-28 21:04:11', 29),
(20616, 'temp', 13.27, 'unité', '2026-02-28 21:04:16', 28),
(20617, 'temp', 51.64, 'unité', '2026-02-28 21:04:16', 29),
(20618, 'temp', 52.62, 'unité', '2026-02-28 21:04:21', 28),
(20619, 'temp', 92.83, 'unité', '2026-02-28 21:04:21', 29),
(20620, 'temp', 77.21, 'unité', '2026-02-28 21:04:27', 28),
(20621, 'temp', 1.12, 'unité', '2026-02-28 21:04:27', 29),
(20622, 'temp', 64.03, 'unité', '2026-02-28 21:04:32', 28),
(20623, 'temp', 49.37, 'unité', '2026-02-28 21:04:32', 29),
(20624, 'temp', 66.49, 'unité', '2026-02-28 21:04:37', 28),
(20625, 'temp', 67.62, 'unité', '2026-02-28 21:04:37', 29),
(20626, 'temp', 47.16, 'unité', '2026-02-28 21:04:42', 28),
(20627, 'temp', 79.35, 'unité', '2026-02-28 21:04:42', 29),
(20628, 'temp', 98.5, 'unité', '2026-02-28 21:04:47', 28),
(20629, 'temp', 79.82, 'unité', '2026-02-28 21:04:47', 29),
(20630, 'temp', 98.32, 'unité', '2026-02-28 21:04:52', 28),
(20631, 'temp', 46.28, 'unité', '2026-02-28 21:04:52', 29),
(20632, 'temp', 0.09, 'unité', '2026-02-28 21:04:57', 28),
(20633, 'temp', 75.15, 'unité', '2026-02-28 21:04:57', 29),
(20634, 'temp', 93.6, 'unité', '2026-02-28 21:05:02', 28),
(20635, 'temp', 88.53, 'unité', '2026-02-28 21:05:02', 29),
(20636, 'temp', 77.35, 'unité', '2026-02-28 21:05:07', 28),
(20637, 'temp', 97.35, 'unité', '2026-02-28 21:05:08', 29),
(20638, 'temp', 2.17, 'unité', '2026-02-28 21:05:13', 28),
(20639, 'temp', 62.32, 'unité', '2026-02-28 21:05:13', 29),
(20640, 'temp', 37.41, 'unité', '2026-02-28 21:05:18', 28),
(20641, 'temp', 38.04, 'unité', '2026-02-28 21:05:18', 29),
(20642, 'temp', 57.59, 'unité', '2026-02-28 21:05:23', 28),
(20643, 'temp', 98.08, 'unité', '2026-02-28 21:05:23', 29),
(20644, 'temp', 72.16, 'unité', '2026-02-28 21:05:28', 28),
(20645, 'temp', 51.62, 'unité', '2026-02-28 21:05:28', 29),
(20646, 'temp', 66.77, 'unité', '2026-02-28 21:05:33', 28),
(20647, 'temp', 54.55, 'unité', '2026-02-28 21:05:33', 29),
(20648, 'temp', 83.61, 'unité', '2026-02-28 21:05:38', 28),
(20649, 'temp', 80.6, 'unité', '2026-02-28 21:05:38', 29),
(20650, 'temp', 76.64, 'unité', '2026-02-28 21:05:43', 28),
(20651, 'temp', 60.29, 'unité', '2026-02-28 21:05:43', 29),
(20652, 'temp', 82.76, 'unité', '2026-02-28 21:05:48', 28),
(20653, 'temp', 18.79, 'unité', '2026-02-28 21:05:48', 29),
(20654, 'temp', 5.51, 'unité', '2026-02-28 21:05:53', 28),
(20655, 'temp', 89.64, 'unité', '2026-02-28 21:05:54', 29),
(20656, 'temp', 2.21, 'unité', '2026-02-28 21:05:59', 28),
(20657, 'temp', 41.63, 'unité', '2026-02-28 21:05:59', 29),
(20658, 'temp', 42.29, 'unité', '2026-02-28 21:06:04', 28),
(20659, 'temp', 10.34, 'unité', '2026-02-28 21:06:04', 29),
(20660, 'temp', 8.11, 'unité', '2026-02-28 21:06:09', 28),
(20661, 'temp', 45.35, 'unité', '2026-02-28 21:06:09', 29),
(20662, 'temp', 93.8, 'unité', '2026-02-28 21:06:14', 28),
(20663, 'temp', 77.36, 'unité', '2026-02-28 21:06:14', 29),
(20664, 'temp', 86.32, 'unité', '2026-02-28 21:06:19', 28),
(20665, 'temp', 5.39, 'unité', '2026-02-28 21:06:19', 29),
(20666, 'temp', 51.86, 'unité', '2026-02-28 21:06:24', 28),
(20667, 'temp', 20.28, 'unité', '2026-02-28 21:06:24', 29),
(20668, 'temp', 80.91, 'unité', '2026-02-28 21:06:29', 28),
(20669, 'temp', 79.38, 'unité', '2026-02-28 21:06:29', 29),
(20670, 'temp', 86.63, 'unité', '2026-02-28 21:06:35', 28),
(20671, 'temp', 92.85, 'unité', '2026-02-28 21:06:35', 29);
INSERT INTO `releve_terrain` (`id_releve`, `type_mesure`, `valeur_mesuree`, `unite`, `date_heure`, `id_capteur`) VALUES
(20672, 'temp', 18.84, 'unité', '2026-02-28 21:06:40', 28),
(20673, 'temp', 31.67, 'unité', '2026-02-28 21:06:40', 29),
(20674, 'temp', 76.93, 'unité', '2026-02-28 21:06:45', 28),
(20675, 'temp', 12.76, 'unité', '2026-02-28 21:06:45', 29),
(20676, 'temp', 85.89, 'unité', '2026-02-28 21:06:50', 28),
(20677, 'temp', 73.71, 'unité', '2026-02-28 21:06:50', 29),
(20678, 'temp', 97.24, 'unité', '2026-02-28 21:06:55', 28),
(20679, 'temp', 31.25, 'unité', '2026-02-28 21:06:55', 29),
(20680, 'temp', 82.48, 'unité', '2026-02-28 21:07:00', 28),
(20681, 'temp', 86.57, 'unité', '2026-02-28 21:07:00', 29),
(20682, 'temp', 4.91, 'unité', '2026-02-28 21:07:05', 28),
(20683, 'temp', 9.22, 'unité', '2026-02-28 21:07:05', 29),
(20684, 'temp', 49.62, 'unité', '2026-02-28 21:07:11', 28),
(20685, 'temp', 78.51, 'unité', '2026-02-28 21:07:11', 29),
(20686, 'temp', 46.91, 'unité', '2026-02-28 21:07:16', 28),
(20687, 'temp', 59.81, 'unité', '2026-02-28 21:07:16', 29),
(20688, 'temp', 91.93, 'unité', '2026-02-28 21:07:21', 28),
(20689, 'temp', 25.82, 'unité', '2026-02-28 21:07:21', 29),
(20690, 'temp', 66.06, 'unité', '2026-02-28 21:07:26', 28),
(20691, 'temp', 77.21, 'unité', '2026-02-28 21:07:26', 29),
(20692, 'temp', 93.02, 'unité', '2026-02-28 21:07:31', 28),
(20693, 'temp', 18.07, 'unité', '2026-02-28 21:07:31', 29),
(20694, 'temp', 67.47, 'unité', '2026-02-28 21:07:36', 28),
(20695, 'temp', 74.79, 'unité', '2026-02-28 21:07:36', 29),
(20696, 'temp', 74.53, 'unité', '2026-02-28 21:07:41', 28),
(20697, 'temp', 75.61, 'unité', '2026-02-28 21:07:41', 29),
(20698, 'temp', 71.77, 'unité', '2026-02-28 21:07:46', 28),
(20699, 'temp', 28.83, 'unité', '2026-02-28 21:07:46', 29),
(20700, 'temp', 61.18, 'unité', '2026-02-28 21:07:52', 28),
(20701, 'temp', 14.43, 'unité', '2026-02-28 21:07:52', 29),
(20702, 'temp', 57.36, 'unité', '2026-02-28 21:07:57', 28),
(20703, 'temp', 64.22, 'unité', '2026-02-28 21:07:57', 29),
(20704, 'temp', 54.22, 'unité', '2026-02-28 21:08:02', 28),
(20705, 'temp', 12.92, 'unité', '2026-02-28 21:08:02', 29),
(20706, 'temp', 42.98, 'unité', '2026-02-28 21:08:07', 28),
(20707, 'temp', 82.21, 'unité', '2026-02-28 21:08:07', 29),
(20708, 'temp', 18.06, 'unité', '2026-02-28 21:08:12', 28),
(20709, 'temp', 69.26, 'unité', '2026-02-28 21:08:12', 29),
(20710, 'temp', 56.01, 'unité', '2026-02-28 21:08:17', 28),
(20711, 'temp', 1.11, 'unité', '2026-02-28 21:08:17', 29),
(20712, 'temp', 30.28, 'unité', '2026-02-28 21:08:22', 28),
(20713, 'temp', 28.81, 'unité', '2026-02-28 21:08:23', 29),
(20714, 'temp', 80.67, 'unité', '2026-02-28 21:08:28', 28),
(20715, 'temp', 7.63, 'unité', '2026-02-28 21:08:28', 29),
(20716, 'temp', 53.34, 'unité', '2026-02-28 21:08:33', 28),
(20717, 'temp', 17.85, 'unité', '2026-02-28 21:08:33', 29),
(20718, 'temp', 84.1, 'unité', '2026-02-28 21:08:38', 28),
(20719, 'temp', 11.15, 'unité', '2026-02-28 21:08:38', 29),
(20720, 'temp', 62.08, 'unité', '2026-02-28 21:08:43', 28),
(20721, 'temp', 38.42, 'unité', '2026-02-28 21:08:43', 29),
(20722, 'temp', 33.65, 'unité', '2026-02-28 21:08:48', 28),
(20723, 'temp', 8.25, 'unité', '2026-02-28 21:08:48', 29),
(20724, 'temp', 21.01, 'unité', '2026-02-28 21:08:53', 28),
(20725, 'temp', 70.84, 'unité', '2026-02-28 21:08:53', 29),
(20726, 'temp', 85.41, 'unité', '2026-02-28 21:08:58', 28),
(20727, 'temp', 12.09, 'unité', '2026-02-28 21:08:58', 29),
(20728, 'temp', 20.13, 'unité', '2026-02-28 21:09:03', 28),
(20729, 'temp', 34.39, 'unité', '2026-02-28 21:09:03', 29),
(20730, 'temp', 70.32, 'unité', '2026-02-28 21:09:09', 28),
(20731, 'temp', 31.73, 'unité', '2026-02-28 21:09:09', 29),
(20732, 'temp', 98.83, 'unité', '2026-02-28 21:09:14', 28),
(20733, 'temp', 19.24, 'unité', '2026-02-28 21:09:14', 29),
(20734, 'temp', 77.67, 'unité', '2026-02-28 21:09:19', 28),
(20735, 'temp', 1.36, 'unité', '2026-02-28 21:09:19', 29),
(20736, 'temp', 90.77, 'unité', '2026-02-28 21:09:24', 28),
(20737, 'temp', 20.79, 'unité', '2026-02-28 21:09:24', 29),
(20738, 'temp', 0.12, 'unité', '2026-02-28 21:09:29', 28),
(20739, 'temp', 68.06, 'unité', '2026-02-28 21:09:29', 29),
(20740, 'temp', 63.89, 'unité', '2026-02-28 21:09:34', 28),
(20741, 'temp', 50.13, 'unité', '2026-02-28 21:09:34', 29),
(20742, 'temp', 6.3, 'unité', '2026-02-28 21:09:39', 28),
(20743, 'temp', 57.48, 'unité', '2026-02-28 21:09:39', 29),
(20744, 'temp', 24.85, 'unité', '2026-02-28 21:09:45', 28),
(20745, 'temp', 60.18, 'unité', '2026-02-28 21:09:45', 29),
(20746, 'temp', 66.68, 'unité', '2026-02-28 21:09:50', 28),
(20747, 'temp', 65.05, 'unité', '2026-02-28 21:09:50', 29),
(20748, 'temp', 62.36, 'unité', '2026-02-28 21:09:55', 28),
(20749, 'temp', 38.47, 'unité', '2026-02-28 21:09:55', 29),
(20750, 'temp', 33.54, 'unité', '2026-02-28 21:10:00', 28),
(20751, 'temp', 34.18, 'unité', '2026-02-28 21:10:00', 29),
(20752, 'temp', 81.61, 'unité', '2026-02-28 21:10:05', 28),
(20753, 'temp', 56.25, 'unité', '2026-02-28 21:10:05', 29),
(20754, 'temp', 57.05, 'unité', '2026-02-28 21:10:10', 28),
(20755, 'temp', 4.77, 'unité', '2026-02-28 21:10:10', 29),
(20756, 'temp', 44.29, 'unité', '2026-02-28 21:10:15', 28),
(20757, 'temp', 82.79, 'unité', '2026-02-28 21:10:15', 29),
(20758, 'temp', 30.61, 'unité', '2026-02-28 21:10:21', 28),
(20759, 'temp', 81.18, 'unité', '2026-02-28 21:10:21', 29),
(20760, 'temp', 51.3, 'unité', '2026-02-28 21:10:26', 28),
(20761, 'temp', 28.02, 'unité', '2026-02-28 21:10:26', 29),
(20762, 'temp', 36.54, 'unité', '2026-02-28 21:10:31', 28),
(20763, 'temp', 0.74, 'unité', '2026-02-28 21:10:31', 29),
(20764, 'temp', 26.52, 'unité', '2026-02-28 21:10:36', 28),
(20765, 'temp', 96.9, 'unité', '2026-02-28 21:10:36', 29),
(20766, 'temp', 0.2, 'unité', '2026-02-28 21:10:41', 28),
(20767, 'temp', 78.52, 'unité', '2026-02-28 21:10:41', 29),
(20768, 'temp', 92.78, 'unité', '2026-02-28 21:10:46', 28),
(20769, 'temp', 17.93, 'unité', '2026-02-28 21:10:46', 29),
(20770, 'temp', 88.5, 'unité', '2026-02-28 21:10:51', 28),
(20771, 'temp', 39.89, 'unité', '2026-02-28 21:10:51', 29),
(20772, 'temp', 72.65, 'unité', '2026-02-28 21:10:56', 28),
(20773, 'temp', 73.25, 'unité', '2026-02-28 21:10:56', 29),
(20774, 'temp', 37.17, 'unité', '2026-02-28 21:11:02', 28),
(20775, 'temp', 79.8, 'unité', '2026-02-28 21:11:02', 29),
(20776, 'temp', 56.28, 'unité', '2026-02-28 21:11:07', 28),
(20777, 'temp', 35.36, 'unité', '2026-02-28 21:11:07', 29),
(20778, 'temp', 33.39, 'unité', '2026-02-28 21:11:12', 28),
(20779, 'temp', 60.38, 'unité', '2026-02-28 21:11:12', 29),
(20780, 'temp', 9.68, 'unité', '2026-02-28 21:11:17', 28),
(20781, 'temp', 72.05, 'unité', '2026-02-28 21:11:17', 29),
(20782, 'temp', 70.06, 'unité', '2026-02-28 21:11:22', 28),
(20783, 'temp', 90.42, 'unité', '2026-02-28 21:11:22', 29),
(20784, 'temp', 42.4, 'unité', '2026-02-28 21:11:27', 28),
(20785, 'temp', 57.06, 'unité', '2026-02-28 21:11:27', 29),
(20786, 'temp', 10.56, 'unité', '2026-02-28 21:11:32', 28),
(20787, 'temp', 12.83, 'unité', '2026-02-28 21:11:32', 29),
(20788, 'temp', 96.87, 'unité', '2026-02-28 21:11:38', 28),
(20789, 'temp', 69.86, 'unité', '2026-02-28 21:11:38', 29),
(20790, 'temp', 72.68, 'unité', '2026-02-28 21:11:43', 28),
(20791, 'temp', 4.84, 'unité', '2026-02-28 21:11:43', 29),
(20792, 'temp', 29.7, 'unité', '2026-02-28 21:11:48', 28),
(20793, 'temp', 91.79, 'unité', '2026-02-28 21:11:48', 29),
(20794, 'temp', 72.73, 'unité', '2026-02-28 21:11:53', 28),
(20795, 'temp', 73.18, 'unité', '2026-02-28 21:11:53', 29),
(20796, 'temp', 98.57, 'unité', '2026-02-28 21:11:58', 28),
(20797, 'temp', 50.24, 'unité', '2026-02-28 21:11:58', 29),
(20798, 'temp', 47.68, 'unité', '2026-02-28 21:12:03', 28),
(20799, 'temp', 90.56, 'unité', '2026-02-28 21:12:03', 29),
(20800, 'temp', 80.36, 'unité', '2026-02-28 21:12:08', 28),
(20801, 'temp', 19.19, 'unité', '2026-02-28 21:12:08', 29),
(20802, 'temp', 72.19, 'unité', '2026-02-28 21:12:13', 28),
(20803, 'temp', 61.24, 'unité', '2026-02-28 21:12:13', 29),
(20804, 'temp', 86.21, 'unité', '2026-02-28 21:12:19', 28),
(20805, 'temp', 58.84, 'unité', '2026-02-28 21:12:19', 29),
(20806, 'temp', 30.34, 'unité', '2026-02-28 21:12:24', 28),
(20807, 'temp', 99.56, 'unité', '2026-02-28 21:12:24', 29),
(20808, 'temp', 34.31, 'unité', '2026-02-28 21:12:29', 28),
(20809, 'temp', 37.85, 'unité', '2026-02-28 21:12:29', 29),
(20810, 'temp', 21.87, 'unité', '2026-02-28 21:12:34', 28),
(20811, 'temp', 19.86, 'unité', '2026-02-28 21:12:34', 29),
(20812, 'temp', 50.26, 'unité', '2026-02-28 21:12:39', 28),
(20813, 'temp', 4.67, 'unité', '2026-02-28 21:12:39', 29),
(20814, 'temp', 92.39, 'unité', '2026-02-28 21:12:44', 28),
(20815, 'temp', 49.43, 'unité', '2026-02-28 21:12:44', 29),
(20816, 'temp', 1.26, 'unité', '2026-02-28 21:12:50', 28),
(20817, 'temp', 10.54, 'unité', '2026-02-28 21:12:50', 29),
(20818, 'temp', 52.31, 'unité', '2026-02-28 21:12:55', 28),
(20819, 'temp', 7.92, 'unité', '2026-02-28 21:12:55', 29),
(20820, 'temp', 93.02, 'unité', '2026-02-28 21:13:00', 28),
(20821, 'temp', 29.61, 'unité', '2026-02-28 21:13:00', 29),
(20822, 'temp', 4.87, 'unité', '2026-02-28 21:13:05', 28),
(20823, 'temp', 47.07, 'unité', '2026-02-28 21:13:05', 29),
(20824, 'temp', 75.28, 'unité', '2026-02-28 21:13:10', 28),
(20825, 'temp', 69.49, 'unité', '2026-02-28 21:13:10', 29),
(20826, 'temp', 43.82, 'unité', '2026-02-28 21:13:15', 28),
(20827, 'temp', 21.86, 'unité', '2026-02-28 21:13:15', 29),
(20828, 'temp', 17.83, 'unité', '2026-02-28 21:13:20', 28),
(20829, 'temp', 53.81, 'unité', '2026-02-28 21:13:20', 29),
(20830, 'temp', 90.94, 'unité', '2026-02-28 21:13:26', 28),
(20831, 'temp', 34.15, 'unité', '2026-02-28 21:13:26', 29),
(20832, 'temp', 22.55, 'unité', '2026-02-28 21:13:31', 28),
(20833, 'temp', 25.45, 'unité', '2026-02-28 21:13:31', 29),
(20834, 'temp', 58.85, 'unité', '2026-02-28 21:13:36', 28),
(20835, 'temp', 79.17, 'unité', '2026-02-28 21:13:36', 29),
(20836, 'temp', 99.13, 'unité', '2026-02-28 21:13:41', 28),
(20837, 'temp', 43.78, 'unité', '2026-02-28 21:13:41', 29),
(20838, 'temp', 91.65, 'unité', '2026-02-28 21:13:46', 28),
(20839, 'temp', 82.64, 'unité', '2026-02-28 21:13:46', 29),
(20840, 'temp', 26.15, 'unité', '2026-02-28 21:13:51', 28),
(20841, 'temp', 91.78, 'unité', '2026-02-28 21:13:51', 29),
(20842, 'temp', 9.15, 'unité', '2026-02-28 21:13:56', 28),
(20843, 'temp', 89.97, 'unité', '2026-02-28 21:13:56', 29),
(20844, 'temp', 92.51, 'unité', '2026-02-28 21:14:02', 28),
(20845, 'temp', 10.72, 'unité', '2026-02-28 21:14:02', 29),
(20846, 'temp', 33.43, 'unité', '2026-02-28 21:14:07', 28),
(20847, 'temp', 67.51, 'unité', '2026-02-28 21:14:07', 29),
(20848, 'temp', 6.09, 'unité', '2026-02-28 21:14:12', 28),
(20849, 'temp', 95.43, 'unité', '2026-02-28 21:14:12', 29),
(20850, 'temp', 68.96, 'unité', '2026-02-28 21:14:17', 28),
(20851, 'temp', 8.45, 'unité', '2026-02-28 21:14:17', 29),
(20852, 'temp', 9.77, 'unité', '2026-02-28 21:14:22', 28),
(20853, 'temp', 34.42, 'unité', '2026-02-28 21:14:22', 29),
(20854, 'temp', 80.7, 'unité', '2026-02-28 21:14:27', 28),
(20855, 'temp', 84.82, 'unité', '2026-02-28 21:14:27', 29),
(20856, 'temp', 88.22, 'unité', '2026-02-28 21:14:33', 28),
(20857, 'temp', 57.33, 'unité', '2026-02-28 21:14:33', 29),
(20858, 'temp', 81.98, 'unité', '2026-02-28 21:14:38', 28),
(20859, 'temp', 94.16, 'unité', '2026-02-28 21:14:38', 29),
(20860, 'temp', 30.46, 'unité', '2026-02-28 21:14:43', 28),
(20861, 'temp', 63.42, 'unité', '2026-02-28 21:14:43', 29),
(20862, 'temp', 85.61, 'unité', '2026-02-28 21:14:48', 28),
(20863, 'temp', 98.76, 'unité', '2026-02-28 21:14:48', 29),
(20864, 'temp', 86.53, 'unité', '2026-02-28 21:14:53', 28),
(20865, 'temp', 39.91, 'unité', '2026-02-28 21:14:53', 29),
(20866, 'temp', 79.85, 'unité', '2026-02-28 21:14:58', 28),
(20867, 'temp', 5.25, 'unité', '2026-02-28 21:14:58', 29),
(20868, 'temp', 43.78, 'unité', '2026-02-28 21:15:04', 28),
(20869, 'temp', 52.51, 'unité', '2026-02-28 21:15:04', 29),
(20870, 'temp', 59.44, 'unité', '2026-02-28 21:15:09', 28),
(20871, 'temp', 14.25, 'unité', '2026-02-28 21:15:09', 29),
(20872, 'temp', 23.94, 'unité', '2026-02-28 21:15:14', 28),
(20873, 'temp', 62.08, 'unité', '2026-02-28 21:15:14', 29),
(20874, 'temp', 60.58, 'unité', '2026-02-28 21:15:19', 28),
(20875, 'temp', 80.77, 'unité', '2026-02-28 21:15:19', 29),
(20876, 'temp', 64.9, 'unité', '2026-02-28 21:15:24', 28),
(20877, 'temp', 10.43, 'unité', '2026-02-28 21:15:24', 29),
(20878, 'temp', 2.16, 'unité', '2026-02-28 21:15:29', 28),
(20879, 'temp', 20.08, 'unité', '2026-02-28 21:15:29', 29),
(20880, 'temp', 52.47, 'unité', '2026-02-28 21:15:34', 28),
(20881, 'temp', 12.25, 'unité', '2026-02-28 21:15:34', 29),
(20882, 'temp', 94.66, 'unité', '2026-02-28 21:15:39', 28),
(20883, 'temp', 87.62, 'unité', '2026-02-28 21:15:39', 29),
(20884, 'temp', 84.89, 'unité', '2026-02-28 21:15:45', 28),
(20885, 'temp', 92.71, 'unité', '2026-02-28 21:15:45', 29),
(20886, 'temp', 93.51, 'unité', '2026-02-28 21:15:50', 28),
(20887, 'temp', 30.86, 'unité', '2026-02-28 21:15:50', 29),
(20888, 'temp', 87.91, 'unité', '2026-02-28 21:15:55', 28),
(20889, 'temp', 4.52, 'unité', '2026-02-28 21:15:55', 29),
(20890, 'temp', 47.52, 'unité', '2026-02-28 21:16:00', 28),
(20891, 'temp', 49.31, 'unité', '2026-02-28 21:16:00', 29),
(20892, 'temp', 12.5, 'unité', '2026-02-28 21:16:05', 28),
(20893, 'temp', 98.02, 'unité', '2026-02-28 21:16:05', 29),
(20894, 'temp', 48.08, 'unité', '2026-02-28 21:16:10', 28),
(20895, 'temp', 30.51, 'unité', '2026-02-28 21:16:10', 29),
(20896, 'temp', 48.42, 'unité', '2026-02-28 21:16:15', 28),
(20897, 'temp', 72.13, 'unité', '2026-02-28 21:16:15', 29),
(20898, 'temp', 56.9, 'unité', '2026-02-28 21:16:20', 28),
(20899, 'temp', 4.41, 'unité', '2026-02-28 21:16:20', 29),
(20900, 'temp', 43.18, 'unité', '2026-02-28 21:16:26', 28),
(20901, 'temp', 80.99, 'unité', '2026-02-28 21:16:26', 29),
(20902, 'temp', 33.11, 'unité', '2026-02-28 21:16:31', 28),
(20903, 'temp', 87.06, 'unité', '2026-02-28 21:16:31', 29),
(20904, 'temp', 9.17, 'unité', '2026-02-28 21:16:36', 28),
(20905, 'temp', 8.68, 'unité', '2026-02-28 21:16:36', 29),
(20906, 'temp', 26.86, 'unité', '2026-02-28 21:16:41', 28),
(20907, 'temp', 96.34, 'unité', '2026-02-28 21:16:41', 29),
(20908, 'temp', 69.07, 'unité', '2026-02-28 21:16:46', 28),
(20909, 'temp', 67.1, 'unité', '2026-02-28 21:16:46', 29),
(20910, 'temp', 81.86, 'unité', '2026-02-28 21:16:51', 28),
(20911, 'temp', 16.31, 'unité', '2026-02-28 21:16:51', 29),
(20912, 'temp', 99.37, 'unité', '2026-02-28 21:16:56', 28),
(20913, 'temp', 73.45, 'unité', '2026-02-28 21:16:56', 29),
(20914, 'temp', 40.43, 'unité', '2026-02-28 21:17:01', 28),
(20915, 'temp', 5.45, 'unité', '2026-02-28 21:17:01', 29),
(20916, 'temp', 92.75, 'unité', '2026-02-28 21:17:07', 28),
(20917, 'temp', 40.45, 'unité', '2026-02-28 21:17:07', 29),
(20918, 'temp', 45.56, 'unité', '2026-02-28 21:17:12', 28),
(20919, 'temp', 0.21, 'unité', '2026-02-28 21:17:12', 29),
(20920, 'temp', 50.9, 'unité', '2026-02-28 21:17:17', 28),
(20921, 'temp', 44.21, 'unité', '2026-02-28 21:17:17', 29),
(20922, 'temp', 59.21, 'unité', '2026-02-28 21:17:22', 28),
(20923, 'temp', 66.48, 'unité', '2026-02-28 21:17:22', 29),
(20924, 'temp', 46.31, 'unité', '2026-02-28 21:17:27', 28),
(20925, 'temp', 28.06, 'unité', '2026-02-28 21:17:27', 29),
(20926, 'temp', 79.44, 'unité', '2026-02-28 21:17:32', 28),
(20927, 'temp', 59.29, 'unité', '2026-02-28 21:17:33', 29),
(20928, 'temp', 4.55, 'unité', '2026-02-28 21:17:38', 28),
(20929, 'temp', 95.01, 'unité', '2026-02-28 21:17:38', 29),
(20930, 'temp', 40.96, 'unité', '2026-02-28 21:17:43', 28),
(20931, 'temp', 98.1, 'unité', '2026-02-28 21:17:43', 29),
(20932, 'temp', 39.94, 'unité', '2026-02-28 21:17:48', 28),
(20933, 'temp', 37.25, 'unité', '2026-02-28 21:17:48', 29),
(20934, 'temp', 80.36, 'unité', '2026-02-28 21:17:53', 28),
(20935, 'temp', 97.87, 'unité', '2026-02-28 21:17:53', 29),
(20936, 'temp', 84.2, 'unité', '2026-02-28 21:17:58', 28),
(20937, 'temp', 41.66, 'unité', '2026-02-28 21:17:58', 29),
(20938, 'temp', 50.68, 'unité', '2026-02-28 21:18:03', 28),
(20939, 'temp', 12.15, 'unité', '2026-02-28 21:18:03', 29),
(20940, 'temp', 90.34, 'unité', '2026-02-28 21:18:08', 28),
(20941, 'temp', 52.35, 'unité', '2026-02-28 21:18:09', 29),
(20942, 'temp', 97.75, 'unité', '2026-02-28 21:18:14', 28),
(20943, 'temp', 26.4, 'unité', '2026-02-28 21:18:14', 29),
(20944, 'temp', 68.99, 'unité', '2026-02-28 21:18:19', 28),
(20945, 'temp', 43.5, 'unité', '2026-02-28 21:18:19', 29),
(20946, 'temp', 72.7, 'unité', '2026-02-28 21:18:24', 28),
(20947, 'temp', 85.54, 'unité', '2026-02-28 21:18:24', 29),
(20948, 'temp', 74.64, 'unité', '2026-02-28 21:18:29', 28),
(20949, 'temp', 13.74, 'unité', '2026-02-28 21:18:29', 29),
(20950, 'temp', 93.73, 'unité', '2026-02-28 21:18:34', 28),
(20951, 'temp', 47.71, 'unité', '2026-02-28 21:18:34', 29),
(20952, 'temp', 41.35, 'unité', '2026-02-28 21:18:39', 28),
(20953, 'temp', 1.33, 'unité', '2026-02-28 21:18:39', 29),
(20954, 'temp', 13.23, 'unité', '2026-02-28 21:18:45', 28),
(20955, 'temp', 22.61, 'unité', '2026-02-28 21:18:45', 29),
(20956, 'temp', 60.14, 'unité', '2026-02-28 21:18:50', 28),
(20957, 'temp', 44.04, 'unité', '2026-02-28 21:18:50', 29),
(20958, 'temp', 77.58, 'unité', '2026-02-28 21:18:55', 28),
(20959, 'temp', 88.72, 'unité', '2026-02-28 21:18:55', 29),
(20960, 'temp', 69.48, 'unité', '2026-02-28 21:19:00', 28),
(20961, 'temp', 13.68, 'unité', '2026-02-28 21:19:00', 29),
(20962, 'temp', 15.79, 'unité', '2026-02-28 21:19:05', 28),
(20963, 'temp', 18.15, 'unité', '2026-02-28 21:19:05', 29),
(20964, 'temp', 60.87, 'unité', '2026-02-28 21:19:10', 28),
(20965, 'temp', 27, 'unité', '2026-02-28 21:19:10', 29),
(20966, 'temp', 91.71, 'unité', '2026-02-28 21:19:15', 28),
(20967, 'temp', 98.64, 'unité', '2026-02-28 21:19:15', 29),
(20968, 'temp', 71.19, 'unité', '2026-02-28 21:19:20', 28),
(20969, 'temp', 68.89, 'unité', '2026-02-28 21:19:20', 29),
(20970, 'temp', 41.91, 'unité', '2026-02-28 21:19:26', 28),
(20971, 'temp', 61.3, 'unité', '2026-02-28 21:19:26', 29),
(20972, 'temp', 83.38, 'unité', '2026-02-28 21:19:31', 28),
(20973, 'temp', 40.32, 'unité', '2026-02-28 21:19:31', 29),
(20974, 'temp', 98.67, 'unité', '2026-02-28 21:19:36', 28),
(20975, 'temp', 70.55, 'unité', '2026-02-28 21:19:36', 29),
(20976, 'temp', 27.42, 'unité', '2026-02-28 21:19:41', 28),
(20977, 'temp', 79.55, 'unité', '2026-02-28 21:19:41', 29),
(20978, 'temp', 1.19, 'unité', '2026-02-28 21:19:46', 28),
(20979, 'temp', 36.23, 'unité', '2026-02-28 21:19:46', 29),
(20980, 'temp', 24.61, 'unité', '2026-02-28 21:19:51', 28),
(20981, 'temp', 93.01, 'unité', '2026-02-28 21:19:51', 29),
(20982, 'temp', 8.38, 'unité', '2026-02-28 21:19:56', 28),
(20983, 'temp', 81.55, 'unité', '2026-02-28 21:19:56', 29),
(20984, 'temp', 9.74, 'unité', '2026-02-28 21:20:01', 28),
(20985, 'temp', 60.83, 'unité', '2026-02-28 21:20:01', 29),
(20986, 'temp', 13.66, 'unité', '2026-02-28 21:20:06', 28),
(20987, 'temp', 65.73, 'unité', '2026-02-28 21:20:06', 29),
(20988, 'temp', 22.79, 'unité', '2026-02-28 21:20:12', 28),
(20989, 'temp', 17.13, 'unité', '2026-02-28 21:20:12', 29),
(20990, 'temp', 70.64, 'unité', '2026-02-28 21:20:17', 28),
(20991, 'temp', 57.71, 'unité', '2026-02-28 21:20:17', 29),
(20992, 'temp', 38.59, 'unité', '2026-02-28 21:20:22', 28),
(20993, 'temp', 41.35, 'unité', '2026-02-28 21:20:22', 29),
(20994, 'temp', 76.48, 'unité', '2026-02-28 21:20:27', 28),
(20995, 'temp', 81.9, 'unité', '2026-02-28 21:20:27', 29),
(20996, 'temp', 5.03, 'unité', '2026-02-28 21:20:32', 28),
(20997, 'temp', 33.2, 'unité', '2026-02-28 21:20:32', 29),
(20998, 'temp', 60.96, 'unité', '2026-02-28 21:20:37', 28),
(20999, 'temp', 65.7, 'unité', '2026-02-28 21:20:37', 29),
(21000, 'temp', 16.98, 'unité', '2026-02-28 21:20:42', 28),
(21001, 'temp', 54.59, 'unité', '2026-02-28 21:20:42', 29),
(21002, 'temp', 53.31, 'unité', '2026-02-28 21:20:48', 28),
(21003, 'temp', 62.45, 'unité', '2026-02-28 21:20:48', 29),
(21004, 'temp', 98.74, 'unité', '2026-02-28 21:20:53', 28),
(21005, 'temp', 56.69, 'unité', '2026-02-28 21:20:53', 29),
(21006, 'temp', 98.73, 'unité', '2026-02-28 21:20:58', 28),
(21007, 'temp', 65.76, 'unité', '2026-02-28 21:20:58', 29),
(21008, 'temp', 0.47, 'unité', '2026-02-28 21:21:03', 28),
(21009, 'temp', 64.72, 'unité', '2026-02-28 21:21:03', 29),
(21010, 'temp', 50, 'unité', '2026-02-28 21:21:08', 28),
(21011, 'temp', 89.93, 'unité', '2026-02-28 21:21:08', 29),
(21012, 'temp', 26.88, 'unité', '2026-02-28 21:21:13', 28),
(21013, 'temp', 76.4, 'unité', '2026-02-28 21:21:13', 29),
(21014, 'temp', 25.36, 'unité', '2026-02-28 21:21:18', 28),
(21015, 'temp', 0.7, 'unité', '2026-02-28 21:21:18', 29),
(21016, 'temp', 68.12, 'unité', '2026-02-28 21:21:23', 28),
(21017, 'temp', 12.87, 'unité', '2026-02-28 21:21:23', 29),
(21018, 'temp', 13.76, 'unité', '2026-02-28 21:21:28', 28),
(21019, 'temp', 5.73, 'unité', '2026-02-28 21:21:28', 29),
(21020, 'temp', 87.28, 'unité', '2026-02-28 21:21:34', 28),
(21021, 'temp', 77.24, 'unité', '2026-02-28 21:21:34', 29),
(21022, 'temp', 74.71, 'unité', '2026-02-28 21:21:39', 28),
(21023, 'temp', 39.01, 'unité', '2026-02-28 21:21:39', 29),
(21024, 'temp', 46.75, 'unité', '2026-02-28 21:21:44', 28),
(21025, 'temp', 70.23, 'unité', '2026-02-28 21:21:44', 29),
(21026, 'temp', 33.2, 'unité', '2026-02-28 21:21:49', 28),
(21027, 'temp', 36.87, 'unité', '2026-02-28 21:21:49', 29),
(21028, 'temp', 63.92, 'unité', '2026-02-28 21:21:54', 28),
(21029, 'temp', 8.53, 'unité', '2026-02-28 21:21:54', 29),
(21030, 'temp', 60.23, 'unité', '2026-02-28 21:21:59', 28),
(21031, 'temp', 12.84, 'unité', '2026-02-28 21:21:59', 29),
(21032, 'temp', 14.36, 'unité', '2026-02-28 21:22:04', 28),
(21033, 'temp', 60.26, 'unité', '2026-02-28 21:22:04', 29),
(21034, 'temp', 20.66, 'unité', '2026-02-28 21:22:10', 28),
(21035, 'temp', 87.27, 'unité', '2026-02-28 21:22:10', 29),
(21036, 'temp', 71.55, 'unité', '2026-02-28 21:22:15', 28),
(21037, 'temp', 31.19, 'unité', '2026-02-28 21:22:15', 29),
(21038, 'temp', 14.4, 'unité', '2026-02-28 21:22:20', 28),
(21039, 'temp', 64.64, 'unité', '2026-02-28 21:22:20', 29),
(21040, 'temp', 86.68, 'unité', '2026-02-28 21:22:25', 28),
(21041, 'temp', 59.44, 'unité', '2026-02-28 21:22:25', 29),
(21042, 'temp', 57.29, 'unité', '2026-02-28 21:22:30', 28),
(21043, 'temp', 71.48, 'unité', '2026-02-28 21:22:30', 29),
(21044, 'temp', 77.51, 'unité', '2026-02-28 21:22:35', 28),
(21045, 'temp', 41.15, 'unité', '2026-02-28 21:22:35', 29),
(21046, 'temp', 30.98, 'unité', '2026-02-28 21:22:40', 28),
(21047, 'temp', 11.7, 'unité', '2026-02-28 21:22:40', 29),
(21048, 'temp', 58.52, 'unité', '2026-02-28 21:22:46', 28),
(21049, 'temp', 44.98, 'unité', '2026-02-28 21:22:46', 29),
(21050, 'temp', 75.83, 'unité', '2026-02-28 21:22:51', 28),
(21051, 'temp', 0.81, 'unité', '2026-02-28 21:22:51', 29),
(21052, 'temp', 65.03, 'unité', '2026-02-28 21:22:56', 28),
(21053, 'temp', 46.36, 'unité', '2026-02-28 21:22:56', 29),
(21054, 'temp', 78.95, 'unité', '2026-02-28 21:23:01', 28),
(21055, 'temp', 78.23, 'unité', '2026-02-28 21:23:01', 29),
(21056, 'temp', 56.87, 'unité', '2026-02-28 21:23:06', 28),
(21057, 'temp', 86.75, 'unité', '2026-02-28 21:23:06', 29),
(21058, 'temp', 13.03, 'unité', '2026-02-28 21:23:11', 28),
(21059, 'temp', 85.56, 'unité', '2026-02-28 21:23:11', 29),
(21060, 'temp', 75.2, 'unité', '2026-02-28 21:23:16', 28),
(21061, 'temp', 57.29, 'unité', '2026-02-28 21:23:16', 29),
(21062, 'temp', 84.79, 'unité', '2026-02-28 21:23:21', 28),
(21063, 'temp', 31.29, 'unité', '2026-02-28 21:23:21', 29),
(21064, 'temp', 25.19, 'unité', '2026-02-28 21:23:27', 28),
(21065, 'temp', 77.62, 'unité', '2026-02-28 21:23:27', 29),
(21066, 'temp', 63.84, 'unité', '2026-02-28 21:23:32', 28),
(21067, 'temp', 2.69, 'unité', '2026-02-28 21:23:32', 29),
(21068, 'temp', 73.16, 'unité', '2026-02-28 21:23:37', 28),
(21069, 'temp', 27.38, 'unité', '2026-02-28 21:23:37', 29),
(21070, 'temp', 74.7, 'unité', '2026-02-28 21:23:42', 28),
(21071, 'temp', 27.39, 'unité', '2026-02-28 21:23:42', 29),
(21072, 'temp', 92.3, 'unité', '2026-02-28 21:23:47', 28),
(21073, 'temp', 35.01, 'unité', '2026-02-28 21:23:47', 29),
(21074, 'temp', 97.85, 'unité', '2026-02-28 21:23:52', 28),
(21075, 'temp', 66.46, 'unité', '2026-02-28 21:23:52', 29),
(21076, 'temp', 36.1, 'unité', '2026-02-28 21:23:57', 28),
(21077, 'temp', 56.55, 'unité', '2026-02-28 21:23:57', 29),
(21078, 'temp', 58.99, 'unité', '2026-02-28 21:24:02', 28),
(21079, 'temp', 53.26, 'unité', '2026-02-28 21:24:03', 29),
(21080, 'temp', 0.08, 'unité', '2026-02-28 21:24:08', 28),
(21081, 'temp', 58.07, 'unité', '2026-02-28 21:24:08', 29),
(21082, 'temp', 33.95, 'unité', '2026-02-28 21:24:13', 28),
(21083, 'temp', 20.03, 'unité', '2026-02-28 21:24:13', 29),
(21084, 'temp', 45.52, 'unité', '2026-02-28 21:24:18', 28),
(21085, 'temp', 78.63, 'unité', '2026-02-28 21:24:18', 29),
(21086, 'temp', 94.54, 'unité', '2026-02-28 21:24:23', 28),
(21087, 'temp', 69.77, 'unité', '2026-02-28 21:24:23', 29),
(21088, 'temp', 21.49, 'unité', '2026-02-28 21:24:28', 28),
(21089, 'temp', 65.21, 'unité', '2026-02-28 21:24:28', 29),
(21090, 'temp', 96.62, 'unité', '2026-02-28 21:24:33', 28),
(21091, 'temp', 41.78, 'unité', '2026-02-28 21:24:33', 29),
(21092, 'temp', 34.4, 'unité', '2026-02-28 21:24:39', 28),
(21093, 'temp', 16.3, 'unité', '2026-02-28 21:24:39', 29),
(21094, 'temp', 92.61, 'unité', '2026-02-28 21:24:44', 28),
(21095, 'temp', 61.12, 'unité', '2026-02-28 21:24:44', 29),
(21096, 'temp', 32.54, 'unité', '2026-02-28 21:24:49', 28),
(21097, 'temp', 91.46, 'unité', '2026-02-28 21:24:49', 29),
(21098, 'temp', 59.03, 'unité', '2026-02-28 21:24:54', 28),
(21099, 'temp', 1.86, 'unité', '2026-02-28 21:24:54', 29),
(21100, 'temp', 33.78, 'unité', '2026-02-28 21:24:59', 28),
(21101, 'temp', 34.05, 'unité', '2026-02-28 21:24:59', 29),
(21102, 'temp', 85.9, 'unité', '2026-02-28 21:25:04', 28),
(21103, 'temp', 96.37, 'unité', '2026-02-28 21:25:04', 29),
(21104, 'temp', 94.14, 'unité', '2026-02-28 21:25:09', 28),
(21105, 'temp', 62.04, 'unité', '2026-02-28 21:25:09', 29),
(21106, 'temp', 52.98, 'unité', '2026-02-28 21:25:15', 28),
(21107, 'temp', 54.98, 'unité', '2026-02-28 21:25:15', 29),
(21108, 'temp', 39.26, 'unité', '2026-02-28 21:25:20', 28),
(21109, 'temp', 26.5, 'unité', '2026-02-28 21:25:20', 29),
(21110, 'temp', 92.14, 'unité', '2026-02-28 21:25:25', 28),
(21111, 'temp', 21.6, 'unité', '2026-02-28 21:25:25', 29),
(21112, 'temp', 9.42, 'unité', '2026-02-28 21:25:30', 28),
(21113, 'temp', 26.52, 'unité', '2026-02-28 21:25:30', 29),
(21114, 'temp', 84.6, 'unité', '2026-02-28 21:25:35', 28),
(21115, 'temp', 59.83, 'unité', '2026-02-28 21:25:36', 29),
(21116, 'temp', 31.21, 'unité', '2026-02-28 21:25:42', 28),
(21117, 'temp', 98.08, 'unité', '2026-02-28 21:25:42', 29),
(21118, 'temp', 9.3, 'unité', '2026-02-28 21:25:48', 28),
(21119, 'temp', 45.85, 'unité', '2026-02-28 21:25:48', 29),
(21120, 'temp', 57.47, 'unité', '2026-02-28 21:25:53', 28),
(21121, 'temp', 44.76, 'unité', '2026-02-28 21:25:53', 29),
(21122, 'temp', 95.95, 'unité', '2026-02-28 21:25:59', 28),
(21123, 'temp', 56.96, 'unité', '2026-02-28 21:25:59', 29),
(21124, 'temp', 36.69, 'unité', '2026-02-28 21:26:04', 28),
(21125, 'temp', 67.4, 'unité', '2026-02-28 21:26:04', 29),
(21126, 'temp', 29.92, 'unité', '2026-02-28 21:26:09', 28),
(21127, 'temp', 58.73, 'unité', '2026-02-28 21:26:09', 29),
(21128, 'temp', 18.23, 'unité', '2026-02-28 21:26:14', 28),
(21129, 'temp', 63.68, 'unité', '2026-02-28 21:26:14', 29),
(21130, 'temp', 45.29, 'unité', '2026-02-28 21:26:19', 28),
(21131, 'temp', 98.39, 'unité', '2026-02-28 21:26:19', 29),
(21132, 'temp', 96.24, 'unité', '2026-02-28 21:26:25', 28),
(21133, 'temp', 8.74, 'unité', '2026-02-28 21:26:25', 29),
(21134, 'temp', 87.44, 'unité', '2026-02-28 21:26:30', 28),
(21135, 'temp', 6.21, 'unité', '2026-02-28 21:26:33', 29),
(21136, 'temp', 72.63, 'unité', '2026-02-28 21:26:41', 28),
(21137, 'temp', 21.75, 'unité', '2026-02-28 21:26:41', 29),
(21138, 'temp', 25.81, 'unité', '2026-02-28 21:26:47', 28),
(21139, 'temp', 47.18, 'unité', '2026-02-28 21:26:47', 29),
(21140, 'temp', 74.18, 'unité', '2026-02-28 21:26:52', 28),
(21141, 'temp', 51.84, 'unité', '2026-02-28 21:26:52', 29),
(21142, 'temp', 45.24, 'unité', '2026-02-28 21:26:57', 28),
(21143, 'temp', 74.83, 'unité', '2026-02-28 21:26:57', 29),
(21144, 'temp', 54.56, 'unité', '2026-02-28 21:27:02', 28),
(21145, 'temp', 75.98, 'unité', '2026-02-28 21:27:02', 29),
(21146, 'temp', 84.58, 'unité', '2026-02-28 21:27:07', 28),
(21147, 'temp', 89.12, 'unité', '2026-02-28 21:27:07', 29),
(21148, 'temp', 83.68, 'unité', '2026-02-28 21:27:13', 28),
(21149, 'temp', 21.47, 'unité', '2026-02-28 21:27:13', 29),
(21150, 'temp', 20.89, 'unité', '2026-02-28 21:27:18', 28),
(21151, 'temp', 74.05, 'unité', '2026-02-28 21:27:18', 29),
(21152, 'temp', 6.76, 'unité', '2026-02-28 21:27:23', 28),
(21153, 'temp', 92.84, 'unité', '2026-02-28 21:27:23', 29),
(21154, 'temp', 79.6, 'unité', '2026-02-28 21:27:28', 28),
(21155, 'temp', 32.48, 'unité', '2026-02-28 21:27:28', 29),
(21156, 'temp', 85.62, 'unité', '2026-02-28 21:27:33', 28),
(21157, 'temp', 87.56, 'unité', '2026-02-28 21:27:33', 29),
(21158, 'temp', 55.64, 'unité', '2026-02-28 21:27:39', 28),
(21159, 'temp', 72.59, 'unité', '2026-02-28 21:27:39', 29),
(21160, 'temp', 96.29, 'unité', '2026-02-28 21:27:44', 28),
(21161, 'temp', 85.24, 'unité', '2026-02-28 21:27:44', 29),
(21162, 'temp', 21.86, 'unité', '2026-02-28 21:27:49', 28),
(21163, 'temp', 84.62, 'unité', '2026-02-28 21:27:49', 29),
(21164, 'temp', 2.9, 'unité', '2026-02-28 21:27:54', 28),
(21165, 'temp', 61.2, 'unité', '2026-02-28 21:27:54', 29),
(21166, 'temp', 7.24, 'unité', '2026-02-28 21:27:59', 28),
(21167, 'temp', 34.15, 'unité', '2026-02-28 21:27:59', 29),
(21168, 'temp', 40.3, 'unité', '2026-02-28 21:28:04', 28),
(21169, 'temp', 49.16, 'unité', '2026-02-28 21:28:04', 29),
(21170, 'temp', 46.56, 'unité', '2026-02-28 21:28:09', 28),
(21171, 'temp', 89.46, 'unité', '2026-02-28 21:28:09', 29),
(21172, 'temp', 50.85, 'unité', '2026-02-28 21:28:15', 28),
(21173, 'temp', 92.05, 'unité', '2026-02-28 21:28:15', 29),
(21174, 'temp', 82.75, 'unité', '2026-02-28 21:28:20', 28),
(21175, 'temp', 38.92, 'unité', '2026-02-28 21:28:20', 29),
(21176, 'temp', 0.51, 'unité', '2026-02-28 21:28:25', 28),
(21177, 'temp', 27.67, 'unité', '2026-02-28 21:28:25', 29),
(21178, 'temp', 84.83, 'unité', '2026-02-28 21:28:30', 28),
(21179, 'temp', 44.92, 'unité', '2026-02-28 21:28:30', 29),
(21180, 'temp', 50.68, 'unité', '2026-02-28 21:28:35', 28),
(21181, 'temp', 79.36, 'unité', '2026-02-28 21:28:35', 29),
(21182, 'temp', 76.24, 'unité', '2026-02-28 21:28:40', 28),
(21183, 'temp', 67.04, 'unité', '2026-02-28 21:28:40', 29),
(21184, 'temp', 57.52, 'unité', '2026-02-28 21:28:45', 28),
(21185, 'temp', 78.93, 'unité', '2026-02-28 21:28:45', 29),
(21186, 'temp', 23.43, 'unité', '2026-02-28 21:28:50', 28),
(21187, 'temp', 86.82, 'unité', '2026-02-28 21:28:50', 29),
(21188, 'temp', 88.31, 'unité', '2026-02-28 21:28:56', 28),
(21189, 'temp', 18.35, 'unité', '2026-02-28 21:28:56', 29),
(21190, 'temp', 36.75, 'unité', '2026-02-28 21:29:01', 28),
(21191, 'temp', 74.19, 'unité', '2026-02-28 21:29:01', 29),
(21192, 'temp', 76.01, 'unité', '2026-02-28 21:29:06', 28),
(21193, 'temp', 80.31, 'unité', '2026-02-28 21:29:06', 29),
(21194, 'temp', 67.72, 'unité', '2026-02-28 21:29:11', 28),
(21195, 'temp', 1.53, 'unité', '2026-02-28 21:29:11', 29),
(21196, 'temp', 52.33, 'unité', '2026-02-28 21:29:16', 28),
(21197, 'temp', 56.38, 'unité', '2026-02-28 21:29:16', 29),
(21198, 'temp', 59.19, 'unité', '2026-02-28 21:29:21', 28),
(21199, 'temp', 16.46, 'unité', '2026-02-28 21:29:21', 29),
(21200, 'temp', 1.47, 'unité', '2026-02-28 21:29:26', 28),
(21201, 'temp', 97.2, 'unité', '2026-02-28 21:29:26', 29),
(21202, 'temp', 50.67, 'unité', '2026-02-28 21:29:32', 28),
(21203, 'temp', 79.52, 'unité', '2026-02-28 21:29:32', 29),
(21204, 'temp', 55.17, 'unité', '2026-02-28 21:29:37', 28),
(21205, 'temp', 23.95, 'unité', '2026-02-28 21:29:37', 29),
(21206, 'temp', 8.76, 'unité', '2026-02-28 21:29:42', 28),
(21207, 'temp', 4.01, 'unité', '2026-02-28 21:29:42', 29),
(21208, 'temp', 26.64, 'unité', '2026-02-28 21:29:47', 28),
(21209, 'temp', 86.62, 'unité', '2026-02-28 21:29:47', 29),
(21210, 'temp', 5.24, 'unité', '2026-02-28 21:29:52', 28),
(21211, 'temp', 21.26, 'unité', '2026-02-28 21:29:52', 29),
(21212, 'temp', 12.05, 'unité', '2026-02-28 21:29:57', 28),
(21213, 'temp', 94.01, 'unité', '2026-02-28 21:29:57', 29),
(21214, 'temp', 0.33, 'unité', '2026-02-28 21:30:02', 28),
(21215, 'temp', 7.57, 'unité', '2026-02-28 21:30:02', 29),
(21216, 'temp', 89.61, 'unité', '2026-02-28 21:30:07', 28),
(21217, 'temp', 25.91, 'unité', '2026-02-28 21:30:07', 29),
(21218, 'temp', 84.05, 'unité', '2026-02-28 21:30:12', 28),
(21219, 'temp', 80.77, 'unité', '2026-02-28 21:30:13', 29),
(21220, 'temp', 73.95, 'unité', '2026-02-28 21:30:18', 28),
(21221, 'temp', 42.55, 'unité', '2026-02-28 21:30:18', 29),
(21222, 'temp', 0.29, 'unité', '2026-02-28 21:30:23', 28),
(21223, 'temp', 33.87, 'unité', '2026-02-28 21:30:23', 29),
(21224, 'temp', 33.22, 'unité', '2026-02-28 21:30:28', 28),
(21225, 'temp', 45.75, 'unité', '2026-02-28 21:30:28', 29),
(21226, 'temp', 75.71, 'unité', '2026-02-28 21:30:33', 28),
(21227, 'temp', 27.52, 'unité', '2026-02-28 21:30:33', 29),
(21228, 'temp', 73.69, 'unité', '2026-02-28 21:30:38', 28),
(21229, 'temp', 95.06, 'unité', '2026-02-28 21:30:38', 29),
(21230, 'temp', 42.59, 'unité', '2026-02-28 21:30:43', 28),
(21231, 'temp', 27.51, 'unité', '2026-02-28 21:30:43', 29),
(21232, 'temp', 6.48, 'unité', '2026-02-28 21:30:48', 28),
(21233, 'temp', 43.22, 'unité', '2026-02-28 21:30:48', 29),
(21234, 'temp', 81.73, 'unité', '2026-02-28 21:30:53', 28),
(21235, 'temp', 42.33, 'unité', '2026-02-28 21:30:53', 29),
(21236, 'temp', 98.11, 'unité', '2026-02-28 21:30:59', 28),
(21237, 'temp', 23.98, 'unité', '2026-02-28 21:30:59', 29),
(21238, 'temp', 84.61, 'unité', '2026-02-28 21:31:04', 28),
(21239, 'temp', 68.51, 'unité', '2026-02-28 21:31:04', 29),
(21240, 'temp', 69.07, 'unité', '2026-02-28 21:31:09', 28),
(21241, 'temp', 3.6, 'unité', '2026-02-28 21:31:09', 29),
(21242, 'temp', 44.28, 'unité', '2026-02-28 21:31:14', 28),
(21243, 'temp', 13.27, 'unité', '2026-02-28 21:31:14', 29),
(21244, 'temp', 68.23, 'unité', '2026-02-28 21:31:19', 28),
(21245, 'temp', 71.97, 'unité', '2026-02-28 21:31:19', 29),
(21246, 'temp', 84.86, 'unité', '2026-02-28 21:31:24', 28),
(21247, 'temp', 56.7, 'unité', '2026-02-28 21:31:24', 29),
(21248, 'temp', 3.89, 'unité', '2026-02-28 21:31:30', 28),
(21249, 'temp', 26.52, 'unité', '2026-02-28 21:31:30', 29),
(21250, 'temp', 68.72, 'unité', '2026-02-28 21:31:35', 28),
(21251, 'temp', 13.86, 'unité', '2026-02-28 21:31:35', 29),
(21252, 'temp', 56.51, 'unité', '2026-02-28 21:31:40', 28),
(21253, 'temp', 42.87, 'unité', '2026-02-28 21:31:40', 29),
(21254, 'temp', 67.71, 'unité', '2026-02-28 21:31:45', 28),
(21255, 'temp', 4.18, 'unité', '2026-02-28 21:31:45', 29),
(21256, 'temp', 18.94, 'unité', '2026-02-28 21:31:50', 28),
(21257, 'temp', 87.47, 'unité', '2026-02-28 21:31:50', 29),
(21258, 'temp', 55.26, 'unité', '2026-02-28 21:31:55', 28),
(21259, 'temp', 44.26, 'unité', '2026-02-28 21:31:55', 29),
(21260, 'temp', 95.97, 'unité', '2026-02-28 21:32:00', 28),
(21261, 'temp', 96.35, 'unité', '2026-02-28 21:32:00', 29),
(21262, 'temp', 9.98, 'unité', '2026-02-28 21:32:06', 28),
(21263, 'temp', 70.62, 'unité', '2026-02-28 21:32:06', 29),
(21264, 'temp', 33.97, 'unité', '2026-02-28 21:32:11', 28),
(21265, 'temp', 9.68, 'unité', '2026-02-28 21:32:11', 29),
(21266, 'temp', 90.34, 'unité', '2026-02-28 21:32:16', 28),
(21267, 'temp', 69.16, 'unité', '2026-02-28 21:32:16', 29),
(21268, 'temp', 32.4, 'unité', '2026-02-28 21:32:21', 28),
(21269, 'temp', 28.88, 'unité', '2026-02-28 21:32:21', 29),
(21270, 'temp', 44.27, 'unité', '2026-02-28 21:32:26', 28),
(21271, 'temp', 85.58, 'unité', '2026-02-28 21:32:26', 29),
(21272, 'temp', 29.04, 'unité', '2026-02-28 21:32:31', 28),
(21273, 'temp', 29.07, 'unité', '2026-02-28 21:32:31', 29),
(21274, 'temp', 20.66, 'unité', '2026-02-28 21:32:36', 28),
(21275, 'temp', 93.97, 'unité', '2026-02-28 21:32:36', 29),
(21276, 'temp', 15.42, 'unité', '2026-02-28 21:32:41', 28),
(21277, 'temp', 56.06, 'unité', '2026-02-28 21:32:41', 29),
(21278, 'temp', 61.52, 'unité', '2026-02-28 21:32:47', 28),
(21279, 'temp', 31.01, 'unité', '2026-02-28 21:32:47', 29),
(21280, 'temp', 5.64, 'unité', '2026-02-28 21:32:52', 28),
(21281, 'temp', 31.96, 'unité', '2026-02-28 21:32:52', 29),
(21282, 'temp', 92.36, 'unité', '2026-02-28 21:32:57', 28),
(21283, 'temp', 1.66, 'unité', '2026-02-28 21:32:57', 29),
(21284, 'temp', 85.65, 'unité', '2026-02-28 21:33:02', 28),
(21285, 'temp', 50.84, 'unité', '2026-02-28 21:33:02', 29),
(21286, 'temp', 12.53, 'unité', '2026-02-28 21:33:07', 28),
(21287, 'temp', 47.55, 'unité', '2026-02-28 21:33:07', 29),
(21288, 'temp', 47.05, 'unité', '2026-02-28 21:33:12', 28),
(21289, 'temp', 51.78, 'unité', '2026-02-28 21:33:12', 29),
(21290, 'temp', 44.49, 'unité', '2026-02-28 21:33:17', 28),
(21291, 'temp', 24.37, 'unité', '2026-02-28 21:33:18', 29),
(21292, 'temp', 16.21, 'unité', '2026-02-28 21:33:23', 28),
(21293, 'temp', 12.1, 'unité', '2026-02-28 21:33:23', 29),
(21294, 'temp', 92.73, 'unité', '2026-02-28 21:33:28', 28),
(21295, 'temp', 35.48, 'unité', '2026-02-28 21:33:28', 29),
(21296, 'temp', 33.73, 'unité', '2026-02-28 21:33:33', 28),
(21297, 'temp', 53.25, 'unité', '2026-02-28 21:33:33', 29),
(21298, 'temp', 33.22, 'unité', '2026-02-28 21:33:38', 28),
(21299, 'temp', 71.53, 'unité', '2026-02-28 21:33:38', 29),
(21300, 'temp', 99.36, 'unité', '2026-02-28 21:33:43', 28),
(21301, 'temp', 61.07, 'unité', '2026-02-28 21:33:43', 29),
(21302, 'temp', 10.79, 'unité', '2026-02-28 21:33:48', 28),
(21303, 'temp', 39.05, 'unité', '2026-02-28 21:33:48', 29),
(21304, 'temp', 55.9, 'unité', '2026-02-28 21:33:53', 28),
(21305, 'temp', 55.2, 'unité', '2026-02-28 21:33:53', 29),
(21306, 'temp', 53.41, 'unité', '2026-02-28 21:33:58', 28),
(21307, 'temp', 44.05, 'unité', '2026-02-28 21:33:58', 29),
(21308, 'temp', 89.76, 'unité', '2026-02-28 21:34:04', 28),
(21309, 'temp', 73.35, 'unité', '2026-02-28 21:34:04', 29),
(21310, 'temp', 74.64, 'unité', '2026-02-28 21:34:09', 28),
(21311, 'temp', 93.53, 'unité', '2026-02-28 21:34:09', 29),
(21312, 'temp', 60.66, 'unité', '2026-02-28 21:34:14', 28),
(21313, 'temp', 63.56, 'unité', '2026-02-28 21:34:14', 29),
(21314, 'temp', 90.57, 'unité', '2026-02-28 21:34:19', 28),
(21315, 'temp', 89.65, 'unité', '2026-02-28 21:34:19', 29),
(21316, 'temp', 38.1, 'unité', '2026-02-28 21:34:24', 28),
(21317, 'temp', 55.93, 'unité', '2026-02-28 21:34:24', 29),
(21318, 'temp', 15.78, 'unité', '2026-02-28 21:34:29', 28),
(21319, 'temp', 93.33, 'unité', '2026-02-28 21:34:29', 29),
(21320, 'temp', 96.54, 'unité', '2026-02-28 21:34:34', 28),
(21321, 'temp', 92.93, 'unité', '2026-02-28 21:34:34', 29),
(21322, 'temp', 96.65, 'unité', '2026-02-28 21:34:40', 28),
(21323, 'temp', 17.2, 'unité', '2026-02-28 21:34:40', 29),
(21324, 'temp', 51.12, 'unité', '2026-02-28 21:34:45', 28),
(21325, 'temp', 46.58, 'unité', '2026-02-28 21:34:45', 29),
(21326, 'temp', 77.32, 'unité', '2026-02-28 21:34:50', 28),
(21327, 'temp', 7.86, 'unité', '2026-02-28 21:34:50', 29),
(21328, 'temp', 20.07, 'unité', '2026-02-28 21:34:55', 28),
(21329, 'temp', 67.95, 'unité', '2026-02-28 21:34:55', 29),
(21330, 'temp', 0.03, 'unité', '2026-02-28 21:35:00', 28),
(21331, 'temp', 38.11, 'unité', '2026-02-28 21:35:00', 29),
(21332, 'temp', 96.58, 'unité', '2026-02-28 21:35:05', 28),
(21333, 'temp', 11.07, 'unité', '2026-02-28 21:35:05', 29),
(21334, 'temp', 19.94, 'unité', '2026-02-28 21:35:10', 28),
(21335, 'temp', 33.43, 'unité', '2026-02-28 21:35:10', 29),
(21336, 'temp', 53.73, 'unité', '2026-02-28 21:35:15', 28),
(21337, 'temp', 78.14, 'unité', '2026-02-28 21:35:15', 29),
(21338, 'temp', 82.28, 'unité', '2026-02-28 21:35:20', 28),
(21339, 'temp', 47.43, 'unité', '2026-02-28 21:35:20', 29),
(21340, 'temp', 23.5, 'unité', '2026-02-28 21:35:26', 28),
(21341, 'temp', 25.49, 'unité', '2026-02-28 21:35:26', 29),
(21342, 'temp', 38.91, 'unité', '2026-02-28 21:35:31', 28),
(21343, 'temp', 85.95, 'unité', '2026-02-28 21:35:31', 29),
(21344, 'temp', 85.99, 'unité', '2026-02-28 21:35:36', 28),
(21345, 'temp', 76.48, 'unité', '2026-02-28 21:35:36', 29),
(21346, 'temp', 36.19, 'unité', '2026-02-28 21:35:41', 28),
(21347, 'temp', 43.67, 'unité', '2026-02-28 21:35:41', 29),
(21348, 'temp', 51.23, 'unité', '2026-02-28 21:35:46', 28),
(21349, 'temp', 8.95, 'unité', '2026-02-28 21:35:46', 29),
(21350, 'temp', 18.57, 'unité', '2026-02-28 21:35:51', 28),
(21351, 'temp', 31.13, 'unité', '2026-02-28 21:35:51', 29),
(21352, 'temp', 34.37, 'unité', '2026-02-28 21:35:56', 28),
(21353, 'temp', 55, 'unité', '2026-02-28 21:35:56', 29),
(21354, 'temp', 21.28, 'unité', '2026-02-28 21:36:01', 28),
(21355, 'temp', 98.03, 'unité', '2026-02-28 21:36:01', 29),
(21356, 'temp', 50.59, 'unité', '2026-02-28 21:36:06', 28),
(21357, 'temp', 30.82, 'unité', '2026-02-28 21:36:07', 29),
(21358, 'temp', 92.47, 'unité', '2026-02-28 21:36:12', 28),
(21359, 'temp', 55.1, 'unité', '2026-02-28 21:36:12', 29),
(21360, 'temp', 65.58, 'unité', '2026-02-28 21:36:17', 28),
(21361, 'temp', 32.73, 'unité', '2026-02-28 21:36:17', 29),
(21362, 'temp', 63.98, 'unité', '2026-02-28 21:36:22', 28),
(21363, 'temp', 29.25, 'unité', '2026-02-28 21:36:22', 29),
(21364, 'temp', 1.29, 'unité', '2026-02-28 21:36:27', 28),
(21365, 'temp', 53.02, 'unité', '2026-02-28 21:36:27', 29),
(21366, 'temp', 83.39, 'unité', '2026-02-28 21:36:32', 28),
(21367, 'temp', 49.01, 'unité', '2026-02-28 21:36:32', 29),
(21368, 'temp', 50.04, 'unité', '2026-02-28 21:36:37', 28),
(21369, 'temp', 35.09, 'unité', '2026-02-28 21:36:37', 29),
(21370, 'temp', 80.45, 'unité', '2026-02-28 21:36:42', 28),
(21371, 'temp', 76.07, 'unité', '2026-02-28 21:36:42', 29),
(21372, 'temp', 62.62, 'unité', '2026-02-28 21:36:47', 28),
(21373, 'temp', 38.33, 'unité', '2026-02-28 21:36:47', 29),
(21374, 'temp', 36.65, 'unité', '2026-02-28 21:36:53', 28),
(21375, 'temp', 2.77, 'unité', '2026-02-28 21:36:53', 29),
(21376, 'temp', 29.16, 'unité', '2026-02-28 21:36:58', 28),
(21377, 'temp', 66.37, 'unité', '2026-02-28 21:36:58', 29),
(21378, 'temp', 57.68, 'unité', '2026-02-28 21:37:03', 28),
(21379, 'temp', 70.7, 'unité', '2026-02-28 21:37:03', 29),
(21380, 'temp', 35.08, 'unité', '2026-02-28 21:37:08', 28),
(21381, 'temp', 42.79, 'unité', '2026-02-28 21:37:08', 29),
(21382, 'temp', 27.6, 'unité', '2026-02-28 21:37:13', 28),
(21383, 'temp', 48.6, 'unité', '2026-02-28 21:37:13', 29),
(21384, 'temp', 67.9, 'unité', '2026-02-28 21:37:18', 28),
(21385, 'temp', 3.12, 'unité', '2026-02-28 21:37:18', 29),
(21386, 'temp', 57.83, 'unité', '2026-02-28 21:37:23', 28),
(21387, 'temp', 69.71, 'unité', '2026-02-28 21:37:23', 29),
(21388, 'temp', 31.88, 'unité', '2026-02-28 21:37:28', 28),
(21389, 'temp', 62.26, 'unité', '2026-02-28 21:37:28', 29),
(21390, 'temp', 53.59, 'unité', '2026-02-28 21:37:33', 28),
(21391, 'temp', 45.51, 'unité', '2026-02-28 21:37:33', 29),
(21392, 'temp', 73.67, 'unité', '2026-02-28 21:37:39', 28),
(21393, 'temp', 45.51, 'unité', '2026-02-28 21:37:39', 29),
(21394, 'temp', 23.21, 'unité', '2026-02-28 21:37:44', 28),
(21395, 'temp', 75.44, 'unité', '2026-02-28 21:37:44', 29),
(21396, 'temp', 40.69, 'unité', '2026-02-28 21:37:49', 28),
(21397, 'temp', 35.27, 'unité', '2026-02-28 21:37:49', 29),
(21398, 'temp', 80.8, 'unité', '2026-02-28 21:37:54', 28),
(21399, 'temp', 66.26, 'unité', '2026-02-28 21:37:54', 29),
(21400, 'temp', 32.56, 'unité', '2026-02-28 21:37:59', 28),
(21401, 'temp', 2.91, 'unité', '2026-02-28 21:37:59', 29),
(21402, 'temp', 71.25, 'unité', '2026-02-28 21:38:04', 28),
(21403, 'temp', 86.96, 'unité', '2026-02-28 21:38:04', 29),
(21404, 'temp', 85.19, 'unité', '2026-02-28 21:38:09', 28),
(21405, 'temp', 52.69, 'unité', '2026-02-28 21:38:09', 29),
(21406, 'temp', 67.71, 'unité', '2026-02-28 21:38:14', 28),
(21407, 'temp', 65.14, 'unité', '2026-02-28 21:38:15', 29),
(21408, 'temp', 87.27, 'unité', '2026-02-28 21:38:20', 28),
(21409, 'temp', 60.18, 'unité', '2026-02-28 21:38:20', 29),
(21410, 'temp', 18.17, 'unité', '2026-02-28 21:38:25', 28),
(21411, 'temp', 42.12, 'unité', '2026-02-28 21:38:25', 29),
(21412, 'temp', 91.74, 'unité', '2026-02-28 21:38:30', 28),
(21413, 'temp', 21.22, 'unité', '2026-02-28 21:38:30', 29),
(21414, 'temp', 91.89, 'unité', '2026-02-28 21:38:35', 28),
(21415, 'temp', 9.01, 'unité', '2026-02-28 21:38:35', 29),
(21416, 'temp', 3.25, 'unité', '2026-02-28 21:38:40', 28),
(21417, 'temp', 2.28, 'unité', '2026-02-28 21:38:40', 29),
(21418, 'temp', 42.27, 'unité', '2026-02-28 21:38:45', 28),
(21419, 'temp', 26.72, 'unité', '2026-02-28 21:38:45', 29),
(21420, 'temp', 38.67, 'unité', '2026-02-28 21:38:50', 28),
(21421, 'temp', 33.93, 'unité', '2026-02-28 21:38:50', 29),
(21422, 'temp', 84.08, 'unité', '2026-02-28 21:38:56', 28),
(21423, 'temp', 7.11, 'unité', '2026-02-28 21:38:56', 29),
(21424, 'temp', 52.9, 'unité', '2026-02-28 21:39:01', 28),
(21425, 'temp', 87.92, 'unité', '2026-02-28 21:39:01', 29),
(21426, 'temp', 64.79, 'unité', '2026-02-28 21:39:06', 28),
(21427, 'temp', 23.36, 'unité', '2026-02-28 21:39:06', 29),
(21428, 'temp', 85.47, 'unité', '2026-02-28 21:39:11', 28),
(21429, 'temp', 9.43, 'unité', '2026-02-28 21:39:11', 29),
(21430, 'temp', 26.24, 'unité', '2026-02-28 21:39:16', 28),
(21431, 'temp', 73.08, 'unité', '2026-02-28 21:39:16', 29),
(21432, 'temp', 21.2, 'unité', '2026-02-28 21:39:21', 28),
(21433, 'temp', 40.39, 'unité', '2026-02-28 21:39:21', 29),
(21434, 'temp', 8.35, 'unité', '2026-02-28 21:39:26', 28),
(21435, 'temp', 87.68, 'unité', '2026-02-28 21:39:26', 29),
(21436, 'temp', 45.53, 'unité', '2026-02-28 21:39:31', 28),
(21437, 'temp', 95.75, 'unité', '2026-02-28 21:39:31', 29),
(21438, 'temp', 77.53, 'unité', '2026-02-28 21:39:37', 28),
(21439, 'temp', 95.04, 'unité', '2026-02-28 21:39:37', 29),
(21440, 'temp', 68.59, 'unité', '2026-02-28 21:39:42', 28),
(21441, 'temp', 53.27, 'unité', '2026-02-28 21:39:42', 29),
(21442, 'temp', 91.79, 'unité', '2026-02-28 21:39:47', 28),
(21443, 'temp', 37.17, 'unité', '2026-02-28 21:39:47', 29),
(21444, 'temp', 57.17, 'unité', '2026-02-28 21:39:52', 28),
(21445, 'temp', 68.87, 'unité', '2026-02-28 21:39:52', 29),
(21446, 'temp', 10.64, 'unité', '2026-02-28 21:39:57', 28),
(21447, 'temp', 51.63, 'unité', '2026-02-28 21:39:57', 29),
(21448, 'temp', 55.54, 'unité', '2026-02-28 21:40:02', 28),
(21449, 'temp', 73.46, 'unité', '2026-02-28 21:40:02', 29),
(21450, 'temp', 50.69, 'unité', '2026-02-28 21:40:07', 28),
(21451, 'temp', 94.71, 'unité', '2026-02-28 21:40:07', 29),
(21452, 'temp', 22.92, 'unité', '2026-02-28 21:40:12', 28),
(21453, 'temp', 55.77, 'unité', '2026-02-28 21:40:12', 29),
(21454, 'temp', 39.14, 'unité', '2026-02-28 21:40:18', 28),
(21455, 'temp', 17.08, 'unité', '2026-02-28 21:40:18', 29),
(21456, 'temp', 12.97, 'unité', '2026-02-28 21:40:23', 28),
(21457, 'temp', 76.8, 'unité', '2026-02-28 21:40:23', 29),
(21458, 'temp', 84.78, 'unité', '2026-02-28 21:40:28', 28),
(21459, 'temp', 96.39, 'unité', '2026-02-28 21:40:28', 29),
(21460, 'temp', 95.51, 'unité', '2026-02-28 21:40:33', 28),
(21461, 'temp', 25.41, 'unité', '2026-02-28 21:40:33', 29),
(21462, 'temp', 50.25, 'unité', '2026-02-28 21:40:38', 28),
(21463, 'temp', 95.92, 'unité', '2026-02-28 21:40:38', 29),
(21464, 'temp', 95.97, 'unité', '2026-02-28 21:40:43', 28),
(21465, 'temp', 33.21, 'unité', '2026-02-28 21:40:43', 29),
(21466, 'temp', 22.93, 'unité', '2026-02-28 21:40:48', 28),
(21467, 'temp', 81.65, 'unité', '2026-02-28 21:40:48', 29),
(21468, 'temp', 7.08, 'unité', '2026-02-28 21:40:53', 28),
(21469, 'temp', 95.17, 'unité', '2026-02-28 21:40:53', 29),
(21470, 'temp', 14.7, 'unité', '2026-02-28 21:40:59', 28),
(21471, 'temp', 0.96, 'unité', '2026-02-28 21:40:59', 29),
(21472, 'temp', 98.5, 'unité', '2026-02-28 21:41:04', 28),
(21473, 'temp', 95.19, 'unité', '2026-02-28 21:41:04', 29),
(21474, 'temp', 80.22, 'unité', '2026-02-28 21:41:09', 28),
(21475, 'temp', 17.62, 'unité', '2026-02-28 21:41:09', 29),
(21476, 'temp', 41.69, 'unité', '2026-02-28 21:41:14', 28),
(21477, 'temp', 51.51, 'unité', '2026-02-28 21:41:14', 29),
(21478, 'temp', 13.32, 'unité', '2026-02-28 21:41:19', 28),
(21479, 'temp', 76.71, 'unité', '2026-02-28 21:41:19', 29),
(21480, 'temp', 87.5, 'unité', '2026-02-28 21:41:24', 28),
(21481, 'temp', 92.41, 'unité', '2026-02-28 21:41:24', 29),
(21482, 'temp', 46.07, 'unité', '2026-02-28 21:41:29', 28),
(21483, 'temp', 85.67, 'unité', '2026-02-28 21:41:29', 29),
(21484, 'temp', 22.73, 'unité', '2026-02-28 21:41:34', 28),
(21485, 'temp', 17.33, 'unité', '2026-02-28 21:41:34', 29),
(21486, 'temp', 48.15, 'unité', '2026-02-28 21:41:40', 28),
(21487, 'temp', 85.54, 'unité', '2026-02-28 21:41:40', 29),
(21488, 'temp', 48.25, 'unité', '2026-02-28 21:41:45', 28),
(21489, 'temp', 89.25, 'unité', '2026-02-28 21:41:45', 29),
(21490, 'temp', 87.2, 'unité', '2026-02-28 21:41:50', 28),
(21491, 'temp', 6.1, 'unité', '2026-02-28 21:41:50', 29),
(21492, 'temp', 41.8, 'unité', '2026-02-28 21:41:55', 28),
(21493, 'temp', 1.63, 'unité', '2026-02-28 21:41:55', 29),
(21494, 'temp', 99.14, 'unité', '2026-02-28 21:42:00', 28),
(21495, 'temp', 72.25, 'unité', '2026-02-28 21:42:00', 29),
(21496, 'temp', 47.12, 'unité', '2026-02-28 21:42:05', 28),
(21497, 'temp', 35.42, 'unité', '2026-02-28 21:42:05', 29),
(21498, 'temp', 48.41, 'unité', '2026-02-28 21:42:10', 28),
(21499, 'temp', 20.5, 'unité', '2026-02-28 21:42:10', 29),
(21500, 'temp', 26.1, 'unité', '2026-02-28 21:42:15', 28),
(21501, 'temp', 87.91, 'unité', '2026-02-28 21:42:15', 29);

-- --------------------------------------------------------

--
-- Structure de la table `ressourceproject`
--

CREATE TABLE `ressourceproject` (
  `idressource` int(11) NOT NULL,
  `nomressource` varchar(100) NOT NULL,
  `typeressource` enum('equipement','materiaux','service') NOT NULL,
  `quantite` int(11) NOT NULL,
  `cout` decimal(12,2) NOT NULL,
  `fournisseur` varchar(100) DEFAULT NULL,
  `statut` enum('prevu','achete') DEFAULT 'prevu',
  `dateajout` date NOT NULL,
  `idproject` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `ressourceproject`
--

INSERT INTO `ressourceproject` (`idressource`, `nomressource`, `typeressource`, `quantite`, `cout`, `fournisseur`, `statut`, `dateajout`, `idproject`) VALUES
(27, 'tracteur', 'equipement', 1, 540000.00, 'agri', 'prevu', '2026-02-27', 31),
(28, 'tracteur', 'equipement', 1, 4000.00, 'agrifund', 'prevu', '2026-02-27', 31),
(29, 'menetre', 'materiaux', 70, 6000.00, 'tunisia', 'achete', '2026-02-27', 1);

-- --------------------------------------------------------

--
-- Structure de la table `statutenligne`
--

CREATE TABLE `statutenligne` (
  `utilisateur_id` int(11) NOT NULL,
  `est_en_ligne` tinyint(1) DEFAULT 0,
  `derniere_activite` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Structure de la table `tokenreinitialisation`
--

CREATE TABLE `tokenreinitialisation` (
  `id` int(11) NOT NULL,
  `utilisateur_id` int(11) NOT NULL,
  `token` varchar(100) NOT NULL,
  `date_expiration` datetime NOT NULL,
  `utilise` tinyint(1) DEFAULT 0,
  `date_utilisation` datetime DEFAULT NULL,
  `date_creation` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `tokenreinitialisation`
--

INSERT INTO `tokenreinitialisation` (`id`, `utilisateur_id`, `token`, `date_expiration`, `utilise`, `date_utilisation`, `date_creation`) VALUES
(1, 2, 'c389db0b-614f-4586-b8c5-0473f38421dd', '2026-02-07 11:46:49', 0, NULL, '2026-02-07 10:46:49'),
(2, 2, '7f73a036-acb7-410b-bd9a-dfcc50b6a618', '2026-02-07 12:01:59', 1, '2026-02-07 11:03:35', '2026-02-07 11:01:59'),
(3, 2, 'b583f86c-fb28-4ccc-a43a-a220ca7597d7', '2026-02-07 12:15:06', 1, '2026-02-07 11:16:32', '2026-02-07 11:15:06'),
(4, 2, '1ea3d0aa-8ba8-4802-9f57-2f05007413c0', '2026-02-07 12:42:48', 1, '2026-02-07 11:44:21', '2026-02-07 11:42:48'),
(5, 4, 'b13aab68-6624-412f-b974-4ad8227aba8a', '2026-02-07 18:21:41', 1, '2026-02-07 17:23:33', '2026-02-07 17:21:41'),
(7, 7, 'a205b0ee-6926-4510-abc5-e26d596916fe', '2026-02-16 10:36:30', 1, '2026-02-16 09:39:02', '2026-02-16 09:36:30'),
(8, 6, '208ee307-93dd-44ca-8875-8c2b16525acf', '2026-02-22 18:46:35', 1, '2026-02-22 17:47:05', '2026-02-22 17:46:35'),
(9, 6, 'b1c9f1d5-3228-47c2-b487-11a94f4057d2', '2026-02-22 20:24:35', 1, '2026-02-22 19:25:31', '2026-02-22 19:24:35');

-- --------------------------------------------------------

--
-- Structure de la table `utilisateur`
--

CREATE TABLE `utilisateur` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `prenom` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `tel` varchar(20) DEFAULT NULL,
  `date_inscrit` datetime DEFAULT current_timestamp(),
  `photo` varchar(255) DEFAULT NULL,
  `derniere_connexion` timestamp NOT NULL DEFAULT current_timestamp(),
  `est_en_ligne` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Déchargement des données de la table `utilisateur`
--

INSERT INTO `utilisateur` (`id`, `nom`, `prenom`, `email`, `password`, `tel`, `date_inscrit`, `photo`, `derniere_connexion`, `est_en_ligne`) VALUES
(2, 'BEN RHOUMA', 'Samir12', 'souleimab945@gmail.com', '$2a$10$eR7J5DWHf9tXQHJF1ppp3.LpCCS8Gn/FOiTeWD/yNUQEJcrabtAcy', '56328712', '2026-02-07 10:37:50', 'uploads/photos/user_2_20260226_213758.png', '2026-02-15 07:43:52', 0),
(3, 'Ben rhouma', 'Souleima', 'souleima.benrhouma@esprit.tn', '$2a$10$5C3kG19hPwSOZ0CczSxfBuM1nxLg9GaBfda6SnLj0Oq0kdm2b.lF2', '97844625', '2026-02-07 10:43:00', 'uploads/photos/user_3_20260213_154049.png', '2026-02-15 07:43:52', 0),
(4, 'stb', '', 'souleimabenrhouma18122002@gmail.com', '$2a$10$DTCOxjXZXy9UGRdIFZpi3ewDAQ9t45u8pj6URutVwPMU1gUdz1PL6', '70580790', '2026-02-07 11:24:18', 'uploads/photos/user_4_20260207_174308.png', '2026-02-15 07:43:52', 0),
(6, 'JAFFEL', 'Rane', 'souleimabenrhouma7@gmail.com', '$2a$10$XXeTtPbxx243opMd7yYz5.EVfdZ4dn9HXQ24A3gH/WS7DP0tvhkbO', '56328712', '2026-02-15 09:56:47', 'uploads/photos/user_6_20260227_155727.png', '2026-02-15 07:56:47', 0),
(7, 'Mannoubi', 'Farah', 'farah.mannoubi@esprit.tn', '$2a$10$gYl2lpuuIFZknvhR9g.s/uj0A8mtT/GWgOU4zD6xAEXcJtMyaGZ3W', '0022', '2026-02-16 09:29:34', NULL, '2026-02-16 07:29:34', 0);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_dernier_message_conversation`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_dernier_message_conversation` (
`conversation_id` int(11)
,`message_id` int(11)
,`contenu` text
,`date_envoi` datetime
,`expediteur_id` int(11)
,`est_lu` tinyint(1)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_messages_avec_pieces_jointes`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_messages_avec_pieces_jointes` (
`message_id` int(11)
,`conversation_id` int(11)
,`expediteur_id` int(11)
,`contenu` text
,`date_envoi` datetime
,`a_piece_jointe` tinyint(1)
,`nb_pieces_jointes` int(11)
,`total_fichiers` bigint(21)
,`nb_images` decimal(22,0)
,`nb_documents` decimal(22,0)
,`nb_audios` decimal(22,0)
,`taille_totale` decimal(41,0)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_messages_non_lus`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_messages_non_lus` (
`utilisateur_id` int(11)
,`correspondant_id` int(11)
,`nb_non_lus` bigint(21)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_stats_fichiers_conversation`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_stats_fichiers_conversation` (
`conversation_id` int(11)
,`total_fichiers` bigint(21)
,`total_images` decimal(22,0)
,`total_audios` decimal(22,0)
,`total_documents` decimal(22,0)
,`espace_utilise` decimal(41,0)
);

-- --------------------------------------------------------

--
-- Doublure de structure pour la vue `v_stats_securite_utilisateur`
-- (Voir ci-dessous la vue réelle)
--
CREATE TABLE `v_stats_securite_utilisateur` (
`id` int(11)
,`email` varchar(255)
,`a2fa_active` tinyint(1)
,`methode_2fa` enum('email','sms','desactive')
,`nb_connexions_total` bigint(21)
,`nb_connexions_reussies` decimal(22,0)
,`nb_tentatives_echouees` decimal(22,0)
,`derniere_connexion` datetime
);

-- --------------------------------------------------------

--
-- Structure de la vue `v_dernier_message_conversation`
--
DROP TABLE IF EXISTS `v_dernier_message_conversation`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_dernier_message_conversation`  AS SELECT `c`.`id` AS `conversation_id`, `m`.`id` AS `message_id`, `m`.`contenu` AS `contenu`, `m`.`date_envoi` AS `date_envoi`, `m`.`expediteur_id` AS `expediteur_id`, `m`.`est_lu` AS `est_lu` FROM (`conversation` `c` left join `message` `m` on(`m`.`id` = (select max(`message`.`id`) from `message` where `message`.`conversation_id` = `c`.`id` and `message`.`est_supprime` = 0))) ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_messages_avec_pieces_jointes`
--
DROP TABLE IF EXISTS `v_messages_avec_pieces_jointes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_messages_avec_pieces_jointes`  AS SELECT `m`.`id` AS `message_id`, `m`.`conversation_id` AS `conversation_id`, `m`.`expediteur_id` AS `expediteur_id`, `m`.`contenu` AS `contenu`, `m`.`date_envoi` AS `date_envoi`, `m`.`a_piece_jointe` AS `a_piece_jointe`, `m`.`nb_pieces_jointes` AS `nb_pieces_jointes`, count(`p`.`id`) AS `total_fichiers`, sum(case when `p`.`type_fichier` = 'image' then 1 else 0 end) AS `nb_images`, sum(case when `p`.`type_fichier` = 'document' then 1 else 0 end) AS `nb_documents`, sum(case when `p`.`type_fichier` = 'audio' then 1 else 0 end) AS `nb_audios`, sum(`p`.`taille_octets`) AS `taille_totale` FROM (`message` `m` left join `piecejointe` `p` on(`m`.`id` = `p`.`message_id`)) GROUP BY `m`.`id`, `m`.`conversation_id`, `m`.`expediteur_id`, `m`.`contenu`, `m`.`date_envoi`, `m`.`a_piece_jointe`, `m`.`nb_pieces_jointes` ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_messages_non_lus`
--
DROP TABLE IF EXISTS `v_messages_non_lus`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_messages_non_lus`  AS SELECT `c`.`utilisateur1_id` AS `utilisateur_id`, `c`.`utilisateur2_id` AS `correspondant_id`, count(0) AS `nb_non_lus` FROM (`conversation` `c` join `message` `m` on(`m`.`conversation_id` = `c`.`id`)) WHERE `m`.`expediteur_id` = `c`.`utilisateur2_id` AND `m`.`est_lu` = 0 AND `m`.`est_supprime` = 0 GROUP BY `c`.`utilisateur1_id`, `c`.`utilisateur2_id`union all select `c`.`utilisateur2_id` AS `utilisateur_id`,`c`.`utilisateur1_id` AS `correspondant_id`,count(0) AS `nb_non_lus` from (`conversation` `c` join `message` `m` on(`m`.`conversation_id` = `c`.`id`)) where `m`.`expediteur_id` = `c`.`utilisateur1_id` and `m`.`est_lu` = 0 and `m`.`est_supprime` = 0 group by `c`.`utilisateur2_id`,`c`.`utilisateur1_id`  ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_stats_fichiers_conversation`
--
DROP TABLE IF EXISTS `v_stats_fichiers_conversation`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stats_fichiers_conversation`  AS SELECT `c`.`id` AS `conversation_id`, count(distinct `p`.`id`) AS `total_fichiers`, sum(case when `p`.`type_fichier` = 'image' then 1 else 0 end) AS `total_images`, sum(case when `p`.`type_fichier` = 'audio' then 1 else 0 end) AS `total_audios`, sum(case when `p`.`type_fichier` = 'document' then 1 else 0 end) AS `total_documents`, sum(`p`.`taille_octets`) AS `espace_utilise` FROM ((`conversation` `c` left join `message` `m` on(`c`.`id` = `m`.`conversation_id`)) left join `piecejointe` `p` on(`m`.`id` = `p`.`message_id`)) GROUP BY `c`.`id` ;

-- --------------------------------------------------------

--
-- Structure de la vue `v_stats_securite_utilisateur`
--
DROP TABLE IF EXISTS `v_stats_securite_utilisateur`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_stats_securite_utilisateur`  AS SELECT `u`.`id` AS `id`, `u`.`email` AS `email`, `p`.`est_active` AS `a2fa_active`, `p`.`methode_preferee` AS `methode_2fa`, count(distinct `h`.`id`) AS `nb_connexions_total`, sum(case when `h`.`connexion_reussie` = 1 then 1 else 0 end) AS `nb_connexions_reussies`, sum(case when `h`.`connexion_reussie` = 0 then 1 else 0 end) AS `nb_tentatives_echouees`, max(`h`.`date_connexion`) AS `derniere_connexion` FROM ((`utilisateur` `u` left join `parametres2fa` `p` on(`u`.`id` = `p`.`utilisateur_id`)) left join `historiqueconnexion` `h` on(`u`.`id` = `h`.`utilisateur_id`)) GROUP BY `u`.`id`, `u`.`email`, `p`.`est_active`, `p`.`methode_preferee` ;

--
-- Index pour les tables déchargées
--

--
-- Index pour la table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`);

--
-- Index pour la table `agriculteur`
--
ALTER TABLE `agriculteur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `idx_status` (`statuscompte`),
  ADD KEY `idx_verifie` (`compteverifie`);

--
-- Index pour la table `analyse_risque_agricole`
--
ALTER TABLE `analyse_risque_agricole`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agriculteur_id` (`agriculteur_id`),
  ADD KEY `idx_analyse_banque` (`banque_id`),
  ADD KEY `idx_analyse_date` (`date_analyse`);

--
-- Index pour la table `banque`
--
ALTER TABLE `banque`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codebanque` (`codebanque`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `idx_code` (`codebanque`),
  ADD KEY `idx_status` (`statusCompte`);

--
-- Index pour la table `capteur`
--
ALTER TABLE `capteur`
  ADD PRIMARY KEY (`id_capteur`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_type` (`typeCapteur`),
  ADD KEY `fk_capteur_projet` (`idproject`);

--
-- Index pour la table `code2fa`
--
ALTER TABLE `code2fa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_code` (`code`),
  ADD KEY `idx_expiration` (`date_expiration`);

--
-- Index pour la table `conversation`
--
ALTER TABLE `conversation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_conversation` (`utilisateur_min`,`utilisateur_max`),
  ADD KEY `idx_utilisateur1` (`utilisateur1_id`),
  ADD KEY `idx_utilisateur2` (`utilisateur2_id`),
  ADD KEY `idx_activite` (`derniere_activite`);

--
-- Index pour la table `decisionfinanciere`
--
ALTER TABLE `decisionfinanciere`
  ADD PRIMARY KEY (`idDecision`),
  ADD KEY `decisionfinanciere_ibfk_1` (`idEvaluation`),
  ADD KEY `fk_decision_banque` (`banqueId`);

--
-- Index pour la table `document`
--
ALTER TABLE `document`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_statut` (`statut`),
  ADD KEY `idx_type` (`type_document`);

--
-- Index pour la table `donnees_satellite`
--
ALTER TABLE `donnees_satellite`
  ADD PRIMARY KEY (`id`),
  ADD KEY `agriculteur_id` (`agriculteur_id`),
  ADD KEY `idx_satellite_date` (`date_mesure`),
  ADD KEY `idx_satellite_coords` (`latitude`,`longitude`);

--
-- Index pour la table `evaluationrisque`
--
ALTER TABLE `evaluationrisque`
  ADD PRIMARY KEY (`idEvaluation`),
  ADD KEY `fk_evaluation_projectagricole` (`idProjet`),
  ADD KEY `fk_evaluation_banque` (`banqueId`);

--
-- Index pour la table `historiqueconnexion`
--
ALTER TABLE `historiqueconnexion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`),
  ADD KEY `idx_date` (`date_connexion`),
  ADD KEY `idx_reussie` (`connexion_reussie`);

--
-- Index pour la table `message`
--
ALTER TABLE `message`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_conversation` (`conversation_id`),
  ADD KEY `idx_expediteur` (`expediteur_id`),
  ADD KEY `idx_date` (`date_envoi`),
  ADD KEY `idx_lu` (`est_lu`),
  ADD KEY `idx_message_lecture` (`date_lecture`);

--
-- Index pour la table `offre_financiere`
--
ALTER TABLE `offre_financiere`
  ADD PRIMARY KEY (`id_offre`),
  ADD KEY `fk_produit` (`id_produit`);

--
-- Index pour la table `parametres2fa`
--
ALTER TABLE `parametres2fa`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `idx_utilisateur` (`utilisateur_id`);

--
-- Index pour la table `piecejointe`
--
ALTER TABLE `piecejointe`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_message` (`message_id`),
  ADD KEY `idx_type` (`type_fichier`),
  ADD KEY `idx_date` (`date_upload`);

--
-- Index pour la table `produit_financier`
--
ALTER TABLE `produit_financier`
  ADD PRIMARY KEY (`id_produit`);

--
-- Index pour la table `projectagricole`
--
ALTER TABLE `projectagricole`
  ADD PRIMARY KEY (`idproject`),
  ADD KEY `fk_project_agriculteur` (`agriculteur_id`);

--
-- Index pour la table `rapport_journalier`
--
ALTER TABLE `rapport_journalier`
  ADD PRIMARY KEY (`id_rapport`),
  ADD KEY `fk_rapport_projet` (`idproject`);

--
-- Index pour la table `releve_terrain`
--
ALTER TABLE `releve_terrain`
  ADD PRIMARY KEY (`id_releve`),
  ADD KEY `idx_capteur` (`id_capteur`),
  ADD KEY `idx_date` (`date_heure`),
  ADD KEY `idx_type` (`type_mesure`);

--
-- Index pour la table `ressourceproject`
--
ALTER TABLE `ressourceproject`
  ADD PRIMARY KEY (`idressource`),
  ADD KEY `idproject` (`idproject`);

--
-- Index pour la table `statutenligne`
--
ALTER TABLE `statutenligne`
  ADD PRIMARY KEY (`utilisateur_id`),
  ADD KEY `idx_statut_utilisateur` (`utilisateur_id`);

--
-- Index pour la table `tokenreinitialisation`
--
ALTER TABLE `tokenreinitialisation`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`),
  ADD KEY `utilisateur_id` (`utilisateur_id`),
  ADD KEY `idx_token` (`token`),
  ADD KEY `idx_expiration` (`date_expiration`);

--
-- Index pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_email` (`email`);

--
-- AUTO_INCREMENT pour les tables déchargées
--

--
-- AUTO_INCREMENT pour la table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT pour la table `agriculteur`
--
ALTER TABLE `agriculteur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `analyse_risque_agricole`
--
ALTER TABLE `analyse_risque_agricole`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `banque`
--
ALTER TABLE `banque`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `capteur`
--
ALTER TABLE `capteur`
  MODIFY `id_capteur` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `code2fa`
--
ALTER TABLE `code2fa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `conversation`
--
ALTER TABLE `conversation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT pour la table `decisionfinanciere`
--
ALTER TABLE `decisionfinanciere`
  MODIFY `idDecision` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT pour la table `document`
--
ALTER TABLE `document`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT pour la table `donnees_satellite`
--
ALTER TABLE `donnees_satellite`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT pour la table `evaluationrisque`
--
ALTER TABLE `evaluationrisque`
  MODIFY `idEvaluation` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT pour la table `historiqueconnexion`
--
ALTER TABLE `historiqueconnexion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=169;

--
-- AUTO_INCREMENT pour la table `message`
--
ALTER TABLE `message`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT pour la table `offre_financiere`
--
ALTER TABLE `offre_financiere`
  MODIFY `id_offre` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `parametres2fa`
--
ALTER TABLE `parametres2fa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT pour la table `piecejointe`
--
ALTER TABLE `piecejointe`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT pour la table `produit_financier`
--
ALTER TABLE `produit_financier`
  MODIFY `id_produit` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT pour la table `projectagricole`
--
ALTER TABLE `projectagricole`
  MODIFY `idproject` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT pour la table `rapport_journalier`
--
ALTER TABLE `rapport_journalier`
  MODIFY `id_rapport` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=42;

--
-- AUTO_INCREMENT pour la table `releve_terrain`
--
ALTER TABLE `releve_terrain`
  MODIFY `id_releve` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21502;

--
-- AUTO_INCREMENT pour la table `ressourceproject`
--
ALTER TABLE `ressourceproject`
  MODIFY `idressource` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT pour la table `tokenreinitialisation`
--
ALTER TABLE `tokenreinitialisation`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT pour la table `utilisateur`
--
ALTER TABLE `utilisateur`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Contraintes pour les tables déchargées
--

--
-- Contraintes pour la table `admin`
--
ALTER TABLE `admin`
  ADD CONSTRAINT `admin_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `agriculteur`
--
ALTER TABLE `agriculteur`
  ADD CONSTRAINT `agriculteur_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `analyse_risque_agricole`
--
ALTER TABLE `analyse_risque_agricole`
  ADD CONSTRAINT `analyse_risque_agricole_ibfk_1` FOREIGN KEY (`banque_id`) REFERENCES `banque` (`id`),
  ADD CONSTRAINT `analyse_risque_agricole_ibfk_2` FOREIGN KEY (`agriculteur_id`) REFERENCES `agriculteur` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `banque`
--
ALTER TABLE `banque`
  ADD CONSTRAINT `banque_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `capteur`
--
ALTER TABLE `capteur`
  ADD CONSTRAINT `fk_capteur_projet` FOREIGN KEY (`idproject`) REFERENCES `projectagricole` (`idproject`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `code2fa`
--
ALTER TABLE `code2fa`
  ADD CONSTRAINT `code2fa_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `conversation`
--
ALTER TABLE `conversation`
  ADD CONSTRAINT `conversation_ibfk_1` FOREIGN KEY (`utilisateur1_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `conversation_ibfk_2` FOREIGN KEY (`utilisateur2_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `decisionfinanciere`
--
ALTER TABLE `decisionfinanciere`
  ADD CONSTRAINT `decisionfinanciere_ibfk_1` FOREIGN KEY (`idEvaluation`) REFERENCES `evaluationrisque` (`idEvaluation`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_decision_banque` FOREIGN KEY (`banqueId`) REFERENCES `utilisateur` (`id`);

--
-- Contraintes pour la table `document`
--
ALTER TABLE `document`
  ADD CONSTRAINT `document_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `donnees_satellite`
--
ALTER TABLE `donnees_satellite`
  ADD CONSTRAINT `donnees_satellite_ibfk_1` FOREIGN KEY (`agriculteur_id`) REFERENCES `agriculteur` (`id`) ON DELETE SET NULL;

--
-- Contraintes pour la table `evaluationrisque`
--
ALTER TABLE `evaluationrisque`
  ADD CONSTRAINT `fk_evaluation_banque` FOREIGN KEY (`banqueId`) REFERENCES `utilisateur` (`id`),
  ADD CONSTRAINT `fk_evaluation_projectagricole` FOREIGN KEY (`idProjet`) REFERENCES `projectagricole` (`idproject`) ON DELETE CASCADE;

--
-- Contraintes pour la table `historiqueconnexion`
--
ALTER TABLE `historiqueconnexion`
  ADD CONSTRAINT `historiqueconnexion_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `message`
--
ALTER TABLE `message`
  ADD CONSTRAINT `message_ibfk_1` FOREIGN KEY (`conversation_id`) REFERENCES `conversation` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `message_ibfk_2` FOREIGN KEY (`expediteur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `offre_financiere`
--
ALTER TABLE `offre_financiere`
  ADD CONSTRAINT `fk_produit` FOREIGN KEY (`id_produit`) REFERENCES `produit_financier` (`id_produit`) ON DELETE CASCADE;

--
-- Contraintes pour la table `parametres2fa`
--
ALTER TABLE `parametres2fa`
  ADD CONSTRAINT `parametres2fa_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `piecejointe`
--
ALTER TABLE `piecejointe`
  ADD CONSTRAINT `piecejointe_ibfk_1` FOREIGN KEY (`message_id`) REFERENCES `message` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `projectagricole`
--
ALTER TABLE `projectagricole`
  ADD CONSTRAINT `fk_project_agriculteur` FOREIGN KEY (`agriculteur_id`) REFERENCES `agriculteur` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Contraintes pour la table `rapport_journalier`
--
ALTER TABLE `rapport_journalier`
  ADD CONSTRAINT `fk_rapport_projet` FOREIGN KEY (`idproject`) REFERENCES `projectagricole` (`idproject`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Contraintes pour la table `releve_terrain`
--
ALTER TABLE `releve_terrain`
  ADD CONSTRAINT `releve_terrain_ibfk_1` FOREIGN KEY (`id_capteur`) REFERENCES `capteur` (`id_capteur`) ON DELETE CASCADE;

--
-- Contraintes pour la table `ressourceproject`
--
ALTER TABLE `ressourceproject`
  ADD CONSTRAINT `fk_ressource_project` FOREIGN KEY (`idproject`) REFERENCES `projectagricole` (`idproject`);

--
-- Contraintes pour la table `statutenligne`
--
ALTER TABLE `statutenligne`
  ADD CONSTRAINT `statutenligne_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

--
-- Contraintes pour la table `tokenreinitialisation`
--
ALTER TABLE `tokenreinitialisation`
  ADD CONSTRAINT `tokenreinitialisation_ibfk_1` FOREIGN KEY (`utilisateur_id`) REFERENCES `utilisateur` (`id`) ON DELETE CASCADE;

DELIMITER $$
--
-- Évènements
--
CREATE DEFINER=`root`@`localhost` EVENT `nettoyer_codes_2fa_expires` ON SCHEDULE EVERY 1 HOUR STARTS '2026-02-09 16:51:34' ON COMPLETION NOT PRESERVE ENABLE DO DELETE FROM Code2FA 
  WHERE date_expiration < NOW() AND est_utilise = FALSE$$

DELIMITER ;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
