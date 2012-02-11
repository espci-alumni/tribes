ALTER TABLE `cotisation` CHANGE `paiement_mode` `paiement_mode` ENUM( '', 'CHQ', 'CB', 'VIR', 'ESP', 'TST', 'ERR', 'ANL', 'NSP' ) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL ;

ALTER TABLE `contact_activite` ADD `city_id` INT NOT NULL AFTER `contact_id`,
ADD `ville` VARCHAR( 255 ) NOT NULL AFTER `city_id`,
ADD `pays` VARCHAR( 255 ) NOT NULL AFTER `ville` ;

ALTER TABLE `contact_contact` ADD `corresp_adresse_id` INT UNSIGNED NULL AFTER `date_deces` ,
ADD `perso_adresse_id` INT UNSIGNED NULL AFTER `corresp_adresse_id` ,
ADD `pro_adresse_id` INT UNSIGNED NULL AFTER `perso_adresse_id` ,
ADD `principale_activite_id` INT UNSIGNED NULL AFTER `pro_adresse_id` ;

UPDATE contact_activite ac JOIN contact_adresse ad USING (adresse_id)
SET ac.ville=ad.ville, ac.pays=ad.pays, ac.city_id=ad.city_id ;

DELETE FROM ad USING contact_adresse ad JOIN contact_activite ac USING (adresse_id)
WHERE ad.adresse='' AND ad.ville_avant='' AND ad.ville_apres='' AND ad.tel_portable='' AND ad.tel_fixe='' AND ad.tel_fax='' ;

UPDATE contact_activite ac LEFT JOIN contact_adresse ad USING (adresse_id)
SET ac.adresse_id=NULL
WHERE ac.adresse_id IS NOT NULL AND ad.adresse_id IS NULL ;

UPDATE contact_contact SET corresp_adresse_id=NULL, perso_adresse_id=NULL, pro_adresse_id=NULL;

UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET corresp_adresse_id=adresse_id WHERE a.is_active = 1 AND a.is_obsolete=0;
UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET corresp_adresse_id=adresse_id WHERE a.is_active = 1 AND a.is_obsolete=0 AND a.pays='France';
UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET corresp_adresse_id=adresse_id WHERE a.is_active = 1 AND a.is_obsolete=0 AND a.pays='France' AND (a.description LIKE '%perso%' OR a.description = 'domicile');

UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET pro_adresse_id=adresse_id WHERE a.is_obsolete=0 AND adresse_id IN (SELECT adresse_id FROM contact_activite WHERE a.is_obsolete=0 AND (date_fin='000-00-00' OR date_fin > NOW()));
UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET pro_adresse_id=adresse_id WHERE a.is_obsolete=0 AND description LIKE '%pro%';

UPDATE contact_contact c SET perso_adresse_id=corresp_adresse_id WHERE pro_adresse_id IS NULL OR corresp_adresse_id != pro_adresse_id;
UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET perso_adresse_id=adresse_id WHERE a.is_obsolete=0 AND (description LIKE '%perso%' OR description = 'domicile') AND perso_adresse_id IS NULL;

UPDATE contact_contact SET corresp_adresse_id=perso_adresse_id WHERE corresp_adresse_id IS NULL;
UPDATE contact_contact SET corresp_adresse_id=pro_adresse_id WHERE corresp_adresse_id IS NULL;

UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET corresp_adresse_id=adresse_id WHERE corresp_adresse_id IS NULL AND a.is_obsolete=0 AND a.pays='France';
UPDATE contact_contact c JOIN contact_adresse a USING (contact_id) SET corresp_adresse_id=adresse_id WHERE corresp_adresse_id IS NULL AND a.is_obsolete=0;
