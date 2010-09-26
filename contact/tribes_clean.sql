DELETE FROM `contact_contact` WHERE ...
DELETE FROM `contact_activite` WHERE contact_id NOT IN (SELECT contact_id FROM contact_contact);
DELETE FROM `contact_adresse` WHERE contact_id NOT IN (SELECT contact_id FROM contact_contact);
DELETE FROM `contact_email` WHERE contact_id NOT IN (SELECT contact_id FROM contact_contact);
DELETE FROM `contact_alias` WHERE contact_id NOT IN (SELECT contact_id FROM contact_contact);
DELETE FROM `contact_activite` WHERE contact_id NOT IN (SELECT contact_id FROM contact_contact);
DELETE FROM `contact_historique` WHERE contact_id NOT IN (SELECT contact_id FROM contact_contact);
DELETE FROM `contact_affiliation` WHERE activite_id NOT IN (SELECT activite_id FROM contact_activite);
DELETE FROM `contact_organisation` WHERE organisation_id NOT IN (SELECT organisation_id FROM contact_affiliation);
