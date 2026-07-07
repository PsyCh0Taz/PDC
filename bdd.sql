-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 30, 2026 at 09:16 PM
-- Server version: 5.7.11
-- PHP Version: 5.6.18

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pdc_user`
--

-- --------------------------------------------------------

--
-- Table structure for table `domaines`
--

CREATE TABLE `pdc_domaines` (
  `id` int(10) UNSIGNED NOT NULL,
  `hierarchie_id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(150) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `domaines`
--

INSERT INTO `pdc_domaines` (`id`, `hierarchie_id`, `nom`, `ordre`) VALUES
(1, 5, 'IA', 0),
(2, 5, 'C2', 1),
(4, 7, 'ACCS', 1),
(5, 7, 'RADARS', 2),
(6, 4, 'ddd', 0);

-- --------------------------------------------------------

--
-- Table structure for table `hierarchie`
--

CREATE TABLE `pdc_hierarchie` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `id_parent` int(11) DEFAULT '0',
  `ordre` int(11) NOT NULL,
  `actif` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hierarchie`
--

INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES
(0, 'root', NULL, 1, 1),
(1, 'BA118', 0, 1, 1),
(2, 'BA942', 0, 2, 1),
(3, 'EC2SA', 1, 1, 1),
(4, 'ESIOC', 1, 2, 1),
(5, 'CCOA', 3, 1, 1),
(6, 'CTI', 3, 4, 1),
(7, 'CCNS', 3, 2, 1),
(8, 'EM.DSA', 3, 3, 1);

-- --------------------------------------------------------

--
-- Table structure for table `journal_connexions`
--

CREATE TABLE `pdc_journal_connexions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `date_heure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `via_partage` tinyint(1) NOT NULL DEFAULT '0',
  `share_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `journal_modifications`
--

CREATE TABLE `pdc_journal_modifications` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `date_heure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action` varchar(50) NOT NULL COMMENT 'CREATE/UPDATE/DELETE',
  `entite` varchar(50) NOT NULL COMMENT 'projet/jalon/gradient/domaine/etc.',
  `entite_id` int(10) UNSIGNED DEFAULT NULL,
  `description` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- --------------------------------------------------------

--
-- Table structure for table `parametres`
--

CREATE TABLE `pdc_parametres` (
  `cle` varchar(80) NOT NULL,
  `valeur` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `parametres`
--

INSERT INTO `pdc_parametres` (`cle`, `valeur`) VALUES
('logo_url', '/assets/uploads/logo_1780508091.svg'),
('pdf_logo', ''),
('pdf_titre', 'Plan de Charge'),
('titre_pdf', 'Plan de Charge');

-- --------------------------------------------------------

--
-- Table structure for table `projets`
--

CREATE TABLE `pdc_projets` (
  `id` int(10) UNSIGNED NOT NULL,
  `domaine_id` int(10) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `projets`
--

INSERT INTO `pdc_projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 'Agent C2 - 23', '2026-05-01', '2027-06-30', 0, '2026-05-12 19:15:34', '2026-06-30 22:49:00'),
(2, 1, 'Casque RA', '2026-03-01', '2026-10-30', 1, '2026-05-12 19:15:34', '2026-06-30 22:49:00'),
(3, 2, 'bcvbvcb', '2026-05-12', '2026-07-01', 1, '2026-06-01 21:25:54', '2026-06-03 18:52:14'),
(4, 6, 'fdgdsgd', '2026-06-02', '2026-06-12', 0, '2026-06-03 18:52:04', '2026-06-03 22:50:03'),
(5, 4, 'projet CCNS', '2026-01-01', '2026-12-31', 1, '2026-06-30 19:32:55', '2026-06-30 19:32:55');

-- --------------------------------------------------------

--
-- Table structure for table `projet_gradients`
--

CREATE TABLE `pdc_projet_gradients` (
  `id` int(10) UNSIGNED NOT NULL,
  `projet_id` int(10) UNSIGNED NOT NULL,
  `date_gradient` date NOT NULL,
  `couleur` enum('vert','jaune','orange','rouge') NOT NULL DEFAULT 'vert',
  `libelle` varchar(255) DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pdc_projet_gradients`
--

INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES
(445, 3, '2026-06-18', 'jaune', ''),
(446, 3, '2026-06-21', 'orange', ''),
(447, 3, '2026-06-24', 'rouge', ''),
(477, 1, '2026-04-27', 'rouge', ''),
(478, 1, '2026-05-04', 'orange', ''),
(479, 1, '2026-05-11', 'jaune', ''),
(480, 1, '2026-05-18', 'vert', ''),
(481, 1, '2026-05-25', 'jaune', ''),
(482, 1, '2026-06-01', 'orange', ''),
(483, 1, '2026-06-08', 'rouge', ''),
(484, 1, '2026-07-09', 'rouge', ''),
(485, 1, '2026-07-13', 'jaune', ''),
(486, 2, '2026-06-01', 'jaune', ''),
(487, 2, '2026-06-15', 'orange', ''),
(488, 2, '2026-06-22', 'rouge', '');

-- --------------------------------------------------------

--
-- Table structure for table `projet_jalons`
--

CREATE TABLE `pdc_projet_jalons` (
  `id` int(10) UNSIGNED NOT NULL,
  `projet_id` int(10) UNSIGNED NOT NULL,
  `date_jalon` date NOT NULL,
  `couleur` enum('vert','jaune','orange','rouge') NOT NULL DEFAULT 'vert',
  `libelle` varchar(255) NOT NULL DEFAULT '',
  `jalon_reference_id` int(10) UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `projet_jalons`
--

INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`) VALUES
(293, 3, '2026-05-03', 'vert', 'J1', NULL),
(294, 3, '2026-06-10', 'vert', 'J2', 293),
(334, 1, '2026-04-01', 'vert', 'J7', NULL),
(335, 1, '2026-05-07', 'vert', 'J1', NULL),
(336, 1, '2026-05-13', 'rouge', 'J5', NULL),
(337, 1, '2026-05-11', 'vert', 'J8', 334),
(338, 1, '2026-05-21', 'orange', 'J6', 336),
(339, 1, '2026-05-22', 'jaune', 'J2', 335),
(340, 1, '2026-05-27', 'orange', 'J3', NULL),
(341, 1, '2026-06-02', 'vert', 'J9', NULL),
(342, 1, '2026-06-08', 'rouge', 'J4', 340),
(343, 1, '2026-07-06', 'vert', 'J10 décalé', 341),
(344, 2, '2026-05-12', 'vert', 'J1', NULL),
(345, 2, '2026-05-22', 'orange', 'J2', 344),
(346, 2, '2026-05-30', 'vert', 'J3', 345);

-- --------------------------------------------------------

--
-- Table structure for table `share_links`
--

CREATE TABLE `pdc_share_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `url_params` text NOT NULL COMMENT 'Paramètres GET encodés (niveau, ids, période)',
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `pdc_utilisateurs` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `displayname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dn` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs`
--

INSERT INTO `pdc_utilisateurs` (`id`, `username`, `displayname`, `dn`, `email`, `date_creation`) VALUES
(1, 'taz', 'Taz', 'uid=taz,ou=direction,ou=users,dc=a,dc=c,dc=d,dc=fr', 'taz@taz.fr', '2026-06-01 19:00:46'),
(2, 'lcaron', 'Lucie Caron', 'uid=lcaron,ou=developpement,ou=informatique,ou=users,dc=a,dc=c,dc=d,dc=fr', 'lucie.caron@a.c.d.fr', '2026-06-30 19:41:37');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs_roles`
--

CREATE TABLE `pdc_utilisateurs_roles` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_dn` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','responsable','modificateur','lecteur') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs_roles`
--

INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES
(16, 'taz', '*', 'admin', '2026-06-30 20:10:34'),
(17, 'taz', 'hierarchie:1', 'lecteur', '2026-06-30 20:13:16'),
(19, 'taz', 'hierarchie:7', 'lecteur', '2026-06-30 20:13:18'),
(20, 'taz', 'hierarchie:5', 'modificateur', '2026-06-30 20:13:19'),
(22, 'taz', 'hierarchie:6', 'lecteur', '2026-06-30 20:13:22'),
(25, 'lcaron', 'hierarchie:1', 'lecteur', '2026-06-30 20:15:32'),
(26, 'lcaron', 'hierarchie:5', 'lecteur', '2026-06-30 21:06:49'),
(27, 'lcaron', 'hierarchie:7', 'lecteur', '2026-06-30 21:06:49'),
(28, 'lcaron', 'hierarchie:8', 'lecteur', '2026-06-30 21:06:50'),
(29, 'lcaron', 'hierarchie:6', 'lecteur', '2026-06-30 21:06:50'),
(30, 'lcaron', 'hierarchie:4', 'lecteur', '2026-06-30 21:06:50'),
(31, 'lcaron', 'hierarchie:2', 'lecteur', '2026-06-30 21:06:51'),
(32, 'lcaron', 'hierarchie:3', 'modificateur', '2026-06-30 21:07:08'),
(35, 'taz', 'hierarchie:3', 'lecteur', '2026-06-30 21:13:43'),
(36, 'taz', 'hierarchie:8', 'lecteur', '2026-06-30 21:13:44'),
(38, 'taz', 'hierarchie:2', 'modificateur', '2026-06-30 21:13:48'),
(39, 'taz', 'hierarchie:4', 'modificateur', '2026-06-30 21:13:55');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `domaines`
--
ALTER TABLE `pdc_domaines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dom_srv` (`hierarchie_id`);

--
-- Indexes for table `hierarchie`
--
ALTER TABLE `pdc_hierarchie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_parent` (`id_parent`);

--
-- Indexes for table `journal_connexions`
--
ALTER TABLE `pdc_journal_connexions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_date` (`date_heure`);

--
-- Indexes for table `journal_modifications`
--
ALTER TABLE `pdc_journal_modifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_date` (`date_heure`);

--
-- Indexes for table `parametres`
--
ALTER TABLE `pdc_parametres`
  ADD PRIMARY KEY (`cle`);

--
-- Indexes for table `projets`
--
ALTER TABLE `pdc_projets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proj_dom` (`domaine_id`);

--
-- Indexes for table `projet_gradients`
--
ALTER TABLE `pdc_projet_gradients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grad_proj` (`projet_id`);

--
-- Indexes for table `projet_jalons`
--
ALTER TABLE `pdc_projet_jalons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jalon_proj` (`projet_id`),
  ADD KEY `fk_jalon_ref` (`jalon_reference_id`);

--
-- Indexes for table `share_links`
--
ALTER TABLE `pdc_share_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `pdc_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `utilisateurs_roles`
--
ALTER TABLE `pdc_utilisateurs_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role` (`username`,`role_dn`,`role`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `domaines`
--
ALTER TABLE `pdc_domaines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `hierarchie`
--
ALTER TABLE `pdc_hierarchie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;
--
-- AUTO_INCREMENT for table `journal_connexions`
--
ALTER TABLE `pdc_journal_connexions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;
--
-- AUTO_INCREMENT for table `journal_modifications`
--
ALTER TABLE `pdc_journal_modifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=331;
--
-- AUTO_INCREMENT for table `projets`
--
ALTER TABLE `pdc_projets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `projet_gradients`
--
ALTER TABLE `pdc_projet_gradients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=489;
--
-- AUTO_INCREMENT for table `projet_jalons`
--
ALTER TABLE `pdc_projet_jalons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=347;
--
-- AUTO_INCREMENT for table `share_links`
--
ALTER TABLE `pdc_share_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `pdc_utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `utilisateurs_roles`
--
ALTER TABLE `pdc_utilisateurs_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=40;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `projets`
--
ALTER TABLE `pdc_projets`
  ADD CONSTRAINT `fk_proj_dom` FOREIGN KEY (`domaine_id`) REFERENCES `pdc_domaines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projet_gradients`
--
ALTER TABLE `pdc_projet_gradients`
  ADD CONSTRAINT `fk_grad_proj` FOREIGN KEY (`projet_id`) REFERENCES `pdc_projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projet_jalons`
--
ALTER TABLE `pdc_projet_jalons`
  ADD CONSTRAINT `fk_jalon_proj` FOREIGN KEY (`projet_id`) REFERENCES `pdc_projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `utilisateurs_roles`
--
ALTER TABLE `pdc_utilisateurs_roles`
  ADD CONSTRAINT `utilisateurs_roles_ibfk_1` FOREIGN KEY (`username`) REFERENCES `pdc_utilisateurs` (`username`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
