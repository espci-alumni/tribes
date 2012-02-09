ALTER TABLE `contact_activite` ADD `city_id` INT NOT NULL AFTER `contact_id`,
ADD `ville` VARCHAR( 255 ) NOT NULL AFTER `city_id`,
ADD `pays` VARCHAR( 255 ) NOT NULL AFTER `ville` ;

UPDATE contact_activite ac JOIN contact_adresse ad USING (adresse_id)
SET ac.ville=ad.ville, ac.pays=ad.pays, ac.city_id=ad.city_id ;
