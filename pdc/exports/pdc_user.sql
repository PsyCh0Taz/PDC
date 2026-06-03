-- phpMyAdmin SQL Dump
-- version 4.5.4.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: Jun 03, 2026 at 09:55 PM
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
-- Table structure for table `departements`
--

CREATE TABLE `departements` (
  `id` int(10) UNSIGNED NOT NULL,
  `entreprise_id` int(10) UNSIGNED NOT NULL,
  `ldap_dn` varchar(255) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `ordre` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `departements`
--

INSERT INTO `departements` (`id`, `entreprise_id`, `ldap_dn`, `nom`, `actif`, `ordre`) VALUES
(1, 1, 'ou=ec2sa,ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'EC2SA OLD', 1, 0),
(2, 1, 'ou=esioc,ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'ESIOC OLD', 1, 0);

-- --------------------------------------------------------

--
-- Table structure for table `domaines`
--

CREATE TABLE `domaines` (
  `id` int(10) UNSIGNED NOT NULL,
  `hierarchie_id` int(10) UNSIGNED NOT NULL,
  `nom` varchar(150) NOT NULL,
  `ordre` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `domaines`
--

INSERT INTO `domaines` (`id`, `hierarchie_id`, `nom`, `ordre`) VALUES
(1, 5, 'IA', 0),
(2, 5, 'C2', 1),
(4, 7, 'ACCS', 1),
(5, 7, 'RADARS', 2),
(6, 5, 'ddd', 2);

-- --------------------------------------------------------

--
-- Table structure for table `entreprises`
--

CREATE TABLE `entreprises` (
  `id` int(10) UNSIGNED NOT NULL,
  `ldap_dn` varchar(255) NOT NULL COMMENT 'DN LDAP de l entité',
  `nom` varchar(150) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `ordre` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `entreprises`
--

INSERT INTO `entreprises` (`id`, `ldap_dn`, `nom`, `actif`, `ordre`) VALUES
(1, 'ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'BA118', 1, 1),
(2, 'ou=ba942,dc=a,dc=c,dc=d,dc=fr', 'BA942', 1, 2);

-- --------------------------------------------------------

--
-- Table structure for table `hierarchie`
--

CREATE TABLE `hierarchie` (
  `id` int(11) NOT NULL,
  `nom` varchar(100) NOT NULL,
  `id_parent` int(11) DEFAULT '0',
  `ordre` int(11) NOT NULL,
  `actif` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1;

--
-- Dumping data for table `hierarchie`
--

INSERT INTO `hierarchie` (`id`, `nom`, `id_parent`, `ordre`, `actif`) VALUES
(1, 'BA118', 0, 1, 1),
(2, 'BA942', 0, 2, 1),
(3, 'EC2SA', 1, 1, 1),
(4, 'ESIOC', 1, 2, 1),
(5, 'CCOA', 3, 1, 1),
(6, 'CTI', 3, 2, 1),
(7, 'CCNS', 3, 1, 1),
(8, 'EM.DSA', 3, 1, 1);

-- --------------------------------------------------------

--
-- Table structure for table `journal_connexions`
--

CREATE TABLE `journal_connexions` (
  `id` bigint(20) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL,
  `ip` varchar(45) NOT NULL,
  `date_heure` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `via_partage` tinyint(1) NOT NULL DEFAULT '0',
  `share_token` varchar(64) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `journal_connexions`
--

INSERT INTO `journal_connexions` (`id`, `username`, `ip`, `date_heure`, `via_partage`, `share_token`) VALUES
(1, 'taz', '127.0.0.1', '2026-05-08 20:10:50', 0, NULL),
(2, 'taz', '127.0.0.1', '2026-05-10 16:18:04', 0, NULL),
(3, 'taz', '127.0.0.1', '2026-05-12 19:10:30', 0, NULL),
(4, 'taz', '127.0.0.1', '2026-05-14 18:38:52', 0, NULL),
(5, 'taz', '127.0.0.1', '2026-05-22 18:21:53', 0, NULL),
(6, 'taz', '127.0.0.1', '2026-05-27 21:13:50', 0, NULL),
(7, 'tdd (échec)', '127.0.0.1', '2026-05-31 16:34:55', 0, NULL),
(8, 'taz', '127.0.0.1', '2026-05-31 16:35:01', 0, NULL),
(9, 'taz', '127.0.0.1', '2026-06-01 20:35:16', 0, NULL),
(10, 'taz', '127.0.0.1', '2026-06-01 20:51:02', 0, NULL),
(11, 'taz', '127.0.0.1', '2026-06-01 21:01:30', 0, NULL),
(12, 'taz (échec)', '127.0.0.1', '2026-06-01 21:04:03', 0, NULL),
(13, 'taz', '127.0.0.1', '2026-06-01 21:04:06', 0, NULL),
(14, 'taz', '127.0.0.1', '2026-06-03 18:51:47', 0, NULL),
(15, 'taz', '127.0.0.1', '2026-06-03 19:28:23', 0, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `journal_modifications`
--

CREATE TABLE `journal_modifications` (
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
-- Dumping data for table `journal_modifications`
--

INSERT INTO `journal_modifications` (`id`, `username`, `ip`, `date_heure`, `action`, `entite`, `entite_id`, `description`) VALUES
(1, 'taz', '127.0.0.1', '2026-05-12 19:13:48', 'CREATE', 'domaine', 1, 'Création domaine : IA'),
(2, 'taz', '127.0.0.1', '2026-05-12 19:14:02', 'CREATE', 'domaine', 2, 'Création domaine : C2'),
(3, 'taz', '127.0.0.1', '2026-05-14 18:39:23', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=domaine, id=1'),
(4, 'taz', '127.0.0.1', '2026-05-22 18:25:08', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=domaine, id=1'),
(5, 'taz', '127.0.0.1', '2026-05-22 18:29:34', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=domaine, id=1'),
(6, 'taz', '127.0.0.1', '2026-05-22 21:31:32', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(7, 'taz', '127.0.0.1', '2026-05-22 21:31:40', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(8, 'taz', '127.0.0.1', '2026-05-22 21:36:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(9, 'taz', '127.0.0.1', '2026-05-22 21:36:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(10, 'taz', '127.0.0.1', '2026-05-22 21:36:36', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(11, 'taz', '127.0.0.1', '2026-05-22 21:38:01', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(12, 'taz', '127.0.0.1', '2026-05-22 21:38:01', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(13, 'taz', '127.0.0.1', '2026-05-22 21:38:01', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(14, 'taz', '127.0.0.1', '2026-05-22 21:39:00', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(15, 'taz', '127.0.0.1', '2026-05-22 21:39:03', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(16, 'taz', '127.0.0.1', '2026-05-22 21:39:05', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(17, 'taz', '127.0.0.1', '2026-05-22 21:39:49', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(18, 'taz', '127.0.0.1', '2026-05-22 21:40:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(19, 'taz', '127.0.0.1', '2026-05-22 21:40:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(20, 'taz', '127.0.0.1', '2026-05-22 21:41:12', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(21, 'taz', '127.0.0.1', '2026-05-22 21:41:12', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(22, 'taz', '127.0.0.1', '2026-05-22 21:41:12', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(23, 'taz', '127.0.0.1', '2026-05-22 21:41:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(24, 'taz', '127.0.0.1', '2026-05-22 21:41:34', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(25, 'taz', '127.0.0.1', '2026-05-22 21:41:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(26, 'taz', '127.0.0.1', '2026-05-22 21:42:41', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(27, 'taz', '127.0.0.1', '2026-05-22 21:42:41', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(28, 'taz', '127.0.0.1', '2026-05-22 21:42:41', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(29, 'taz', '127.0.0.1', '2026-05-22 21:42:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(30, 'taz', '127.0.0.1', '2026-05-22 21:42:45', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(31, 'taz', '127.0.0.1', '2026-05-22 21:42:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(32, 'taz', '127.0.0.1', '2026-05-22 21:42:52', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(33, 'taz', '127.0.0.1', '2026-05-22 21:42:52', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(34, 'taz', '127.0.0.1', '2026-05-22 21:42:52', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(35, 'taz', '127.0.0.1', '2026-05-22 21:43:23', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2'),
(36, 'taz', '127.0.0.1', '2026-05-22 21:43:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(37, 'taz', '127.0.0.1', '2026-05-22 21:43:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(38, 'taz', '127.0.0.1', '2026-05-22 21:43:34', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(39, 'taz', '127.0.0.1', '2026-05-22 21:43:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(40, 'taz', '127.0.0.1', '2026-05-22 21:43:34', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(41, 'taz', '127.0.0.1', '2026-05-22 21:43:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(42, 'taz', '127.0.0.1', '2026-05-22 21:43:36', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1'),
(43, 'taz', '127.0.0.1', '2026-05-22 21:43:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(44, 'taz', '127.0.0.1', '2026-05-22 21:44:07', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(45, 'taz', '127.0.0.1', '2026-05-22 21:44:11', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(46, 'taz', '127.0.0.1', '2026-05-22 21:44:21', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(47, 'taz', '127.0.0.1', '2026-05-22 21:44:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(48, 'taz', '127.0.0.1', '2026-05-22 21:44:40', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(49, 'taz', '127.0.0.1', '2026-05-22 21:44:40', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(50, 'taz', '127.0.0.1', '2026-05-22 21:44:40', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(51, 'taz', '127.0.0.1', '2026-05-22 21:44:45', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(52, 'taz', '127.0.0.1', '2026-05-22 21:44:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(53, 'taz', '127.0.0.1', '2026-05-22 21:44:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(54, 'taz', '127.0.0.1', '2026-05-22 21:45:28', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(55, 'taz', '127.0.0.1', '2026-05-22 21:45:31', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(56, 'taz', '127.0.0.1', '2026-05-22 21:46:18', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(57, 'taz', '127.0.0.1', '2026-05-22 21:47:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(58, 'taz', '127.0.0.1', '2026-05-22 21:47:10', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(59, 'taz', '127.0.0.1', '2026-05-22 21:47:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(60, 'taz', '127.0.0.1', '2026-05-22 21:47:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(61, 'taz', '127.0.0.1', '2026-05-22 21:47:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(62, 'taz', '127.0.0.1', '2026-05-22 21:47:35', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2'),
(63, 'taz', '127.0.0.1', '2026-05-22 21:47:37', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(64, 'taz', '127.0.0.1', '2026-05-22 21:47:37', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1'),
(65, 'taz', '127.0.0.1', '2026-05-22 21:47:38', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(66, 'taz', '127.0.0.1', '2026-05-22 21:48:10', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2'),
(67, 'taz', '127.0.0.1', '2026-05-22 21:48:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(68, 'taz', '127.0.0.1', '2026-05-22 21:48:10', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(69, 'taz', '127.0.0.1', '2026-05-22 21:48:27', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1'),
(70, 'taz', '127.0.0.1', '2026-05-22 21:48:27', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(71, 'taz', '127.0.0.1', '2026-05-22 21:48:27', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(72, 'taz', '127.0.0.1', '2026-05-22 21:48:32', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(73, 'taz', '127.0.0.1', '2026-05-22 21:48:32', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(74, 'taz', '127.0.0.1', '2026-05-22 21:48:32', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(75, 'taz', '127.0.0.1', '2026-05-22 21:48:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(76, 'taz', '127.0.0.1', '2026-05-22 21:48:59', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(77, 'taz', '127.0.0.1', '2026-05-22 21:49:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(78, 'taz', '127.0.0.1', '2026-05-22 21:49:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(79, 'taz', '127.0.0.1', '2026-05-22 21:49:14', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(80, 'taz', '127.0.0.1', '2026-05-22 21:49:29', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(81, 'taz', '127.0.0.1', '2026-05-22 21:49:29', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(82, 'taz', '127.0.0.1', '2026-05-22 21:49:29', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(83, 'taz', '127.0.0.1', '2026-05-22 21:49:36', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(84, 'taz', '127.0.0.1', '2026-05-22 21:49:39', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(85, 'taz', '127.0.0.1', '2026-05-22 21:50:58', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(86, 'taz', '127.0.0.1', '2026-05-22 21:51:56', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(87, 'taz', '127.0.0.1', '2026-05-22 21:52:30', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(88, 'taz', '127.0.0.1', '2026-05-22 21:52:30', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 2'),
(89, 'taz', '127.0.0.1', '2026-05-22 21:52:30', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(90, 'taz', '127.0.0.1', '2026-05-22 21:52:35', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(91, 'taz', '127.0.0.1', '2026-05-22 21:52:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(92, 'taz', '127.0.0.1', '2026-05-22 21:52:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(93, 'taz', '127.0.0.1', '2026-05-22 21:52:45', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(94, 'taz', '127.0.0.1', '2026-05-22 21:52:51', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(95, 'taz', '127.0.0.1', '2026-05-22 21:52:53', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(96, 'taz', '127.0.0.1', '2026-05-22 21:52:53', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(97, 'taz', '127.0.0.1', '2026-05-22 21:52:53', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(98, 'taz', '127.0.0.1', '2026-05-22 21:52:55', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(99, 'taz', '127.0.0.1', '2026-05-22 21:52:55', 'UPDATE', 'projet', 1, 'Déplacement projet vers domaine 1'),
(100, 'taz', '127.0.0.1', '2026-05-22 21:52:55', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(101, 'taz', '127.0.0.1', '2026-05-22 21:53:11', 'CREATE', 'domaine', 3, 'Création domaine : C2'),
(102, 'taz', '127.0.0.1', '2026-05-22 21:54:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(103, 'taz', '127.0.0.1', '2026-05-22 21:54:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(104, 'taz', '127.0.0.1', '2026-05-22 21:54:19', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(105, 'taz', '127.0.0.1', '2026-05-22 21:54:26', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines'),
(106, 'taz', '127.0.0.1', '2026-05-22 21:54:29', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines'),
(107, 'taz', '127.0.0.1', '2026-05-22 21:56:15', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(108, 'taz', '127.0.0.1', '2026-05-22 21:56:15', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(109, 'taz', '127.0.0.1', '2026-05-22 21:56:15', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(110, 'taz', '127.0.0.1', '2026-05-22 21:57:11', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(111, 'taz', '127.0.0.1', '2026-05-22 21:57:11', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(112, 'taz', '127.0.0.1', '2026-05-22 21:57:11', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(113, 'taz', '127.0.0.1', '2026-05-22 21:57:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(114, 'taz', '127.0.0.1', '2026-05-22 21:57:19', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(115, 'taz', '127.0.0.1', '2026-05-22 21:57:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(116, 'taz', '127.0.0.1', '2026-05-22 21:58:18', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(117, 'taz', '127.0.0.1', '2026-05-22 21:58:26', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(118, 'taz', '127.0.0.1', '2026-05-22 22:24:44', 'UPDATE', 'domaine', 1, 'Modification domaine : IAss'),
(119, 'taz', '127.0.0.1', '2026-05-22 22:24:51', 'UPDATE', 'domaine', 1, 'Modification domaine : IA'),
(120, 'taz', '127.0.0.1', '2026-05-22 22:32:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(121, 'taz', '127.0.0.1', '2026-05-22 22:32:35', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(122, 'taz', '127.0.0.1', '2026-05-22 22:32:35', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(123, 'taz', '127.0.0.1', '2026-05-22 22:32:41', 'DELETE', 'domaine', 3, 'Suppression domaine'),
(124, 'taz', '127.0.0.1', '2026-05-27 21:20:10', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(125, 'taz', '127.0.0.1', '2026-05-27 21:22:37', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(126, 'taz', '127.0.0.1', '2026-05-27 21:26:04', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(127, 'taz', '127.0.0.1', '2026-05-27 21:26:30', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(128, 'taz', '127.0.0.1', '2026-05-27 21:29:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(129, 'taz', '127.0.0.1', '2026-05-27 21:30:34', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(130, 'taz', '127.0.0.1', '2026-05-27 21:30:42', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(131, 'taz', '127.0.0.1', '2026-05-27 21:30:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(132, 'taz', '127.0.0.1', '2026-05-27 21:35:00', 'UPDATE', 'domaine', 1, 'Modification domaine : IA2'),
(133, 'taz', '127.0.0.1', '2026-05-27 21:35:14', 'UPDATE', 'domaine', 1, 'Modification domaine : IA'),
(134, 'taz', '127.0.0.1', '2026-05-27 21:35:21', 'CREATE', 'domaine', 3, 'Création domaine : IA2'),
(135, 'taz', '127.0.0.1', '2026-05-27 21:35:26', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines'),
(136, 'taz', '127.0.0.1', '2026-05-27 21:35:31', 'UPDATE', 'domaines', NULL, 'Réorganisation domaines'),
(137, 'taz', '127.0.0.1', '2026-05-27 21:36:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(138, 'taz', '127.0.0.1', '2026-05-27 21:36:16', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(139, 'taz', '127.0.0.1', '2026-05-27 21:36:32', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(140, 'taz', '127.0.0.1', '2026-05-27 21:37:02', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(141, 'taz', '127.0.0.1', '2026-05-27 21:42:01', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(142, 'taz', '127.0.0.1', '2026-05-27 21:55:00', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(143, 'taz', '127.0.0.1', '2026-05-27 21:58:03', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(144, 'taz', '127.0.0.1', '2026-05-27 21:58:35', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(145, 'taz', '127.0.0.1', '2026-05-27 21:59:09', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(146, 'taz', '127.0.0.1', '2026-05-27 21:59:48', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(147, 'taz', '127.0.0.1', '2026-05-27 22:00:27', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(148, 'taz', '127.0.0.1', '2026-05-27 22:00:58', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 2'),
(149, 'taz', '127.0.0.1', '2026-05-27 22:04:58', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(150, 'taz', '127.0.0.1', '2026-05-27 22:26:41', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(151, 'taz', '127.0.0.1', '2026-05-27 22:27:10', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(152, 'taz', '127.0.0.1', '2026-05-27 22:28:21', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(153, 'taz', '127.0.0.1', '2026-05-27 22:29:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(154, 'taz', '127.0.0.1', '2026-05-27 22:48:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(155, 'taz', '127.0.0.1', '2026-05-27 22:48:32', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(156, 'taz', '127.0.0.1', '2026-05-27 22:49:22', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(157, 'taz', '127.0.0.1', '2026-05-31 16:41:18', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(158, 'taz', '127.0.0.1', '2026-05-31 16:41:37', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(159, 'taz', '127.0.0.1', '2026-05-31 16:44:57', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(160, 'taz', '127.0.0.1', '2026-05-31 16:45:54', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(161, 'taz', '127.0.0.1', '2026-05-31 16:54:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(162, 'taz', '127.0.0.1', '2026-05-31 16:55:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(163, 'taz', '127.0.0.1', '2026-05-31 16:57:19', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(164, 'taz', '127.0.0.1', '2026-05-31 16:57:25', 'UPDATE', 'domaine', 1, 'Modification domaine : IA'),
(165, 'taz', '127.0.0.1', '2026-05-31 16:57:59', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(166, 'taz', '127.0.0.1', '2026-05-31 16:58:23', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(167, 'taz', '127.0.0.1', '2026-05-31 16:58:54', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(168, 'taz', '127.0.0.1', '2026-05-31 16:59:12', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(169, 'taz', '127.0.0.1', '2026-05-31 17:00:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(170, 'taz', '127.0.0.1', '2026-05-31 17:04:07', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(171, 'taz', '127.0.0.1', '2026-05-31 17:04:18', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(172, 'taz', '127.0.0.1', '2026-05-31 17:07:19', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(173, 'taz', '127.0.0.1', '2026-05-31 17:09:15', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(174, 'taz', '127.0.0.1', '2026-05-31 17:11:11', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(175, 'taz', '127.0.0.1', '2026-05-31 17:16:26', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(176, 'taz', '127.0.0.1', '2026-05-31 17:19:45', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(177, 'taz', '127.0.0.1', '2026-05-31 17:20:19', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(178, 'taz', '127.0.0.1', '2026-05-31 17:20:38', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(179, 'taz', '127.0.0.1', '2026-05-31 17:20:57', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(180, 'taz', '127.0.0.1', '2026-05-31 17:22:26', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(181, 'taz', '127.0.0.1', '2026-05-31 17:41:20', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(182, 'taz', '127.0.0.1', '2026-05-31 17:41:38', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(183, 'taz', '127.0.0.1', '2026-05-31 17:50:20', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(184, 'taz', '127.0.0.1', '2026-05-31 17:54:50', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(185, 'taz', '127.0.0.1', '2026-05-31 17:55:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(186, 'taz', '127.0.0.1', '2026-05-31 18:33:32', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA'),
(187, 'taz', '127.0.0.1', '2026-05-31 18:33:38', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA'),
(188, 'taz', '127.0.0.1', '2026-05-31 18:33:52', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA'),
(189, 'taz', '127.0.0.1', '2026-05-31 18:34:17', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA'),
(190, 'taz', '127.0.0.1', '2026-05-31 18:41:55', 'DELETE', 'domaine', 3, 'Suppression domaine'),
(191, 'taz', '127.0.0.1', '2026-05-31 18:41:58', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(192, 'taz', '127.0.0.1', '2026-05-31 18:41:58', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(193, 'taz', '127.0.0.1', '2026-05-31 18:41:58', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(194, 'taz', '127.0.0.1', '2026-05-31 18:52:27', 'CREATE', 'domaine', 4, 'Création domaine : ACCS'),
(195, 'taz', '127.0.0.1', '2026-05-31 18:52:45', 'CREATE', 'domaine', 5, 'Création domaine : RADARS'),
(196, 'taz', '127.0.0.1', '2026-05-31 19:15:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(197, 'taz', '127.0.0.1', '2026-05-31 19:15:23', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(198, 'taz', '127.0.0.1', '2026-05-31 19:15:23', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 2'),
(199, 'taz', '127.0.0.1', '2026-05-31 19:15:56', 'UPDATE', 'projet', 2, 'Déplacement projet vers domaine 1'),
(200, 'taz', '127.0.0.1', '2026-05-31 19:15:56', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(201, 'taz', '127.0.0.1', '2026-05-31 19:15:56', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(202, 'taz', '127.0.0.1', '2026-05-31 19:29:51', 'CREATE', 'domaine', 6, 'Création domaine : ddd'),
(203, 'taz', '127.0.0.1', '2026-05-31 19:30:03', 'UPDATE', 'projet', 0, 'Modification projet : dsfsqf'),
(204, 'taz', '127.0.0.1', '2026-05-31 19:30:25', 'UPDATE', 'projet', 0, 'Modification projet : fffff'),
(205, 'taz', '127.0.0.1', '2026-05-31 19:44:56', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(206, 'taz', '127.0.0.1', '2026-05-31 19:45:12', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(207, 'taz', '127.0.0.1', '2026-06-01 21:21:07', 'ASSIGN_ROLE', 'user', 0, 'Rôle \'responsable\' assigné à taz'),
(208, 'taz', '127.0.0.1', '2026-06-01 21:25:54', 'CREATE', 'projet', 3, 'Création projet : bcvbvcb'),
(209, 'taz', '127.0.0.1', '2026-06-01 21:27:09', 'CREATE', 'projet', 4, 'Création projet : hfhfdgh'),
(210, 'taz', '127.0.0.1', '2026-06-01 21:27:15', 'DELETE', 'projet', 4, 'Suppression projet'),
(211, 'taz', '127.0.0.1', '2026-06-01 21:27:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(212, 'taz', '127.0.0.1', '2026-06-01 21:27:19', 'UPDATE', 'projet', 3, 'Déplacement projet vers domaine 2'),
(213, 'taz', '127.0.0.1', '2026-06-01 21:27:19', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(214, 'taz', '127.0.0.1', '2026-06-01 21:27:44', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb'),
(215, 'taz', '127.0.0.1', '2026-06-01 21:40:17', 'CREATE', 'departement', 3, 'Création département : CAN'),
(216, 'taz', '127.0.0.1', '2026-06-01 21:40:49', 'DELETE', 'departement', 3, 'Suppression département'),
(217, 'taz', '127.0.0.1', '2026-06-01 21:47:38', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb'),
(218, 'taz', '127.0.0.1', '2026-06-01 21:48:05', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb'),
(219, 'taz', '127.0.0.1', '2026-06-01 21:48:29', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb'),
(220, 'taz', '127.0.0.1', '2026-06-01 21:48:39', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb'),
(221, 'taz', '127.0.0.1', '2026-06-01 21:49:02', 'UPDATE', 'projet', 3, 'Modification projet : bcvbvcb'),
(222, 'taz', '127.0.0.1', '2026-06-03 18:52:04', 'CREATE', 'projet', 4, 'Création projet : fdgdsgd'),
(223, 'taz', '127.0.0.1', '2026-06-03 18:52:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(224, 'taz', '127.0.0.1', '2026-06-03 18:52:14', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(225, 'taz', '127.0.0.1', '2026-06-03 18:52:14', 'UPDATE', 'projet', 4, 'Déplacement projet vers domaine 2'),
(226, 'taz', '127.0.0.1', '2026-06-03 18:56:11', 'DESACTIVER', 'service', 3, 'Service CTI désactivé'),
(227, 'taz', '127.0.0.1', '2026-06-03 18:56:26', 'ACTIVER', 'service', 3, 'Service CTI activé'),
(228, 'taz', '127.0.0.1', '2026-06-03 19:24:53', 'DESACTIVER', 'service', 2, 'Service C2NS désactivé'),
(229, 'taz', '127.0.0.1', '2026-06-03 19:25:11', 'DESACTIVER', 'service', 3, 'Service CTI désactivé'),
(230, 'taz', '127.0.0.1', '2026-06-03 19:25:11', 'DESACTIVER', 'service', 4, 'Service EM.DSA désactivé'),
(231, 'taz', '127.0.0.1', '2026-06-03 19:31:38', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour'),
(232, 'taz', '127.0.0.1', '2026-06-03 19:31:38', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour'),
(233, 'taz', '127.0.0.1', '2026-06-03 19:31:48', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour'),
(234, 'taz', '127.0.0.1', '2026-06-03 19:31:48', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour'),
(235, 'taz', '127.0.0.1', '2026-06-03 19:33:24', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour'),
(236, 'taz', '127.0.0.1', '2026-06-03 19:33:24', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour'),
(237, 'taz', '127.0.0.1', '2026-06-03 19:34:51', 'UPDATE_SETTINGS', 'parametres', 0, 'Logo mis à jour'),
(238, 'taz', '127.0.0.1', '2026-06-03 19:34:51', 'UPDATE_SETTINGS', 'parametres', 0, 'Titre PDF mis à jour'),
(239, 'taz', '127.0.0.1', '2026-06-03 19:34:58', 'EXPORT', 'pdf', NULL, 'Export PDF : niveau=entreprise, id='),
(240, 'taz', '127.0.0.1', '2026-06-03 22:50:03', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(241, 'taz', '127.0.0.1', '2026-06-03 22:50:03', 'UPDATE', 'projet', 4, 'Déplacement projet vers domaine 6'),
(242, 'taz', '127.0.0.1', '2026-06-03 22:50:03', 'UPDATE', 'projets', NULL, 'Réorganisation projets'),
(243, 'taz', '127.0.0.1', '2026-06-03 23:29:26', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA'),
(244, 'taz', '127.0.0.1', '2026-06-03 23:29:36', 'UPDATE', 'projet', 2, 'Modification projet : Casque RA'),
(245, 'taz', '127.0.0.1', '2026-06-03 23:30:06', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(246, 'taz', '127.0.0.1', '2026-06-03 23:30:36', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23'),
(247, 'taz', '127.0.0.1', '2026-06-03 23:31:12', 'UPDATE', 'projet', 1, 'Modification projet : Agent C2 - 23');

-- --------------------------------------------------------

--
-- Table structure for table `parametres`
--

CREATE TABLE `parametres` (
  `cle` varchar(80) NOT NULL,
  `valeur` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `parametres`
--

INSERT INTO `parametres` (`cle`, `valeur`) VALUES
('logo_url', '/assets/uploads/logo_1780508091.svg'),
('pdf_logo', ''),
('pdf_titre', 'Plan de Charge'),
('titre_pdf', 'Plan de Charge');

-- --------------------------------------------------------

--
-- Table structure for table `projets`
--

CREATE TABLE `projets` (
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

INSERT INTO `projets` (`id`, `domaine_id`, `titre`, `date_debut`, `date_fin`, `ordre`, `created_at`, `updated_at`) VALUES
(1, 1, 'Agent C2 - 23', '2026-05-01', '2027-06-30', 1, '2026-05-12 19:15:34', '2026-05-31 19:15:56'),
(2, 1, 'Casque RA', '2026-03-01', '2026-10-30', 0, '2026-05-12 19:15:34', '2026-05-31 19:15:56'),
(3, 2, 'bcvbvcb', '2026-05-12', '2026-07-01', 1, '2026-06-01 21:25:54', '2026-06-03 18:52:14'),
(4, 6, 'fdgdsgd', '2026-06-02', '2026-06-12', 0, '2026-06-03 18:52:04', '2026-06-03 22:50:03');

-- --------------------------------------------------------

--
-- Table structure for table `projet_gradients`
--

CREATE TABLE `projet_gradients` (
  `id` int(10) UNSIGNED NOT NULL,
  `projet_id` int(10) UNSIGNED NOT NULL,
  `date_gradient` date NOT NULL,
  `couleur` enum('vert','jaune','orange','rouge') NOT NULL DEFAULT 'vert'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `projet_gradients`
--

INSERT INTO `projet_gradients` (`id`, `projet_id`, `date_gradient`, `couleur`) VALUES
(445, 3, '2026-06-18', 'jaune'),
(446, 3, '2026-06-21', 'orange'),
(447, 3, '2026-06-24', 'rouge'),
(465, 1, '2026-04-27', 'rouge'),
(466, 1, '2026-05-04', 'orange'),
(467, 1, '2026-05-11', 'jaune'),
(468, 1, '2026-05-18', 'vert'),
(469, 1, '2026-05-25', 'jaune'),
(470, 1, '2026-06-01', 'orange'),
(471, 1, '2026-06-08', 'rouge'),
(472, 1, '2026-07-09', 'rouge'),
(473, 1, '2026-07-13', 'jaune');

-- --------------------------------------------------------

--
-- Table structure for table `projet_jalons`
--

CREATE TABLE `projet_jalons` (
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

INSERT INTO `projet_jalons` (`id`, `projet_id`, `date_jalon`, `couleur`, `libelle`, `jalon_reference_id`) VALUES
(293, 3, '2026-05-03', 'vert', 'J1', NULL),
(294, 3, '2026-06-10', 'vert', 'J2', 293),
(298, 2, '2026-05-12', 'vert', 'J1', NULL),
(299, 2, '2026-05-22', 'orange', 'J2', 298),
(300, 2, '2026-05-30', 'vert', 'J3', 299),
(321, 1, '2026-04-01', 'vert', 'J7', NULL),
(322, 1, '2026-05-07', 'vert', 'J1', NULL),
(323, 1, '2026-05-13', 'rouge', 'J5', NULL),
(324, 1, '2026-05-14', 'vert', 'J8', 321),
(325, 1, '2026-05-21', 'orange', 'J6', 323),
(326, 1, '2026-05-22', 'jaune', 'J2', 322),
(327, 1, '2026-05-27', 'orange', 'J3', NULL),
(328, 1, '2026-06-02', 'vert', 'J9', NULL),
(329, 1, '2026-06-08', 'rouge', 'J4', 327),
(330, 1, '2026-07-06', 'vert', 'J10 décalé', 328);

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(10) UNSIGNED NOT NULL,
  `departement_id` int(10) UNSIGNED NOT NULL,
  `ldap_dn` varchar(255) NOT NULL,
  `nom` varchar(150) NOT NULL,
  `actif` tinyint(1) NOT NULL DEFAULT '1',
  `ordre` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `departement_id`, `ldap_dn`, `nom`, `actif`, `ordre`) VALUES
(1, 1, 'ou=ccoa,ou=ec2sa,ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'CCOA', 1, 0),
(2, 1, 'ou=c2ns,ou=ec2sa,ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'C2NS', 0, 0),
(3, 1, 'ou=cti,ou=ec2sa,ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'CTI', 0, 0),
(4, 1, 'ou=emdsa,ou=ec2sa,ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'EM.DSA', 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `share_links`
--

CREATE TABLE `share_links` (
  `id` int(10) UNSIGNED NOT NULL,
  `token` varchar(64) NOT NULL,
  `url_params` text NOT NULL COMMENT 'Paramètres GET encodés (niveau, ids, période)',
  `created_by` varchar(100) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_roles`
--

CREATE TABLE `user_roles` (
  `id` int(10) UNSIGNED NOT NULL,
  `username` varchar(100) NOT NULL COMMENT 'sAMAccountName ou uid LDAP',
  `scope_dn` varchar(255) NOT NULL COMMENT 'DN de la branche LDAP concernée',
  `role` enum('admin','responsable','modificateur','lecteur') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_roles`
--

INSERT INTO `user_roles` (`id`, `username`, `scope_dn`, `role`) VALUES
(1, 'taz22', 'uid=taz,ou=direction,ou=users,dc=a,dc=c,dc=d,dc=fr', 'admin');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs`
--

CREATE TABLE `utilisateurs` (
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

INSERT INTO `utilisateurs` (`id`, `username`, `displayname`, `dn`, `email`, `date_creation`) VALUES
(1, 'taz', 'Taz', 'uid=taz,ou=direction,ou=users,dc=a,dc=c,dc=d,dc=fr', 'taz@taz.fr', '2026-06-01 19:00:46');

-- --------------------------------------------------------

--
-- Table structure for table `utilisateurs_roles`
--

CREATE TABLE `utilisateurs_roles` (
  `id` int(11) NOT NULL,
  `username` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role_dn` varchar(500) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('admin','responsable','modificateur','lecteur') COLLATE utf8mb4_unicode_ci NOT NULL,
  `date_creation` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `utilisateurs_roles`
--

INSERT INTO `utilisateurs_roles` (`id`, `username`, `role_dn`, `role`, `date_creation`) VALUES
(2, 'taz', 'ou=ba118,dc=a,dc=c,dc=d,dc=fr', 'admin', '2026-06-01 19:00:58');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `departements`
--
ALTER TABLE `departements`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ldap_dn` (`ldap_dn`),
  ADD KEY `fk_dep_ent` (`entreprise_id`);

--
-- Indexes for table `domaines`
--
ALTER TABLE `domaines`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_dom_srv` (`hierarchie_id`);

--
-- Indexes for table `entreprises`
--
ALTER TABLE `entreprises`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ldap_dn` (`ldap_dn`);

--
-- Indexes for table `hierarchie`
--
ALTER TABLE `hierarchie`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_parent` (`id_parent`);

--
-- Indexes for table `journal_connexions`
--
ALTER TABLE `journal_connexions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_date` (`date_heure`);

--
-- Indexes for table `journal_modifications`
--
ALTER TABLE `journal_modifications`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_date` (`date_heure`);

--
-- Indexes for table `parametres`
--
ALTER TABLE `parametres`
  ADD PRIMARY KEY (`cle`);

--
-- Indexes for table `projets`
--
ALTER TABLE `projets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_proj_dom` (`domaine_id`);

--
-- Indexes for table `projet_gradients`
--
ALTER TABLE `projet_gradients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_grad_proj` (`projet_id`);

--
-- Indexes for table `projet_jalons`
--
ALTER TABLE `projet_jalons`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_jalon_proj` (`projet_id`),
  ADD KEY `fk_jalon_ref` (`jalon_reference_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ldap_dn` (`ldap_dn`),
  ADD KEY `fk_srv_dep` (`departement_id`);

--
-- Indexes for table `share_links`
--
ALTER TABLE `share_links`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token` (`token`);

--
-- Indexes for table `user_roles`
--
ALTER TABLE `user_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_scope` (`username`,`scope_dn`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `idx_username` (`username`);

--
-- Indexes for table `utilisateurs_roles`
--
ALTER TABLE `utilisateurs_roles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_role` (`username`,`role_dn`,`role`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_role` (`role`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `departements`
--
ALTER TABLE `departements`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `domaines`
--
ALTER TABLE `domaines`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;
--
-- AUTO_INCREMENT for table `entreprises`
--
ALTER TABLE `entreprises`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- AUTO_INCREMENT for table `hierarchie`
--
ALTER TABLE `hierarchie`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;
--
-- AUTO_INCREMENT for table `journal_connexions`
--
ALTER TABLE `journal_connexions`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;
--
-- AUTO_INCREMENT for table `journal_modifications`
--
ALTER TABLE `journal_modifications`
  MODIFY `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=248;
--
-- AUTO_INCREMENT for table `projets`
--
ALTER TABLE `projets`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `projet_gradients`
--
ALTER TABLE `projet_gradients`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=474;
--
-- AUTO_INCREMENT for table `projet_jalons`
--
ALTER TABLE `projet_jalons`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=331;
--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- AUTO_INCREMENT for table `share_links`
--
ALTER TABLE `share_links`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
--
-- AUTO_INCREMENT for table `user_roles`
--
ALTER TABLE `user_roles`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `utilisateurs`
--
ALTER TABLE `utilisateurs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;
--
-- AUTO_INCREMENT for table `utilisateurs_roles`
--
ALTER TABLE `utilisateurs_roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
--
-- Constraints for dumped tables
--

--
-- Constraints for table `departements`
--
ALTER TABLE `departements`
  ADD CONSTRAINT `fk_dep_ent` FOREIGN KEY (`entreprise_id`) REFERENCES `entreprises` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projets`
--
ALTER TABLE `projets`
  ADD CONSTRAINT `fk_proj_dom` FOREIGN KEY (`domaine_id`) REFERENCES `domaines` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projet_gradients`
--
ALTER TABLE `projet_gradients`
  ADD CONSTRAINT `fk_grad_proj` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `projet_jalons`
--
ALTER TABLE `projet_jalons`
  ADD CONSTRAINT `fk_jalon_proj` FOREIGN KEY (`projet_id`) REFERENCES `projets` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `services`
--
ALTER TABLE `services`
  ADD CONSTRAINT `fk_srv_dep` FOREIGN KEY (`departement_id`) REFERENCES `departements` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `utilisateurs_roles`
--
ALTER TABLE `utilisateurs_roles`
  ADD CONSTRAINT `utilisateurs_roles_ibfk_1` FOREIGN KEY (`username`) REFERENCES `utilisateurs` (`username`) ON DELETE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
