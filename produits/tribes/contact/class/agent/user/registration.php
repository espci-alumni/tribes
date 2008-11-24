<?php

class extends agent_pForm
{
	protected $maxage = -1;

	protected function composeForm($o, $f, $send)
	{
		$f->add('check', 'sexe', array('item' => array(
			'F' => 'Mme, Mlle',
			'M' => 'M.'
		)));

		$altern_case_rx = ".*[A-Z][^A-Z\s]+";
		$altern_case_msg = "Merci de respecter minuscules, majuscules et accents pour vos nom et prénom";

		$f->add('text', 'nom_etudiant', $altern_case_rx);
		$f->add('text', 'prenom_usuel', $altern_case_rx);
		$f->add('email', 'email');

		$send->attach(
			'sexe', "Veuillez renseigner le champs Mme Mlle M.", '',
			'nom_etudiant', "Veuillez renseigner votre nom (à l'école)", $altern_case_msg,
			'prenom_usuel', "Veuillez renseigner votre prénom usuel", $altern_case_msg,
			'email', "Veuillez renseigner votre email", ''
		);

		return $o;
	}

	protected function save($data)
	{
		$db = DB();
		
		$sql = "SELECT contact_id, statut_inscription
				FROM contact c
				WHERE " . $this->buildSqlMatchingContact($data);
		$contact = $db->queryRow($sql);

		if (!$contact)
		{
			$db->autoExecute('contact', $data + array('origine' => 'registration'));
			$contact = (object) array(
				'contact_id' => $db->lastInsertId(),
				'statut_inscription' => 'aucune',
			);
		}

		$email = array(
			'contact_id' => $contact->contact_id,
			'email'      => $data['email'],
			'token'      => p::strongid(8),
		);

		unset($data['email']);

		if ('accepted' === $contact->statut_inscription)
		{
			$sql = "INSERT IGNORE INTO contact_email (" . implode(',', array_keys($email)) . ", token_date)
					VALUES ('" . implode("','", $email) . "', NOW())";
			if ($db->exec($sql))
			{
				// Procédure de contre-vérification du mail
			}

			return 'user/registration/receipt/collision';
		}
		else
		{
			$sql = "REPLACE INTO contact_email (" . implode(',', array_keys($email)) . ", token_date)
					VALUES ('" . implode("','", $email) . "', NOW())";
			$db->exec($sql);

			$password_token = p::strongid(8);

			$sql = "UPDATE contact
					SET password_token='{$password_token}',
						password_token_date=NOW(),
						contact_confirmed=NOW(),
						contact_confirmed_data=" . $db->quote(serialize($data)) . "
					WHERE contact_id={$contact->contact_id}";
			$db->exec($sql);

			pMail::sendAgent(
				array('To' => $email['email']),
				'email/user/registration/receipt',
				array('password_token' => $password_token)
			);

			if ('demande' === $contact->statut_inscription)
			{
				//re-notification administrateur
			}

			return 'user/registration/receipt';
		}
	}

	protected function buildSqlMatchingContact($data)
	{
		return "nom_etudiant=" . DB()->quote($data['nom_etudiant']) . "
					AND prenom_usuel=" . DB()->quote($data['prenom_usuel']);;
	}
}
