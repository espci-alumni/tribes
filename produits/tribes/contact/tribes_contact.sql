-- phpMyAdmin SQL Dump
-- version 3.3.2deb1
-- http://www.phpmyadmin.net
--
-- Serveur: localhost
-- Généré le : Ven 02 Juillet 2010 à 16:56
-- Version du serveur: 5.1.41
-- Version de PHP: 5.3.2-1ubuntu4.2

SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: 'sandbox_tribes'
--

-- --------------------------------------------------------

--
-- Structure de la table 'city'
--

CREATE TABLE city (
  city_id int(11) NOT NULL,
  latitude float NOT NULL,
  longitude float NOT NULL,
  divcode varbinary(5) NOT NULL,
  div1 varchar(255) NOT NULL,
  div2 varchar(255) NOT NULL,
  country varchar(255) NOT NULL,
  PRIMARY KEY (city_id)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_activite'
--

CREATE TABLE contact_activite (
  activite_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  contact_id int(10) unsigned NOT NULL,
  adresse_id int(10) unsigned DEFAULT NULL,
  service varchar(255) NOT NULL,
  titre varchar(255) NOT NULL,
  fonction varchar(255) NOT NULL,
  secteur varchar(255) NOT NULL,
  statut varchar(255) NOT NULL,
  date_debut date NOT NULL,
  date_fin date NOT NULL,
  site_web varchar(100) NOT NULL,
  keyword text NOT NULL,
  is_shared tinyint(4) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_modified datetime NOT NULL,
  contact_data blob NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  PRIMARY KEY (activite_id),
  KEY contact_id (contact_id),
  KEY adresse_id (adresse_id),
  KEY titre (titre(5))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_adresse'
--

CREATE TABLE contact_adresse (
  adresse_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  contact_id int(10) unsigned NOT NULL,
  description varchar(255) NOT NULL,
  adresse varchar(255) NOT NULL,
  ville_avant varchar(255) NOT NULL,
  ville varchar(255) NOT NULL,
  ville_apres varchar(255) NOT NULL,
  pays varchar(255) NOT NULL,
  city_id int(11) NOT NULL,
  email_list text NOT NULL,
  tel_portable varchar(255) NOT NULL,
  tel_fixe varchar(255) NOT NULL,
  tel_fax varchar(255) NOT NULL,
  is_shared tinyint(4) NOT NULL,
  is_active tinyint(4) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_modified datetime NOT NULL,
  contact_data blob NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  PRIMARY KEY (adresse_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_affiliation'
--

CREATE TABLE contact_affiliation (
  activite_id int(10) NOT NULL,
  organisation_id int(10) NOT NULL,
  is_admin_confirmed tinyint(4) NOT NULL,
  sort_key int(10) NOT NULL,
  KEY activite_id (activite_id,is_admin_confirmed),
  KEY organisation_id (organisation_id,is_admin_confirmed)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_alias'
--

CREATE TABLE contact_alias (
  alias varchar(255) CHARACTER SET ascii COLLATE ascii_bin NOT NULL,
  contact_id int(10) unsigned NOT NULL,
  hidden tinyint(4) NOT NULL,
  PRIMARY KEY (alias)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_contact'
--

CREATE TABLE contact_contact (
  contact_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  sexe enum('M','F') NOT NULL,
  prenom_usuel varchar(255) NOT NULL,
  nom_usuel varchar(255) NOT NULL,
  prenom_civil varchar(255) NOT NULL,
  nom_civil varchar(255) NOT NULL,
  nom_etudiant varchar(255) NOT NULL,
  date_naissance date NOT NULL,
  date_deces date NOT NULL,
  cotisation_expires date NOT NULL,
  statut_activite varchar(255) NOT NULL,
  ecole varchar(255) NOT NULL,
  promotion varchar(255) NOT NULL,
  programme varchar(255) NOT NULL,
  specialite varchar(255) NOT NULL,
  conjoint_email varchar(255) NOT NULL,
  acces enum('','membre','admin') NOT NULL,
  etape_suivante varchar(255) NOT NULL,
  login varchar(255) NOT NULL,
  `user` varchar(255) NOT NULL,
  `password` varchar(40) NOT NULL,
  photo_token varchar(8) NOT NULL,
  cv_token varchar(8) NOT NULL,
  cv_text text NOT NULL,
  is_active tinyint(4) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_modified datetime NOT NULL,
  contact_data text NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  PRIMARY KEY (contact_id),
  KEY origine (origine),
  KEY `user` (`user`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_email'
--

CREATE TABLE contact_email (
  email_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  contact_id int(10) unsigned NOT NULL,
  email varchar(255) NOT NULL,
  token varchar(50) DEFAULT NULL,
  token_expires datetime NOT NULL,
  is_active tinyint(4) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_data blob NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  PRIMARY KEY (email_id),
  UNIQUE KEY contact_id (contact_id,email)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_historique'
--

CREATE TABLE contact_historique (
  historique_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  historique varchar(255) NOT NULL,
  contact_id int(10) unsigned NOT NULL,
  origine_contact_id int(10) unsigned NOT NULL,
  date_contact datetime NOT NULL,
  details text NOT NULL,
  PRIMARY KEY (historique_id)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'contact_organisation'
--

CREATE TABLE contact_organisation (
  organisation_id int(10) unsigned NOT NULL AUTO_INCREMENT,
  organisation varchar(255) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_modified datetime NOT NULL,
  contact_data blob NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) NOT NULL,
  PRIMARY KEY (organisation_id),
  KEY organisation (organisation(5))
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'cotisation'
--

CREATE TABLE cotisation (
  cotisation_id int(11) NOT NULL AUTO_INCREMENT,
  contact_id int(11) NOT NULL,
  cotisation_date datetime NOT NULL,
  token varbinary(8) NOT NULL,
  `type` varchar(255) NOT NULL,
  nb_mois int(10) unsigned NOT NULL,
  cotisation decimal(10,2) unsigned NOT NULL,
  soutien decimal(10,2) NOT NULL,
  email varbinary(255) NOT NULL,
  commentaire text NOT NULL,
  paiement_euro decimal(10,2) unsigned NOT NULL,
  paiement_date datetime NOT NULL,
  paiement_mode enum('','CHQ','CB','VIR','ESP','TST','ERR','ANL') NOT NULL,
  paiement_ref text NOT NULL,
  PRIMARY KEY (cotisation_id),
  KEY token (token)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'item_lists'
--

CREATE TABLE item_lists (
  `type` varchar(20) NOT NULL,
  `group` varchar(255) NOT NULL,
  `value` varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  KEY `type` (`type`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table 'item_suggestions'
--

CREATE TABLE item_suggestions (
  `type` varchar(20) NOT NULL,
  suggestion varchar(50) NOT NULL,
  UNIQUE KEY `type` (`type`,suggestion)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
