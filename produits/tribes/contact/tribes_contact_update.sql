ALTER contact_contact
ADD acces enum('','membre','admin'),
DROP COLUMN reference,
DROP COLUMN statut_inscription;
