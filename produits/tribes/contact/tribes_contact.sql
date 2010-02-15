-- phpMyAdmin SQL Dump
-- version 2.9.0.3
-- http://www.phpmyadmin.net
-- 
-- Serveur: localhost
-- Généré le : Mercredi 17 Décembre 2008 à 20:09
-- Version du serveur: 5.0.27
-- Version de PHP: 5.2.0
-- 
-- Base de données: 'tribes'
-- 

-- --------------------------------------------------------

-- 
-- Structure de la table 'contact_adresse'
-- 

CREATE TABLE contact_adresse (
  adresse_id int(10) unsigned NOT NULL auto_increment,
  contact_id int(10) unsigned NOT NULL,
  description varchar(255) NOT NULL,
  adresse varchar(255) NOT NULL,
  ville_avant varchar(255) NOT NULL,
  ville varchar(255) NOT NULL,
  ville_apres varchar(255) NOT NULL,
  pays varchar(255) NOT NULL,
  ville_id int(11) NOT NULL,
  email_list text NOT NULL,
  tel_portable varchar(255) NOT NULL,
  tel_fixe varchar(255) NOT NULL,
  tel_fax varchar(255) NOT NULL,
  is_active tinyint(4) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_data blob NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  PRIMARY KEY  (adresse_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'contact_alias'
-- 

CREATE TABLE contact_alias (
  login varbinary(255) NOT NULL,
  contact_id int(10) unsigned NOT NULL,
  PRIMARY KEY  (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'contact_contact'
-- 

CREATE TABLE contact_contact (
  contact_id int(10) unsigned NOT NULL auto_increment,
  sexe enum('M','F') NOT NULL,
  prenom_usuel varchar(255) NOT NULL,
  nom_usuel varchar(255) NOT NULL,
  prenom_civil varchar(255) NOT NULL,
  nom_civil varchar(255) NOT NULL,
  nom_etudiant varchar(255) NOT NULL,
  date_naissance date NOT NULL,
  date_deces date NOT NULL,
  conjoint_contact_id int(10) unsigned default NULL,
  statut_inscription enum('','demande','accepted') NOT NULL,
  login varbinary(255) NOT NULL,
  `password` varbinary(40) NOT NULL,
  token varbinary(8) default NULL,
  token_expires datetime NOT NULL,
  is_active tinyint(4) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_data blob NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  PRIMARY KEY  (contact_id),
  UNIQUE KEY login (login)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'contact_email'
-- 

CREATE TABLE contact_email (
  email_id int(10) unsigned NOT NULL auto_increment,
  contact_id int(10) unsigned NOT NULL,
  email varbinary(255) NOT NULL,
  token varbinary(8) default NULL,
  token_expires datetime NOT NULL,
  is_active tinyint(4) NOT NULL,
  is_obsolete tinyint(4) NOT NULL,
  admin_confirmed datetime NOT NULL,
  contact_confirmed datetime NOT NULL,
  contact_data blob NOT NULL,
  origine varchar(255) NOT NULL,
  sort_key int(10) unsigned NOT NULL,
  PRIMARY KEY  (email_id),
  UNIQUE KEY contact_id (contact_id,email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'contact_historique'
-- 

CREATE TABLE contact_historique (
  historique_id int(10) unsigned NOT NULL auto_increment,
  historique varchar(255) NOT NULL,
  contact_id int(10) unsigned NOT NULL,
  origine_contact_id int(10) unsigned NOT NULL,
  date_contact datetime NOT NULL,
  details text NOT NULL,
  PRIMARY KEY  (historique_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

-- 
-- Structure de la table 'ville'
-- 

CREATE TABLE ville (
  ville_id int(11) NOT NULL,
  latitude float NOT NULL,
  longitude float NOT NULL,
  div_code varbinary(5) NOT NULL,
  div1 varchar(255) NOT NULL,
  div2 varchar(255) NOT NULL,
  pays varchar(255) NOT NULL,
  PRIMARY KEY  (ville_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

