ALTER TABLE contact_contact
ADD ecole      VARCHAR(255) NOT NULL AFTER date_deces,
ADD promotion  VARCHAR(255) NOT NULL AFTER ecole,
ADD programme  VARCHAR(255) NOT NULL AFTER promotion,
ADD specialite VARCHAR(255) NOT NULL AFTER programme;
