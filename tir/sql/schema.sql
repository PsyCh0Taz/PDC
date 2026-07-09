-- =============================================================================
-- TIR118 - Schéma de base de données MySQL
-- Encodage : UTF-8
-- =============================================================================

SET NAMES utf8;
SET time_zone = '+00:00';
SET foreign_key_checks = 0;

-- ---------------------------------------------------------------------------
-- Utilisateurs (synchronisés depuis LDAP)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `users` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `ldap_uid`   VARCHAR(100) NOT NULL,
  `nom`        VARCHAR(100) DEFAULT NULL,
  `prenom`     VARCHAR(100) DEFAULT NULL,
  `mail`       VARCHAR(200) DEFAULT NULL,
  `ou`         VARCHAR(500) DEFAULT NULL,
  `is_admin`   TINYINT(1)   NOT NULL DEFAULT 0,
  `last_login` DATETIME     DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `ldap_uid` (`ldap_uid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Catalogue d'armes
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `armes` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `libelle`    VARCHAR(200) NOT NULL,
  `image`      VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Catégories d'armes (groupes d'armes)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories_armes` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `titre`      VARCHAR(200) NOT NULL,
  `image`      VARCHAR(500) DEFAULT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Pivot catégorie_arme <-> arme
CREATE TABLE IF NOT EXISTS `categories_armes_armes` (
  `categorie_id` INT NOT NULL,
  `arme_id`      INT NOT NULL,
  PRIMARY KEY (`categorie_id`, `arme_id`),
  CONSTRAINT `fk_caa_cat`  FOREIGN KEY (`categorie_id`) REFERENCES `categories_armes`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_caa_arme` FOREIGN KEY (`arme_id`)      REFERENCES `armes`(`id`)            ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Raisons de tir
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `raisons` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `libelle`    VARCHAR(200) NOT NULL,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Catégories de tir (modèles / templates)
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `categories_tir` (
  `id`                INT          NOT NULL AUTO_INCREMENT,
  `titre`             VARCHAR(200) NOT NULL,
  `icone`             VARCHAR(200) DEFAULT NULL,
  `couleur`           VARCHAR(20)  NOT NULL DEFAULT '#3498db',
  `categorie_arme_id` INT          DEFAULT NULL,
  `date_raison`       DATE         DEFAULT NULL,
  `created_at`        DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_ct_catarme` FOREIGN KEY (`categorie_arme_id`) REFERENCES `categories_armes`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Pivot catégorie_tir <-> raisons
CREATE TABLE IF NOT EXISTS `categories_tir_raisons` (
  `categorie_tir_id` INT NOT NULL,
  `raison_id`        INT NOT NULL,
  PRIMARY KEY (`categorie_tir_id`, `raison_id`),
  CONSTRAINT `fk_ctr_cat`    FOREIGN KEY (`categorie_tir_id`) REFERENCES `categories_tir`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_ctr_raison` FOREIGN KEY (`raison_id`)        REFERENCES `raisons`(`id`)        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Séances de tir
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `tirs` (
  `id`               INT      NOT NULL AUTO_INCREMENT,
  `categorie_tir_id` INT      NOT NULL,
  `date_debut`       DATETIME NOT NULL,
  `date_fin`         DATETIME NOT NULL,
  `nb_places`        INT      NOT NULL DEFAULT 10,
  `published`        TINYINT(1) NOT NULL DEFAULT 0,
  `valide`           TINYINT(1) NOT NULL DEFAULT 0,
  `created_at`       DATETIME   NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  CONSTRAINT `fk_tir_cat` FOREIGN KEY (`categorie_tir_id`) REFERENCES `categories_tir`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Inscriptions
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `inscriptions` (
  `id`          INT          NOT NULL AUTO_INCREMENT,
  `tir_id`      INT          NOT NULL,
  `user_id`     INT          DEFAULT NULL,
  `nom`         VARCHAR(100) DEFAULT NULL,
  `prenom`      VARCHAR(100) DEFAULT NULL,
  `mail`        VARCHAR(200) NOT NULL,
  `raison_id`   INT          DEFAULT NULL,
  `arme_id`     INT          DEFAULT NULL,
  `raison_date` DATE         DEFAULT NULL,
  `type`        ENUM('inscrit','attente') NOT NULL DEFAULT 'inscrit',
  `statut`      ENUM('inscrit','present','absent','no_safe') NOT NULL DEFAULT 'inscrit',
  `hash`        VARCHAR(64)  NOT NULL,
  `created_at`  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `hash` (`hash`),
  CONSTRAINT `fk_ins_tir`    FOREIGN KEY (`tir_id`)    REFERENCES `tirs`(`id`)    ON DELETE CASCADE,
  CONSTRAINT `fk_ins_user`   FOREIGN KEY (`user_id`)   REFERENCES `users`(`id`)   ON DELETE SET NULL,
  CONSTRAINT `fk_ins_raison` FOREIGN KEY (`raison_id`) REFERENCES `raisons`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_ins_arme`   FOREIGN KEY (`arme_id`)   REFERENCES `armes`(`id`)   ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- ---------------------------------------------------------------------------
-- Articles de la page de garde
-- ---------------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `articles` (
  `id`         INT          NOT NULL AUTO_INCREMENT,
  `titre`      VARCHAR(200) NOT NULL,
  `contenu`    LONGTEXT     DEFAULT NULL,
  `ordre`      INT          NOT NULL DEFAULT 0,
  `actif`      TINYINT(1)   NOT NULL DEFAULT 1,
  `created_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

SET foreign_key_checks = 1;

-- ---------------------------------------------------------------------------
-- Données initiales de démonstration (commentez si non souhaité)
-- ---------------------------------------------------------------------------
INSERT INTO `raisons` (`libelle`) VALUES ('Renouvellement'), ('Inscription');

INSERT INTO `armes` (`libelle`) VALUES ('PA MAC50'), ('BERETTA 92FS'), ('Fusil de chasse juxtaposé');

INSERT INTO `categories_armes` (`titre`) VALUES ('Armes de poing'), ('Fusils d\'assaut'), ('Fusils de chasse');

INSERT INTO `categories_armes_armes` (`categorie_id`, `arme_id`) VALUES (1,1),(1,2),(3,3);

INSERT INTO `categories_tir` (`titre`, `couleur`, `categorie_arme_id`) VALUES
  ('Tir Armes de poing', '#e74c3c', 1),
  ('Tir Fusils de chasse', '#27ae60', 3);

INSERT INTO `articles` (`titre`, `contenu`, `ordre`) VALUES
  ('Bienvenue sur TIR118', '<p>Bienvenue sur l\'application de gestion des séances de tir.</p><p>Consultez le calendrier pour vous inscrire à une séance.</p>', 1);
