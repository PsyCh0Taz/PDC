-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jul 19, 2026 at 08:09 PM
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
CREATE DATABASE IF NOT EXISTS `pdc_user` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci;
USE `pdc_user`;

-- --------------------------------------------------------

--
-- Table structure for table `pdc_domaines`
--

DROP TABLE IF EXISTS `pdc_domaines`;
CREATE TABLE `pdc_domaines` (
  `id` int(10) UNSIGNED NOT NULL,
  `hierarchie_id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(150) NOT NULL,
  `commentaire` varchar(500) DEFAULT NULL,
  `ordre` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pdc_domaines`
--

INSERT INTO `pdc_domaines` (`id`, `hierarchie_id`, `nom`, `commentaire`, `ordre`) VALUES(1, 5, 'IA', 'Projet du futur', 0);
INSERT INTO `pdc_domaines` (`id`, `hierarchie_id`, `nom`, `commentaire`, `ordre`) VALUES(2, 5, 'C2', '<p><span style="background-color: #fbeeb8">000000000000000</span></p>', 1);
INSERT INTO `pdc_domaines` (`id`, `hierarchie_id`, `nom`, `commentaire`, `ordre`) VALUES(4, 7, 'ACCS', '', 1);
INSERT INTO `pdc_domaines` (`id`, `hierarchie_id`, `nom`, `commentaire`, `ordre`) VALUES(5, 7, 'RADARS', '', 2);
INSERT INTO `pdc_domaines` (`id`, `hierarchie_id`, `nom`, `commentaire`, `ordre`) VALUES(6, 4, 'ddd', '', 0);
INSERT INTO `pdc_domaines` (`id`, `hierarchie_id`, `nom`, `commentaire`, `ordre`) VALUES(7, 5, 'ggg', '', 2);

-- --------------------------------------------------------

--
-- Table structure for table `pdc_hierarchie`
--

DROP TABLE IF EXISTS `pdc_hierarchie`;
CREATE TABLE `pdc_hierarchie` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `id_parent` int(11) DEFAULT '0',
  `ordre` int(11) NOT NULL,
  `actif` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `pdc_hierarchie`
--

INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(0, 'root', NULL, 1, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(1, 'BA118', 0, 1, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(2, 'BA942', 0, 2, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(3, 'EC2SA', 1, 1, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(4, 'ESIOC', 1, 2, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(5, 'CCOA', 3, 1, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(6, 'CTI', 3, 3, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(7, 'CCNS', 3, 2, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(8, 'EM.DSA', 3, 4, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(9, 'BAT', 4, 1, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(10, 'CE CYBER', 1, 3, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(11, 'CDC', 1, 4, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(12, 'DACS', 10, 1, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(13, 'DETC', 10, 2, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(14, 'DEV', 5, 1, 1);
INSERT INTO `pdc_hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES(15, 'TECH', 5, 2, 1);

-- --------------------------------------------------------

--
-- Table structure for table `pdc_journal_connexions`
--

DROP TABLE IF EXISTS `pdc_journal_connexions`;
CREATE TABLE `pdc_journal_connexions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `date_heure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `via_partage` tinyint(1) NOT NULL DEFAULT '0',
  `share_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pdc_journal_connexions`
--

INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(1, 'taz', '127.0.0.1', '2026-05-08 20:10:50', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(2, 'taz', '127.0.0.1', '2026-05-10 16:18:04', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(3, 'taz', '127.0.0.1', '2026-05-12 19:10:30', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(4, 'taz', '127.0.0.1', '2026-05-14 18:38:52', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(5, 'taz', '127.0.0.1', '2026-05-22 18:21:53', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(6, 'taz', '127.0.0.1', '2026-05-27 21:13:50', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(7, 'tdd (échec)', '127.0.0.1', '2026-05-31 16:34:55', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(8, 'taz', '127.0.0.1', '2026-05-31 16:35:01', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(9, 'taz', '127.0.0.1', '2026-06-01 20:35:16', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(10, 'taz', '127.0.0.1', '2026-06-01 20:51:02', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(11, 'taz', '127.0.0.1', '2026-06-01 21:01:30', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(12, 'taz (échec)', '127.0.0.1', '2026-06-01 21:04:03', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(13, 'taz', '127.0.0.1', '2026-06-01 21:04:06', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(14, 'taz', '127.0.0.1', '2026-06-03 18:51:47', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(15, 'taz', '127.0.0.1', '2026-06-03 19:28:23', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(16, 'taz', '127.0.0.1', '2026-06-30 18:52:57', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(17, 'taz', '127.0.0.1', '2026-06-30 22:17:04', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(18, 'taz', '127.0.0.1', '2026-06-30 22:27:03', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(19, 'taz', '127.0.0.1', '2026-06-30 22:57:04', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(20, 'taz', '127.0.0.1', '2026-06-30 22:59:14', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(21, 'taz', '127.0.0.1', '2026-07-03 16:53:34', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(22, 'taz', '127.0.0.1', '2026-07-03 17:04:54', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(23, 'taz', '127.0.0.1', '2026-07-05 19:20:33', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(24, 'taz', '127.0.0.1', '2026-07-07 19:23:46', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(25, 'taz', '127.0.0.1', '2026-07-07 19:42:44', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(26, 'taz', '127.0.0.1', '2026-07-09 19:44:33', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(27, 'taz', '127.0.0.1', '2026-07-19 17:59:12', 0, NULL);
INSERT INTO `pdc_journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES(28, 'taz', '127.0.0.1', '2026-07-19 21:36:59', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pdc_journal_modifications`
--

DROP TABLE IF EXISTS `pdc_journal_modifications`;
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
-- Dumping data for table `pdc_journal_modifications`
--

INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(1, 'taz', '127.0.0.1', '2026-05-12 19:13:48', 'CREATE', 'domaine', 1, 'Création domaine : IA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(2, 'taz', '127.0.0.1', '2026-05-12 19:14:02', 'CREATE', 'domaine', 2, 'Création domaine : C2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(3, 'taz', '127.0.0.1', '2026-05-14 18:39:23', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=domaine, id=1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(4, 'taz', '127.0.0.1', '2026-05-22 18:25:08', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=domaine, id=1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(5, 'taz', '127.0.0.1', '2026-05-22 18:29:34', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=domaine, id=1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(6, 'taz', '127.0.0.1', '2026-05-22 21:31:32', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(7, 'taz', '127.0.0.1', '2026-05-22 21:31:40', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(8, 'taz', '127.0.0.1', '2026-05-22 21:36:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(9, 'taz', '127.0.0.1', '2026-05-22 21:36:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(10, 'taz', '127.0.0.1', '2026-05-22 21:36:36', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(11, 'taz', '127.0.0.1', '2026-05-22 21:38:01', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(12, 'taz', '127.0.0.1', '2026-05-22 21:38:01', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(13, 'taz', '127.0.0.1', '2026-05-22 21:38:01', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(14, 'taz', '127.0.0.1', '2026-05-22 21:39:00', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(15, 'taz', '127.0.0.1', '2026-05-22 21:39:03', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(16, 'taz', '127.0.0.1', '2026-05-22 21:39:05', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(17, 'taz', '127.0.0.1', '2026-05-22 21:39:49', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(18, 'taz', '127.0.0.1', '2026-05-22 21:40:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(19, 'taz', '127.0.0.1', '2026-05-22 21:40:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(20, 'taz', '127.0.0.1', '2026-05-22 21:41:12', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(21, 'taz', '127.0.0.1', '2026-05-22 21:41:12', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(22, 'taz', '127.0.0.1', '2026-05-22 21:41:12', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(23, 'taz', '127.0.0.1', '2026-05-22 21:41:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(24, 'taz', '127.0.0.1', '2026-05-22 21:41:34', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(25, 'taz', '127.0.0.1', '2026-05-22 21:41:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(26, 'taz', '127.0.0.1', '2026-05-22 21:42:41', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(27, 'taz', '127.0.0.1', '2026-05-22 21:42:41', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(28, 'taz', '127.0.0.1', '2026-05-22 21:42:41', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(29, 'taz', '127.0.0.1', '2026-05-22 21:42:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(30, 'taz', '127.0.0.1', '2026-05-22 21:42:45', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(31, 'taz', '127.0.0.1', '2026-05-22 21:42:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(32, 'taz', '127.0.0.1', '2026-05-22 21:42:52', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(33, 'taz', '127.0.0.1', '2026-05-22 21:42:52', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(34, 'taz', '127.0.0.1', '2026-05-22 21:42:52', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(35, 'taz', '127.0.0.1', '2026-05-22 21:43:23', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(36, 'taz', '127.0.0.1', '2026-05-22 21:43:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(37, 'taz', '127.0.0.1', '2026-05-22 21:43:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(38, 'taz', '127.0.0.1', '2026-05-22 21:43:34', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(39, 'taz', '127.0.0.1', '2026-05-22 21:43:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(40, 'taz', '127.0.0.1', '2026-05-22 21:43:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(41, 'taz', '127.0.0.1', '2026-05-22 21:43:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(42, 'taz', '127.0.0.1', '2026-05-22 21:43:36', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(43, 'taz', '127.0.0.1', '2026-05-22 21:43:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(44, 'taz', '127.0.0.1', '2026-05-22 21:44:07', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(45, 'taz', '127.0.0.1', '2026-05-22 21:44:11', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(46, 'taz', '127.0.0.1', '2026-05-22 21:44:21', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(47, 'taz', '127.0.0.1', '2026-05-22 21:44:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(48, 'taz', '127.0.0.1', '2026-05-22 21:44:40', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(49, 'taz', '127.0.0.1', '2026-05-22 21:44:40', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(50, 'taz', '127.0.0.1', '2026-05-22 21:44:40', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(51, 'taz', '127.0.0.1', '2026-05-22 21:44:45', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(52, 'taz', '127.0.0.1', '2026-05-22 21:44:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(53, 'taz', '127.0.0.1', '2026-05-22 21:44:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(54, 'taz', '127.0.0.1', '2026-05-22 21:45:28', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(55, 'taz', '127.0.0.1', '2026-05-22 21:45:31', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(56, 'taz', '127.0.0.1', '2026-05-22 21:46:18', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(57, 'taz', '127.0.0.1', '2026-05-22 21:47:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(58, 'taz', '127.0.0.1', '2026-05-22 21:47:10', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(59, 'taz', '127.0.0.1', '2026-05-22 21:47:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(60, 'taz', '127.0.0.1', '2026-05-22 21:47:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(61, 'taz', '127.0.0.1', '2026-05-22 21:47:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(62, 'taz', '127.0.0.1', '2026-05-22 21:47:35', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(63, 'taz', '127.0.0.1', '2026-05-22 21:47:37', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(64, 'taz', '127.0.0.1', '2026-05-22 21:47:37', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(65, 'taz', '127.0.0.1', '2026-05-22 21:47:38', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(66, 'taz', '127.0.0.1', '2026-05-22 21:48:10', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(67, 'taz', '127.0.0.1', '2026-05-22 21:48:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(68, 'taz', '127.0.0.1', '2026-05-22 21:48:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(69, 'taz', '127.0.0.1', '2026-05-22 21:48:27', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(70, 'taz', '127.0.0.1', '2026-05-22 21:48:27', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(71, 'taz', '127.0.0.1', '2026-05-22 21:48:27', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(72, 'taz', '127.0.0.1', '2026-05-22 21:48:32', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(73, 'taz', '127.0.0.1', '2026-05-22 21:48:32', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(74, 'taz', '127.0.0.1', '2026-05-22 21:48:32', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(75, 'taz', '127.0.0.1', '2026-05-22 21:48:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(76, 'taz', '127.0.0.1', '2026-05-22 21:48:59', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(77, 'taz', '127.0.0.1', '2026-05-22 21:49:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(78, 'taz', '127.0.0.1', '2026-05-22 21:49:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(79, 'taz', '127.0.0.1', '2026-05-22 21:49:14', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(80, 'taz', '127.0.0.1', '2026-05-22 21:49:29', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(81, 'taz', '127.0.0.1', '2026-05-22 21:49:29', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(82, 'taz', '127.0.0.1', '2026-05-22 21:49:29', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(83, 'taz', '127.0.0.1', '2026-05-22 21:49:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(84, 'taz', '127.0.0.1', '2026-05-22 21:49:39', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(85, 'taz', '127.0.0.1', '2026-05-22 21:50:58', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(86, 'taz', '127.0.0.1', '2026-05-22 21:51:56', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(87, 'taz', '127.0.0.1', '2026-05-22 21:52:30', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(88, 'taz', '127.0.0.1', '2026-05-22 21:52:30', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(89, 'taz', '127.0.0.1', '2026-05-22 21:52:30', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(90, 'taz', '127.0.0.1', '2026-05-22 21:52:35', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(91, 'taz', '127.0.0.1', '2026-05-22 21:52:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(92, 'taz', '127.0.0.1', '2026-05-22 21:52:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(93, 'taz', '127.0.0.1', '2026-05-22 21:52:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(94, 'taz', '127.0.0.1', '2026-05-22 21:52:51', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(95, 'taz', '127.0.0.1', '2026-05-22 21:52:53', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(96, 'taz', '127.0.0.1', '2026-05-22 21:52:53', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(97, 'taz', '127.0.0.1', '2026-05-22 21:52:53', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(98, 'taz', '127.0.0.1', '2026-05-22 21:52:55', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(99, 'taz', '127.0.0.1', '2026-05-22 21:52:55', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(100, 'taz', '127.0.0.1', '2026-05-22 21:52:55', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(101, 'taz', '127.0.0.1', '2026-05-22 21:53:11', 'CREATE', 'domaine', 3, 'Création domaine : C2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(102, 'taz', '127.0.0.1', '2026-05-22 21:54:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(103, 'taz', '127.0.0.1', '2026-05-22 21:54:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(104, 'taz', '127.0.0.1', '2026-05-22 21:54:19', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(105, 'taz', '127.0.0.1', '2026-05-22 21:54:26', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(106, 'taz', '127.0.0.1', '2026-05-22 21:54:29', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(107, 'taz', '127.0.0.1', '2026-05-22 21:56:15', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(108, 'taz', '127.0.0.1', '2026-05-22 21:56:15', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(109, 'taz', '127.0.0.1', '2026-05-22 21:56:15', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(110, 'taz', '127.0.0.1', '2026-05-22 21:57:11', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(111, 'taz', '127.0.0.1', '2026-05-22 21:57:11', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(112, 'taz', '127.0.0.1', '2026-05-22 21:57:11', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(113, 'taz', '127.0.0.1', '2026-05-22 21:57:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(114, 'taz', '127.0.0.1', '2026-05-22 21:57:19', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(115, 'taz', '127.0.0.1', '2026-05-22 21:57:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(116, 'taz', '127.0.0.1', '2026-05-22 21:58:18', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(117, 'taz', '127.0.0.1', '2026-05-22 21:58:26', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(118, 'taz', '127.0.0.1', '2026-05-22 22:24:44', 'UPDATE', 'domaine', 1, 'Modification domaine : IAss');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(119, 'taz', '127.0.0.1', '2026-05-22 22:24:51', 'UPDATE', 'domaine', 1, 'Modification domaine : IA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(120, 'taz', '127.0.0.1', '2026-05-22 22:32:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(121, 'taz', '127.0.0.1', '2026-05-22 22:32:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(122, 'taz', '127.0.0.1', '2026-05-22 22:32:35', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(123, 'taz', '127.0.0.1', '2026-05-22 22:32:41', 'DELETE', 'domaine', 3, 'Suppression domaine');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(124, 'taz', '127.0.0.1', '2026-05-27 21:20:10', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(125, 'taz', '127.0.0.1', '2026-05-27 21:22:37', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(126, 'taz', '127.0.0.1', '2026-05-27 21:26:04', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(127, 'taz', '127.0.0.1', '2026-05-27 21:26:30', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(128, 'taz', '127.0.0.1', '2026-05-27 21:29:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(129, 'taz', '127.0.0.1', '2026-05-27 21:30:34', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(130, 'taz', '127.0.0.1', '2026-05-27 21:30:42', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(131, 'taz', '127.0.0.1', '2026-05-27 21:30:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(132, 'taz', '127.0.0.1', '2026-05-27 21:35:00', 'UPDATE', 'domaine', 1, 'Modification domaine : IA2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(133, 'taz', '127.0.0.1', '2026-05-27 21:35:14', 'UPDATE', 'domaine', 1, 'Modification domaine : IA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(134, 'taz', '127.0.0.1', '2026-05-27 21:35:21', 'CREATE', 'domaine', 3, 'Création domaine : IA2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(135, 'taz', '127.0.0.1', '2026-05-27 21:35:26', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(136, 'taz', '127.0.0.1', '2026-05-27 21:35:31', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(137, 'taz', '127.0.0.1', '2026-05-27 21:36:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(138, 'taz', '127.0.0.1', '2026-05-27 21:36:16', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(139, 'taz', '127.0.0.1', '2026-05-27 21:36:32', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(140, 'taz', '127.0.0.1', '2026-05-27 21:37:02', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(141, 'taz', '127.0.0.1', '2026-05-27 21:42:01', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(142, 'taz', '127.0.0.1', '2026-05-27 21:55:00', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(143, 'taz', '127.0.0.1', '2026-05-27 21:58:03', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(144, 'taz', '127.0.0.1', '2026-05-27 21:58:35', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(145, 'taz', '127.0.0.1', '2026-05-27 21:59:09', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(146, 'taz', '127.0.0.1', '2026-05-27 21:59:48', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(147, 'taz', '127.0.0.1', '2026-05-27 22:00:27', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(148, 'taz', '127.0.0.1', '2026-05-27 22:00:58', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(149, 'taz', '127.0.0.1', '2026-05-27 22:04:58', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(150, 'taz', '127.0.0.1', '2026-05-27 22:26:41', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(151, 'taz', '127.0.0.1', '2026-05-27 22:27:10', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(152, 'taz', '127.0.0.1', '2026-05-27 22:28:21', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(153, 'taz', '127.0.0.1', '2026-05-27 22:29:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(154, 'taz', '127.0.0.1', '2026-05-27 22:48:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(155, 'taz', '127.0.0.1', '2026-05-27 22:48:32', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(156, 'taz', '127.0.0.1', '2026-05-27 22:49:22', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(157, 'taz', '127.0.0.1', '2026-05-31 16:41:18', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(158, 'taz', '127.0.0.1', '2026-05-31 16:41:37', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(159, 'taz', '127.0.0.1', '2026-05-31 16:44:57', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(160, 'taz', '127.0.0.1', '2026-05-31 16:45:54', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(161, 'taz', '127.0.0.1', '2026-05-31 16:54:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(162, 'taz', '127.0.0.1', '2026-05-31 16:55:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(163, 'taz', '127.0.0.1', '2026-05-31 16:57:19', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(164, 'taz', '127.0.0.1', '2026-05-31 16:57:25', 'UPDATE', 'domaine', 1, 'Modification domaine : IA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(165, 'taz', '127.0.0.1', '2026-05-31 16:57:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(166, 'taz', '127.0.0.1', '2026-05-31 16:58:23', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(167, 'taz', '127.0.0.1', '2026-05-31 16:58:54', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(168, 'taz', '127.0.0.1', '2026-05-31 16:59:12', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(169, 'taz', '127.0.0.1', '2026-05-31 17:00:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(170, 'taz', '127.0.0.1', '2026-05-31 17:04:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(171, 'taz', '127.0.0.1', '2026-05-31 17:04:18', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(172, 'taz', '127.0.0.1', '2026-05-31 17:07:19', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(173, 'taz', '127.0.0.1', '2026-05-31 17:09:15', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(174, 'taz', '127.0.0.1', '2026-05-31 17:11:11', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(175, 'taz', '127.0.0.1', '2026-05-31 17:16:26', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(176, 'taz', '127.0.0.1', '2026-05-31 17:19:45', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(177, 'taz', '127.0.0.1', '2026-05-31 17:20:19', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(178, 'taz', '127.0.0.1', '2026-05-31 17:20:38', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(179, 'taz', '127.0.0.1', '2026-05-31 17:20:57', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(180, 'taz', '127.0.0.1', '2026-05-31 17:22:26', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(181, 'taz', '127.0.0.1', '2026-05-31 17:41:20', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(182, 'taz', '127.0.0.1', '2026-05-31 17:41:38', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(183, 'taz', '127.0.0.1', '2026-05-31 17:50:20', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(184, 'taz', '127.0.0.1', '2026-05-31 17:54:50', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(185, 'taz', '127.0.0.1', '2026-05-31 17:55:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(186, 'taz', '127.0.0.1', '2026-05-31 18:33:32', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(187, 'taz', '127.0.0.1', '2026-05-31 18:33:38', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(188, 'taz', '127.0.0.1', '2026-05-31 18:33:52', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(189, 'taz', '127.0.0.1', '2026-05-31 18:34:17', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(190, 'taz', '127.0.0.1', '2026-05-31 18:41:55', 'DELETE', 'domaine', 3, 'Suppression domaine');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(191, 'taz', '127.0.0.1', '2026-05-31 18:41:58', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(192, 'taz', '127.0.0.1', '2026-05-31 18:41:58', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(193, 'taz', '127.0.0.1', '2026-05-31 18:41:58', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(194, 'taz', '127.0.0.1', '2026-05-31 18:52:27', 'CREATE', 'domaine', 4, 'Création domaine : ACCS');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(195, 'taz', '127.0.0.1', '2026-05-31 18:52:45', 'CREATE', 'domaine', 5, 'Création domaine : RADARS');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(196, 'taz', '127.0.0.1', '2026-05-31 19:15:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(197, 'taz', '127.0.0.1', '2026-05-31 19:15:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(198, 'taz', '127.0.0.1', '2026-05-31 19:15:23', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(199, 'taz', '127.0.0.1', '2026-05-31 19:15:56', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(200, 'taz', '127.0.0.1', '2026-05-31 19:15:56', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(201, 'taz', '127.0.0.1', '2026-05-31 19:15:56', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(202, 'taz', '127.0.0.1', '2026-05-31 19:29:51', 'CREATE', 'domaine', 6, 'Création domaine : ddd');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(203, 'taz', '127.0.0.1', '2026-05-31 19:30:03', 'UPDATE', 'projet', 0, 'Modification projet : dsfsqf');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(204, 'taz', '127.0.0.1', '2026-05-31 19:30:25', 'UPDATE', 'projet', 0, 'Modification projet : fffff');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(205, 'taz', '127.0.0.1', '2026-05-31 19:44:56', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(206, 'taz', '127.0.0.1', '2026-05-31 19:45:12', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(207, 'taz', '127.0.0.1', '2026-06-01 21:21:07', 'ASSIGN_ROLE', 'user', 0, 'Rôle \'responsable\' assigné à taz');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(208, 'taz', '127.0.0.1', '2026-06-01 21:25:54', 'CREATE', 'projet', 3, 'Création projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(209, 'taz', '127.0.0.1', '2026-06-01 21:27:09', 'CREATE', 'projet', 4, 'Création projet : hfhfdgh');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(210, 'taz', '127.0.0.1', '2026-06-01 21:27:15', 'DELETE', 'projet', 4, 'Suppression projet');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(211, 'taz', '127.0.0.1', '2026-06-01 21:27:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(212, 'taz', '127.0.0.1', '2026-06-01 21:27:19', 'UPDATE', 'projet', 3, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(213, 'taz', '127.0.0.1', '2026-06-01 21:27:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(214, 'taz', '127.0.0.1', '2026-06-01 21:27:44', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(215, 'taz', '127.0.0.1', '2026-06-01 21:40:17', 'CREATE', 'departement', 3, 'Création département : CAN');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(216, 'taz', '127.0.0.1', '2026-06-01 21:40:49', 'DELETE', 'departement', 3, 'Suppression département');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(217, 'taz', '127.0.0.1', '2026-06-01 21:47:38', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(218, 'taz', '127.0.0.1', '2026-06-01 21:48:05', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(219, 'taz', '127.0.0.1', '2026-06-01 21:48:29', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(220, 'taz', '127.0.0.1', '2026-06-01 21:48:39', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(221, 'taz', '127.0.0.1', '2026-06-01 21:49:02', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(222, 'taz', '127.0.0.1', '2026-06-03 18:52:04', 'CREATE', 'projet', 4, 'Création projet : fdgdsgd');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(223, 'taz', '127.0.0.1', '2026-06-03 18:52:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(224, 'taz', '127.0.0.1', '2026-06-03 18:52:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(225, 'taz', '127.0.0.1', '2026-06-03 18:52:14', 'UPDATE', 'projet', 4, 'Déplacement projet vers domaine 2');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(226, 'taz', '127.0.0.1', '2026-06-03 18:56:11', 'DESACTIVER', 'service', 3, 'Service CTI désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(227, 'taz', '127.0.0.1', '2026-06-03 18:56:26', 'ACTIVER', 'service', 3, 'Service CTI activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(228, 'taz', '127.0.0.1', '2026-06-03 19:24:53', 'DESACTIVER', 'service', 2, 'Service C2NS désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(229, 'taz', '127.0.0.1', '2026-06-03 19:25:11', 'DESACTIVER', 'service', 3, 'Service CTI désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(230, 'taz', '127.0.0.1', '2026-06-03 19:25:11', 'DESACTIVER', 'service', 4, 'Service EM.DSA désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(231, 'taz', '127.0.0.1', '2026-06-03 19:31:38', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(232, 'taz', '127.0.0.1', '2026-06-03 19:31:38', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(233, 'taz', '127.0.0.1', '2026-06-03 19:31:48', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(234, 'taz', '127.0.0.1', '2026-06-03 19:31:48', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(235, 'taz', '127.0.0.1', '2026-06-03 19:33:24', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(236, 'taz', '127.0.0.1', '2026-06-03 19:33:24', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(237, 'taz', '127.0.0.1', '2026-06-03 19:34:51', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(238, 'taz', '127.0.0.1', '2026-06-03 19:34:51', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(239, 'taz', '127.0.0.1', '2026-06-03 19:34:58', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=entreprise, id=');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(240, 'taz', '127.0.0.1', '2026-06-03 22:50:03', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(241, 'taz', '127.0.0.1', '2026-06-03 22:50:03', 'UPDATE', 'projet', 4, 'Déplacement projet vers domaine 6');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(242, 'taz', '127.0.0.1', '2026-06-03 22:50:03', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(243, 'taz', '127.0.0.1', '2026-06-03 23:29:26', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(244, 'taz', '127.0.0.1', '2026-06-03 23:29:36', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(245, 'taz', '127.0.0.1', '2026-06-03 23:30:06', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(246, 'taz', '127.0.0.1', '2026-06-03 23:30:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(247, 'taz', '127.0.0.1', '2026-06-03 23:31:12', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(248, 'taz', '127.0.0.1', '2026-06-30 19:18:27', 'DESACTIVER', 'hierarchie', 4, 'Niveau ESIOC désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(249, 'taz', '127.0.0.1', '2026-06-30 19:32:55', 'CREATE', 'projet', 5, 'Création projet : projet CCNS');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(250, 'taz', '127.0.0.1', '2026-06-30 19:35:41', 'DESACTIVER', 'hierarchie', 8, 'Niveau EM.DSA désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(251, 'taz', '127.0.0.1', '2026-06-30 19:35:46', 'ACTIVER', 'hierarchie', 8, 'Niveau EM.DSA activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(252, 'taz', '127.0.0.1', '2026-06-30 19:35:49', 'DESACTIVER', 'hierarchie', 3, 'Niveau EC2SA désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(253, 'taz', '127.0.0.1', '2026-06-30 19:38:42', 'ACTIVER', 'hierarchie', 3, 'Niveau EC2SA activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(254, 'taz', '127.0.0.1', '2026-06-30 19:38:46', 'DESACTIVER', 'hierarchie', 3, 'Niveau EC2SA désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(255, 'taz', '127.0.0.1', '2026-06-30 19:38:47', 'ACTIVER', 'hierarchie', 3, 'Niveau EC2SA activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(256, 'taz', '127.0.0.1', '2026-06-30 19:39:59', 'CREATE', 'domaine', 7, 'Création domaine : test');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(257, 'taz', '127.0.0.1', '2026-06-30 19:44:41', 'CREATE', 'domaine', 8, 'Création domaine : ssss');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(258, 'taz', '127.0.0.1', '2026-06-30 19:45:32', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(259, 'taz', '127.0.0.1', '2026-06-30 19:45:39', 'DESACTIVER', 'hierarchie', 3, 'Niveau EC2SA désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(260, 'taz', '127.0.0.1', '2026-06-30 19:46:36', 'ACTIVER', 'hierarchie', 3, 'Niveau EC2SA activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(261, 'taz', '127.0.0.1', '2026-06-30 19:46:37', 'DESACTIVER', 'hierarchie', 3, 'Niveau EC2SA désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(262, 'taz', '127.0.0.1', '2026-06-30 19:47:52', 'ACTIVER', 'hierarchie', 3, 'Niveau EC2SA activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(263, 'taz', '127.0.0.1', '2026-06-30 19:48:38', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(264, 'taz', '127.0.0.1', '2026-06-30 19:56:57', 'CREATE', 'projet', 6, 'Création projet : pr1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(265, 'taz', '127.0.0.1', '2026-06-30 19:57:07', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(266, 'taz', '127.0.0.1', '2026-06-30 20:01:05', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=undefined, id=5');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(267, 'taz', '127.0.0.1', '2026-06-30 20:10:56', 'DELETE', 'domaine', 8, 'Suppression domaine');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(268, 'taz', '127.0.0.1', '2026-06-30 20:11:03', 'DELETE', 'domaine', 7, 'Suppression domaine');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(269, 'taz', '127.0.0.1', '2026-06-30 20:12:27', 'ACTIVER', 'hierarchie', 4, 'Niveau ESIOC activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(270, 'taz', '127.0.0.1', '2026-06-30 20:21:49', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(271, 'taz', '127.0.0.1', '2026-06-30 21:41:37', 'ADD_USER', 'utilisateur', 0, 'Ajout/Maj utilisateur LDAP : lcaron');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(272, 'taz', '127.0.0.1', '2026-06-30 21:41:47', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:1\' pour taz : admin');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(273, 'taz', '127.0.0.1', '2026-06-30 21:41:48', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : admin');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(274, 'taz', '127.0.0.1', '2026-06-30 21:41:50', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:7\' pour taz : admin');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(275, 'taz', '127.0.0.1', '2026-06-30 21:41:51', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : admin');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(276, 'taz', '127.0.0.1', '2026-06-30 21:41:52', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:8\' pour taz : admin');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(277, 'taz', '127.0.0.1', '2026-06-30 21:41:54', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:6\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(278, 'taz', '127.0.0.1', '2026-06-30 22:08:56', 'MIGRATE_ADMIN_GLOBAL', 'utilisateurs_roles', 0, 'Migration des admins hiérarchiques vers admin global');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(279, 'taz', '127.0.0.1', '2026-06-30 22:10:34', 'SET_GLOBAL_ADMIN', 'user', 0, 'Admin global pour lcaron : activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(280, 'taz', '127.0.0.1', '2026-06-30 22:13:16', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:1\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(281, 'taz', '127.0.0.1', '2026-06-30 22:13:17', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(282, 'taz', '127.0.0.1', '2026-06-30 22:13:18', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:7\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(283, 'taz', '127.0.0.1', '2026-06-30 22:13:19', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(284, 'taz', '127.0.0.1', '2026-06-30 22:13:20', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:8\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(285, 'taz', '127.0.0.1', '2026-06-30 22:13:22', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:6\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(286, 'taz', '127.0.0.1', '2026-06-30 22:13:24', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(287, 'taz', '127.0.0.1', '2026-06-30 22:13:25', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(288, 'taz', '127.0.0.1', '2026-06-30 22:13:37', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(289, 'taz', '127.0.0.1', '2026-06-30 22:15:32', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:1\' pour lcaron : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(290, 'taz', '127.0.0.1', '2026-06-30 22:15:44', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(291, 'taz', '127.0.0.1', '2026-06-30 22:37:12', 'CREATE', 'hierarchie', 10, 'Création niveau hiérarchique : C4ISR');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(292, 'taz', '127.0.0.1', '2026-06-30 22:37:53', 'CREATE', 'hierarchie', 11, 'Création niveau hiérarchique : a');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(293, 'taz', '127.0.0.1', '2026-06-30 22:37:58', 'CREATE', 'hierarchie', 12, 'Création niveau hiérarchique : b');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(294, 'taz', '127.0.0.1', '2026-06-30 22:40:54', 'DESACTIVER', 'hierarchie', 7, 'Niveau CCNS désactivé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(295, 'taz', '127.0.0.1', '2026-06-30 22:40:55', 'ACTIVER', 'hierarchie', 7, 'Niveau CCNS activé');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(296, 'taz', '127.0.0.1', '2026-06-30 22:41:53', 'MOVE', 'hierarchie', 7, 'Déplacement niveau hiérarchique : CCNS (parent=3, ordre=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(297, 'taz', '127.0.0.1', '2026-06-30 22:42:10', 'MOVE', 'hierarchie', 12, 'Déplacement niveau hiérarchique : b (parent=10, ordre=0)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(298, 'taz', '127.0.0.1', '2026-06-30 22:42:14', 'MOVE', 'hierarchie', 11, 'Déplacement niveau hiérarchique : a (parent=10, ordre=0)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(299, 'taz', '127.0.0.1', '2026-06-30 22:43:06', 'DELETE', 'hierarchie', 11, 'Suppression niveau hiérarchique : a');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(300, 'taz', '127.0.0.1', '2026-06-30 22:44:14', 'DELETE', 'hierarchie', 12, 'Suppression niveau hiérarchique : b');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(301, 'taz', '127.0.0.1', '2026-06-30 22:44:23', 'CREATE', 'hierarchie', 13, 'Création niveau hiérarchique : sss');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(302, 'taz', '127.0.0.1', '2026-06-30 22:44:29', 'CREATE', 'hierarchie', 14, 'Création niveau hiérarchique : ssss');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(303, 'taz', '127.0.0.1', '2026-06-30 22:45:37', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(304, 'taz', '127.0.0.1', '2026-06-30 22:46:10', 'DELETE', 'hierarchie', 10, 'Suppression niveau hiérarchique : C4ISR (récursive)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(305, 'taz', '127.0.0.1', '2026-06-30 22:48:55', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(306, 'taz', '127.0.0.1', '2026-06-30 22:49:00', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(307, 'taz', '127.0.0.1', '2026-06-30 22:49:22', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(308, 'taz', '127.0.0.1', '2026-06-30 22:49:24', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(309, 'taz', '127.0.0.1', '2026-06-30 23:06:49', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour lcaron : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(310, 'taz', '127.0.0.1', '2026-06-30 23:06:49', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:7\' pour lcaron : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(311, 'taz', '127.0.0.1', '2026-06-30 23:06:50', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:8\' pour lcaron : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(312, 'taz', '127.0.0.1', '2026-06-30 23:06:50', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:6\' pour lcaron : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(313, 'taz', '127.0.0.1', '2026-06-30 23:06:50', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour lcaron : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(314, 'taz', '127.0.0.1', '2026-06-30 23:06:51', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour lcaron : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(315, 'taz', '127.0.0.1', '2026-06-30 23:07:08', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour lcaron : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(316, 'taz', '127.0.0.1', '2026-06-30 23:07:29', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(317, 'taz', '127.0.0.1', '2026-06-30 23:07:44', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(318, 'taz', '127.0.0.1', '2026-06-30 23:10:18', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(319, 'taz', '127.0.0.1', '2026-06-30 23:10:31', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(320, 'taz', '127.0.0.1', '2026-06-30 23:10:52', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(321, 'taz', '127.0.0.1', '2026-06-30 23:11:19', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(322, 'taz', '127.0.0.1', '2026-06-30 23:11:35', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 3)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(323, 'taz', '127.0.0.1', '2026-06-30 23:12:20', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(324, 'taz', '127.0.0.1', '2026-06-30 23:13:43', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(325, 'taz', '127.0.0.1', '2026-06-30 23:13:44', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:8\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(326, 'taz', '127.0.0.1', '2026-06-30 23:13:46', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(327, 'taz', '127.0.0.1', '2026-06-30 23:13:48', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(328, 'taz', '127.0.0.1', '2026-06-30 23:13:55', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(329, 'taz', '127.0.0.1', '2026-06-30 23:14:22', 'UPDATE', 'domaine', 6, 'Modification domaine : ddd (niveau: 4)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(330, 'taz', '127.0.0.1', '2026-06-30 23:14:49', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(331, 'taz', '127.0.0.1', '2026-07-03 17:05:19', 'ADD_USER', 'utilisateur', 0, 'Ajout/Maj utilisateur LDAP : arobin');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(332, 'taz', '127.0.0.1', '2026-07-03 17:05:24', 'DELETE_USER', 'utilisateur', 0, 'Suppression utilisateur : lcaron');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(333, 'taz', '127.0.0.1', '2026-07-03 18:13:13', 'CREATE', 'domaine', 7, 'Création domaine : ggg');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(334, 'taz', '127.0.0.1', '2026-07-03 18:13:21', 'CREATE', 'projet', 6, 'Création projet : ggggggggg');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(335, 'taz', '127.0.0.1', '2026-07-03 18:13:31', 'UPDATE', 'projet', 6, 'Modification projet : ggggggggg');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(336, 'taz', '127.0.0.1', '2026-07-05 19:20:54', 'MOVE', 'hierarchie', 8, 'Déplacement niveau hiérarchique : EM.DSA (parent=3, ordre=3)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(337, 'taz', '127.0.0.1', '2026-07-07 19:50:23', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:1\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(338, 'taz', '127.0.0.1', '2026-07-07 19:50:24', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(339, 'taz', '127.0.0.1', '2026-07-07 19:50:25', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(340, 'taz', '127.0.0.1', '2026-07-07 19:50:26', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(341, 'taz', '127.0.0.1', '2026-07-07 19:50:26', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:7\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(342, 'taz', '127.0.0.1', '2026-07-07 19:50:27', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:8\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(343, 'taz', '127.0.0.1', '2026-07-07 19:50:28', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(344, 'taz', '127.0.0.1', '2026-07-07 19:50:28', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(345, 'taz', '127.0.0.1', '2026-07-07 20:50:09', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(346, 'taz', '127.0.0.1', '2026-07-07 20:57:31', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(347, 'taz', '127.0.0.1', '2026-07-07 21:12:39', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(348, 'taz', '127.0.0.1', '2026-07-07 21:16:39', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(349, 'taz', '127.0.0.1', '2026-07-07 21:19:25', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(350, 'taz', '127.0.0.1', '2026-07-07 21:19:50', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(351, 'taz', '127.0.0.1', '2026-07-07 21:20:33', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(352, 'taz', '127.0.0.1', '2026-07-07 21:24:14', 'CREATE', 'hierarchie', 9, 'Création niveau hiérarchique : BAT');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(353, 'taz', '127.0.0.1', '2026-07-07 21:24:20', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:9\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(354, 'taz', '127.0.0.1', '2026-07-09 19:49:11', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(355, 'taz', '127.0.0.1', '2026-07-09 19:53:00', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(356, 'taz', '127.0.0.1', '2026-07-09 19:56:31', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(357, 'taz', '127.0.0.1', '2026-07-09 20:00:14', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(358, 'taz', '127.0.0.1', '2026-07-09 20:01:41', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(359, 'taz', '127.0.0.1', '2026-07-09 20:03:08', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(360, 'taz', '127.0.0.1', '2026-07-09 20:03:09', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(361, 'taz', '127.0.0.1', '2026-07-09 20:03:16', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(362, 'taz', '127.0.0.1', '2026-07-09 20:04:30', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(363, 'taz', '127.0.0.1', '2026-07-09 20:05:15', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(364, 'taz', '127.0.0.1', '2026-07-09 20:06:16', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(365, 'taz', '127.0.0.1', '2026-07-09 20:06:29', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(366, 'taz', '127.0.0.1', '2026-07-09 20:07:59', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(367, 'taz', '127.0.0.1', '2026-07-09 20:08:40', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(368, 'taz', '127.0.0.1', '2026-07-09 20:09:24', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(369, 'taz', '127.0.0.1', '2026-07-09 20:10:22', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(370, 'taz', '127.0.0.1', '2026-07-09 20:11:01', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(371, 'taz', '127.0.0.1', '2026-07-09 20:11:15', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(372, 'taz', '127.0.0.1', '2026-07-09 20:16:14', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(373, 'taz', '127.0.0.1', '2026-07-09 20:20:35', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(374, 'taz', '127.0.0.1', '2026-07-09 21:01:34', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(375, 'taz', '127.0.0.1', '2026-07-09 21:02:55', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(376, 'taz', '127.0.0.1', '2026-07-09 21:02:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(377, 'taz', '127.0.0.1', '2026-07-09 21:03:44', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(378, 'taz', '127.0.0.1', '2026-07-09 21:04:21', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(379, 'taz', '127.0.0.1', '2026-07-09 21:04:26', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(380, 'taz', '127.0.0.1', '2026-07-09 21:04:33', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(381, 'taz', '127.0.0.1', '2026-07-09 21:04:35', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(382, 'taz', '127.0.0.1', '2026-07-09 21:04:44', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(383, 'taz', '127.0.0.1', '2026-07-09 21:14:54', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(384, 'taz', '127.0.0.1', '2026-07-09 21:17:53', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(385, 'taz', '127.0.0.1', '2026-07-09 21:18:12', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(386, 'taz', '127.0.0.1', '2026-07-09 21:18:33', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(387, 'taz', '127.0.0.1', '2026-07-09 21:18:51', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(388, 'taz', '127.0.0.1', '2026-07-09 21:19:30', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(389, 'taz', '127.0.0.1', '2026-07-09 21:19:52', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(390, 'taz', '127.0.0.1', '2026-07-09 21:46:15', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(391, 'taz', '127.0.0.1', '2026-07-09 21:46:43', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(392, 'taz', '127.0.0.1', '2026-07-09 21:47:35', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(393, 'taz', '127.0.0.1', '2026-07-09 21:49:51', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(394, 'taz', '127.0.0.1', '2026-07-09 21:50:03', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(395, 'taz', '127.0.0.1', '2026-07-09 21:50:15', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(396, 'taz', '127.0.0.1', '2026-07-09 21:53:35', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(397, 'taz', '127.0.0.1', '2026-07-09 21:53:44', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=0, jalons=0)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(398, 'taz', '127.0.0.1', '2026-07-09 21:57:14', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=0, jalons=0)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(399, 'taz', '127.0.0.1', '2026-07-09 21:57:58', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(400, 'taz', '127.0.0.1', '2026-07-09 22:02:49', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(401, 'taz', '127.0.0.1', '2026-07-09 22:02:51', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(402, 'taz', '127.0.0.1', '2026-07-09 22:03:07', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(403, 'taz', '127.0.0.1', '2026-07-09 22:06:06', 'EXPORT', 'pdf_server', 5, 'Export PDF serveur niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(404, 'taz', '127.0.0.1', '2026-07-09 22:06:31', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(405, 'taz', '127.0.0.1', '2026-07-09 22:07:44', 'EXPORT', 'pdf_server', 5, 'Export PDF serveur niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(406, 'taz', '127.0.0.1', '2026-07-09 22:08:23', 'EXPORT', 'pdf_server', 5, 'Export PDF serveur niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(407, 'taz', '127.0.0.1', '2026-07-09 22:09:47', 'EXPORT', 'pdf_server', 5, 'Export PDF serveur niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(408, 'taz', '127.0.0.1', '2026-07-09 22:11:07', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(409, 'taz', '127.0.0.1', '2026-07-19 18:07:33', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(410, 'taz', '127.0.0.1', '2026-07-19 18:08:55', 'CREATE', 'domaine', 8, 'Création domaine : Domaine_EC2SA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(411, 'taz', '127.0.0.1', '2026-07-19 18:13:03', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(412, 'taz', '127.0.0.1', '2026-07-19 19:03:16', 'UPDATE', 'domaine', 2, 'Modification domaine : C2 (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(413, 'taz', '127.0.0.1', '2026-07-19 19:06:44', 'DELETE', 'domaine', 8, 'Suppression domaine');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(414, 'taz', '127.0.0.1', '2026-07-19 19:07:15', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(415, 'taz', '127.0.0.1', '2026-07-19 19:07:16', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(416, 'taz', '127.0.0.1', '2026-07-19 19:07:40', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(417, 'taz', '127.0.0.1', '2026-07-19 19:17:23', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(418, 'taz', '127.0.0.1', '2026-07-19 19:19:45', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(419, 'taz', '127.0.0.1', '2026-07-19 19:21:31', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(420, 'taz', '127.0.0.1', '2026-07-19 19:21:35', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 7');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(421, 'taz', '127.0.0.1', '2026-07-19 19:21:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(422, 'taz', '127.0.0.1', '2026-07-19 19:21:39', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(423, 'taz', '127.0.0.1', '2026-07-19 19:21:39', 'UPDATE', 'projets', NULL, 'Réorganisation projets');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(424, 'taz', '127.0.0.1', '2026-07-19 19:21:54', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 9)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(425, 'taz', '127.0.0.1', '2026-07-19 19:22:11', 'UPDATE', 'domaine', 1, 'Modification domaine : IA (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(426, 'taz', '127.0.0.1', '2026-07-19 19:48:26', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(427, 'taz', '127.0.0.1', '2026-07-19 19:48:28', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(428, 'taz', '127.0.0.1', '2026-07-19 20:26:23', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(429, 'taz', '127.0.0.1', '2026-07-19 20:26:24', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:7\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(430, 'taz', '127.0.0.1', '2026-07-19 20:26:25', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:8\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(431, 'taz', '127.0.0.1', '2026-07-19 20:26:25', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(432, 'taz', '127.0.0.1', '2026-07-19 20:26:26', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(433, 'taz', '127.0.0.1', '2026-07-19 20:26:27', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:9\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(434, 'taz', '127.0.0.1', '2026-07-19 20:26:42', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(435, 'taz', '127.0.0.1', '2026-07-19 20:26:43', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:9\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(436, 'taz', '127.0.0.1', '2026-07-19 20:26:44', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(437, 'taz', '127.0.0.1', '2026-07-19 20:26:46', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:8\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(438, 'taz', '127.0.0.1', '2026-07-19 20:26:49', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(439, 'taz', '127.0.0.1', '2026-07-19 20:26:49', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:7\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(440, 'taz', '127.0.0.1', '2026-07-19 20:26:50', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:6\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(441, 'taz', '127.0.0.1', '2026-07-19 20:27:37', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(442, 'taz', '127.0.0.1', '2026-07-19 20:27:38', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(443, 'taz', '127.0.0.1', '2026-07-19 20:27:38', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:9\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(444, 'taz', '127.0.0.1', '2026-07-19 20:29:24', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:1\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(445, 'taz', '127.0.0.1', '2026-07-19 20:36:06', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:7\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(446, 'taz', '127.0.0.1', '2026-07-19 20:37:06', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(447, 'taz', '127.0.0.1', '2026-07-19 20:37:41', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(448, 'taz', '127.0.0.1', '2026-07-19 20:37:57', 'UPDATE', 'projet', 6, 'Modification projet : ggggggggg');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(449, 'taz', '127.0.0.1', '2026-07-19 20:38:21', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(450, 'taz', '127.0.0.1', '2026-07-19 21:11:02', 'UPDATE', 'domaine', 2, 'Modification domaine : C2 (niveau: 5)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(451, 'taz', '127.0.0.1', '2026-07-19 21:11:16', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(452, 'taz', '127.0.0.1', '2026-07-19 21:19:44', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(453, 'taz', '127.0.0.1', '2026-07-19 21:27:54', 'CREATE', 'domaine', 8, 'Création domaine : fdsqfqsdf');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(454, 'taz', '127.0.0.1', '2026-07-19 21:28:04', 'DELETE', 'domaine', 8, 'Suppression domaine');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(455, 'taz', '127.0.0.1', '2026-07-19 21:31:35', 'UPDATE', 'projet', 1, 'Mise à jour des jalons');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(456, 'taz', '127.0.0.1', '2026-07-19 21:33:25', 'EXPORT', 'pdf', 5, 'Export PDF niveau hierarchie: CCOA (gradients=1, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(457, 'taz', '127.0.0.1', '2026-07-19 21:35:37', 'EXPORT', 'pdf', 3, 'Export PDF niveau hierarchie: EC2SA (gradients=0, jalons=1)');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(458, 'taz', '127.0.0.1', '2026-07-19 21:43:39', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:3\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(459, 'taz', '127.0.0.1', '2026-07-19 21:45:57', 'UPDATE', 'hierarchie', 1, 'Renommage niveau hiérarchique : BA118 -> CE CYBER');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(460, 'taz', '127.0.0.1', '2026-07-19 21:46:09', 'UPDATE', 'hierarchie', 1, 'Renommage niveau hiérarchique : CE CYBER -> B1118');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(461, 'taz', '127.0.0.1', '2026-07-19 21:46:15', 'UPDATE', 'hierarchie', 1, 'Renommage niveau hiérarchique : B1118 -> BA118');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(462, 'taz', '127.0.0.1', '2026-07-19 21:46:20', 'CREATE', 'hierarchie', 10, 'Création niveau hiérarchique : CE CYBER');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(463, 'taz', '127.0.0.1', '2026-07-19 21:46:30', 'CREATE', 'hierarchie', 11, 'Création niveau hiérarchique : CDC');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(464, 'taz', '127.0.0.1', '2026-07-19 21:46:54', 'CREATE', 'hierarchie', 12, 'Création niveau hiérarchique : DACS');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(465, 'taz', '127.0.0.1', '2026-07-19 21:47:04', 'CREATE', 'hierarchie', 13, 'Création niveau hiérarchique : DETC');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(466, 'taz', '127.0.0.1', '2026-07-19 21:48:26', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:12\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(467, 'taz', '127.0.0.1', '2026-07-19 21:48:27', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:13\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(468, 'taz', '127.0.0.1', '2026-07-19 21:48:28', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:10\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(469, 'taz', '127.0.0.1', '2026-07-19 21:48:30', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:11\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(470, 'taz', '127.0.0.1', '2026-07-19 21:48:30', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:2\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(471, 'taz', '127.0.0.1', '2026-07-19 21:48:32', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:4\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(472, 'taz', '127.0.0.1', '2026-07-19 21:48:33', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:9\' pour taz : lecteur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(473, 'taz', '127.0.0.1', '2026-07-19 21:51:32', 'CREATE', 'hierarchie', 14, 'Création niveau hiérarchique : DEV');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(474, 'taz', '127.0.0.1', '2026-07-19 21:51:39', 'CREATE', 'hierarchie', 15, 'Création niveau hiérarchique : TECH');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(475, 'taz', '127.0.0.1', '2026-07-19 21:52:16', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(476, 'taz', '127.0.0.1', '2026-07-19 21:52:18', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:5\' pour taz : modificateur');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(477, 'taz', '127.0.0.1', '2026-07-19 21:54:18', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:10\' pour taz : aucun');
INSERT INTO `pdc_journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES(478, 'taz', '127.0.0.1', '2026-07-19 22:01:49', 'SET_SCOPE_ROLE', 'user', 0, 'Rôle scope \'hierarchie:15\' pour taz : lecteur');

-- --------------------------------------------------------

--
-- Table structure for table `pdc_parametres`
--

DROP TABLE IF EXISTS `pdc_parametres`;
CREATE TABLE `pdc_parametres` (
  `cle` varchar(80) NOT NULL,
  `valeur` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pdc_parametres`
--

INSERT INTO `pdc_parametres` (`cle`, `valeur`) VALUES('logo_url', '/assets/uploads/logo_1780508091.svg');
INSERT INTO `pdc_parametres` (`cle`, `valeur`) VALUES('pdf_logo', '');
INSERT INTO `pdc_parametres` (`cle`, `valeur`) VALUES('pdf_titre', 'Plan de Charge');
INSERT INTO `pdc_parametres` (`cle`, `valeur`) VALUES('titre_pdf', 'Plan de Charge');

-- --------------------------------------------------------

--
-- Table structure for table `pdc_projets`
--

DROP TABLE IF EXISTS `pdc_projets`;
CREATE TABLE `pdc_projets` (
  `id` int(10) UNSIGNED NOT NULL,
  `domaine_id` int(10) UNSIGNED NOT NULL,
  `titre` varchar(200) NOT NULL,
  `date_debut` date NOT NULL,
  `date_fin` date NOT NULL,
  `commentaires` varchar(500) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT '0',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pdc_projets`
--

INSERT INTO `pdc_projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `commentaires`, `ordre`, `created_at`, `updated_at`) VALUES(1, 1, 'Agent C2 - 23', '2026-05-01', '2027-06-30', '<p>Le commentaire de la mort qui tue</p>\n<p><span style="color: #f1c40f; background-color: #3598db">dfsgfdsgfdsgdsfgdsf</span></p>', 0, '2026-05-12 19:15:34', '2026-07-19 19:21:31');
INSERT INTO `pdc_projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `commentaires`, `ordre`, `created_at`, `updated_at`) VALUES(2, 1, 'Casque RA', '2026-03-01', '2026-10-30', '<p>dfsgdfgdsfgdsfgdsgdfsgdfsgfdgdsfgdsfgdsfgddsfgdsfgfdsgdsfgdsfgdsgdsfg</p>', 1, '2026-05-12 19:15:34', '2026-07-19 19:21:39');
INSERT INTO `pdc_projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `commentaires`, `ordre`, `created_at`, `updated_at`) VALUES(3, 2, 'bcvbvcb', '2026-05-12', '2026-07-01', '<p><span style="background-color: #3598db">aaaaaaaaaaaaaa</span></p>', 1, '2026-06-01 21:25:54', '2026-07-19 21:11:16');
INSERT INTO `pdc_projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `commentaires`, `ordre`, `created_at`, `updated_at`) VALUES(4, 6, 'fdgdsgd', '2026-06-02', '2026-06-12', '', 0, '2026-06-03 18:52:04', '2026-06-03 22:50:03');
INSERT INTO `pdc_projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `commentaires`, `ordre`, `created_at`, `updated_at`) VALUES(5, 4, 'projet CCNS', '2026-01-01', '2026-12-31', '', 1, '2026-06-30 19:32:55', '2026-06-30 19:32:55');
INSERT INTO `pdc_projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `commentaires`, `ordre`, `created_at`, `updated_at`) VALUES(6, 7, 'ggggggggg', '2026-07-06', '2026-07-25', '', 1, '2026-07-03 18:13:21', '2026-07-03 18:13:21');

-- --------------------------------------------------------

--
-- Table structure for table `pdc_projet_gradients`
--

DROP TABLE IF EXISTS `pdc_projet_gradients`;
CREATE TABLE `pdc_projet_gradients` (
  `id` int(10) UNSIGNED NOT NULL,
  `projet_id` int(10) UNSIGNED NOT NULL,
  `date_gradient` date NOT NULL,
  `couleur` enum('vert','jaune','orange','rouge') NOT NULL DEFAULT 'vert',
  `libelle` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pdc_projet_gradients`
--

INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(712, 2, '2026-06-01', 'jaune', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(713, 2, '2026-06-15', 'orange', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(714, 2, '2026-06-22', 'rouge', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(715, 2, '2026-07-30', 'vert', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(724, 6, '2026-07-07', 'orange', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(725, 1, '2026-04-27', 'rouge', 'gdgddqsfqsd fdsqfqs');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(726, 1, '2026-05-04', 'orange', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(727, 1, '2026-05-11', 'jaune', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(728, 1, '2026-05-18', 'vert', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(729, 1, '2026-05-25', 'jaune', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(730, 1, '2026-06-01', 'orange', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(731, 1, '2026-06-08', 'rouge', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(732, 1, '2026-07-09', 'rouge', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(733, 1, '2026-07-13', 'jaune', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(734, 3, '2026-05-30', 'jaune', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(735, 3, '2026-06-18', 'jaune', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(736, 3, '2026-06-21', 'orange', '');
INSERT INTO `pdc_projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`, `libelle`) VALUES(737, 3, '2026-06-24', 'rouge', '');

-- --------------------------------------------------------

--
-- Table structure for table `pdc_projet_jalons`
--

DROP TABLE IF EXISTS `pdc_projet_jalons`;
CREATE TABLE `pdc_projet_jalons` (
  `id` int(10) UNSIGNED NOT NULL,
  `projet_id` int(10) UNSIGNED NOT NULL,
  `date_jalon` date NOT NULL,
  `couleur` enum('vert','jaune','orange','rouge') NOT NULL DEFAULT 'vert',
  `libelle` varchar(255) NOT NULL DEFAULT '',
  `jalon_reference_id` int(10) UNSIGNED DEFAULT NULL,
  `commentaire` varchar(500) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `pdc_projet_jalons`
--

INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(615, 2, '2026-05-12', 'vert', 'J1', NULL, 'Premier jalon de la frise');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(616, 2, '2026-05-22', 'orange', 'J2', 615, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(617, 2, '2026-05-30', 'vert', 'J3', 616, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(633, 3, '2026-05-03', 'vert', 'J1', NULL, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(634, 3, '2026-06-10', 'vert', 'J2', 633, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(635, 1, '2026-04-01', 'vert', 'J7', NULL, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(636, 1, '2026-05-07', 'vert', 'J1', NULL, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(637, 1, '2026-05-11', 'vert', 'J8', 635, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(638, 1, '2026-05-13', 'rouge', 'J5', 637, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(639, 1, '2026-05-21', 'orange', 'J6', 638, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(640, 1, '2026-05-22', 'jaune', 'J2', 636, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(641, 1, '2026-05-27', 'orange', 'J3', NULL, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(642, 1, '2026-06-02', 'vert', 'J9', 645, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(643, 1, '2026-06-08', 'rouge', 'J4', 641, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(644, 1, '2026-07-06', 'vert', 'J10 décalé', 642, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(645, 1, '2026-07-18', 'rouge', 'J11', NULL, '');
INSERT INTO `pdc_projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`, `commentaire`) VALUES(646, 1, '2026-06-16', 'orange', 'Taz', NULL, '');

-- --------------------------------------------------------

--
-- Table structure for table `pdc_share_links`
--

DROP TABLE IF EXISTS `pdc_share_links`;
CREATE TABLE `pdc_share_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `url_params` text NOT NULL COMMENT 'Paramètres GET encodés (niveau, ids, période)',
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `pdc_utilisateurs`
--

DROP TABLE IF EXISTS `pdc_utilisateurs`;
CREATE TABLE `pdc_utilisateurs` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `displayname` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `dn` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pdc_utilisateurs`
--

INSERT INTO `pdc_utilisateurs` (`id`, `username`, `displayname`, `dn`, `email`, `date_creation`) VALUES(4, 'taz', 'aa', 'aa', 'aa@', '2026-07-07 17:50:12');

-- --------------------------------------------------------

--
-- Table structure for table `pdc_utilisateurs_roles`
--

DROP TABLE IF EXISTS `pdc_utilisateurs_roles`;
CREATE TABLE `pdc_utilisateurs_roles` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_dn` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','responsable','modificateur','lecteur') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `pdc_utilisateurs_roles`
--

INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(41, 'taz', '*', 'admin', '2026-07-07 17:50:15');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(58, 'taz', 'hierarchie:8', 'lecteur', '2026-07-19 18:26:46');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(61, 'taz', 'hierarchie:6', 'modificateur', '2026-07-19 18:26:50');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(62, 'taz', 'hierarchie:7', 'lecteur', '2026-07-19 18:36:06');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(63, 'taz', 'hierarchie:3', 'modificateur', '2026-07-19 19:43:39');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(64, 'taz', 'hierarchie:12', 'modificateur', '2026-07-19 19:48:26');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(65, 'taz', 'hierarchie:13', 'modificateur', '2026-07-19 19:48:27');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(67, 'taz', 'hierarchie:11', 'lecteur', '2026-07-19 19:48:30');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(68, 'taz', 'hierarchie:2', 'lecteur', '2026-07-19 19:48:30');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(69, 'taz', 'hierarchie:4', 'lecteur', '2026-07-19 19:48:32');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(70, 'taz', 'hierarchie:9', 'lecteur', '2026-07-19 19:48:33');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(71, 'taz', 'hierarchie:5', 'modificateur', '2026-07-19 19:52:18');
INSERT INTO `pdc_utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES(72, 'taz', 'hierarchie:15', 'lecteur', '2026-07-19 20:01:49');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pdc_domaines`
--
ALTER TABLE `pdc_domaines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dom_srv` (`hierarchie_id`);

--
-- Indexes for table `pdc_hierarchie`
--
ALTER TABLE `pdc_hierarchie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_parent` (`id_parent`);

--
-- Indexes for table `pdc_journal_connexions`
--
ALTER TABLE `pdc_journal_connexions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_date` (`date_heure`);

--
-- Indexes for table `pdc_journal_modifications`
--
ALTER TABLE `pdc_journal_modifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_date` (`date_heure`);

--
-- Indexes for table `pdc_parametres`
--
ALTER TABLE `pdc_parametres`
  ADD PRIMARY KEY (`cle`);

--
-- Indexes for table `pdc_projets`
--
ALTER TABLE `pdc_projets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proj_dom` (`domaine_id`);

--
-- Indexes for table `pdc_projet_gradients`
--
ALTER TABLE `pdc_projet_gradients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grad_proj` (`projet_id`);

--
-- Indexes for table `pdc_projet_jalons`
--
ALTER TABLE `pdc_projet_jalons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jalon_proj` (`projet_id`),
  ADD KEY `fk_jalon_ref` (`jalon_reference_id`);

--
-- Indexes for table `pdc_share_links`
--
ALTER TABLE `pdc_share_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `pdc_utilisateurs`
--
ALTER TABLE `pdc_utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `pdc_utilisateurs_roles`
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
-- AUTO_INCREMENT for table `pdc_domaines`
--
ALTER TABLE `pdc_domaines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `pdc_hierarchie`
--
ALTER TABLE `pdc_hierarchie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `pdc_journal_connexions`
--
ALTER TABLE `pdc_journal_connexions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
--
-- AUTO_INCREMENT for table `pdc_journal_modifications`
--
ALTER TABLE `pdc_journal_modifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=479;
--
-- AUTO_INCREMENT for table `pdc_projets`
--
ALTER TABLE `pdc_projets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `pdc_projet_gradients`
--
ALTER TABLE `pdc_projet_gradients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=738;
--
-- AUTO_INCREMENT for table `pdc_projet_jalons`
--
ALTER TABLE `pdc_projet_jalons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=647;
--
-- AUTO_INCREMENT for table `pdc_share_links`
--
ALTER TABLE `pdc_share_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `pdc_utilisateurs`
--
ALTER TABLE `pdc_utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `pdc_utilisateurs_roles`
--
ALTER TABLE `pdc_utilisateurs_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=73;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `pdc_projets`
--
ALTER TABLE `pdc_projets`
  ADD CONSTRAINT `fk_proj_dom` FOREIGN KEY (`domaine_id`) REFERENCES `pdc_domaines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pdc_projet_gradients`
--
ALTER TABLE `pdc_projet_gradients`
  ADD CONSTRAINT `fk_grad_proj` FOREIGN KEY (`projet_id`) REFERENCES `pdc_projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pdc_projet_jalons`
--
ALTER TABLE `pdc_projet_jalons`
  ADD CONSTRAINT `fk_jalon_proj` FOREIGN KEY (`projet_id`) REFERENCES `pdc_projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `pdc_utilisateurs_roles`
--
ALTER TABLE `pdc_utilisateurs_roles`
  ADD CONSTRAINT `pdc_utilisateurs_roles_ibfk_1` FOREIGN KEY (`username`) REFERENCES `pdc_utilisateurs` (`username`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
