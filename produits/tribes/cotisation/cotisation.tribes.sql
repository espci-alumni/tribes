ALTER TABLE contact_contact
ADD cotisation_date DATETIME     NOT NULL AFTER statut_inscription,
ADD cotisation_type VARCHAR(255) NOT NULL AFTER cotisation_date;
