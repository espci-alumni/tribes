<?php

class extends agent_pForm
{
	protected

	$maxage = -1,
	$mandatoryEmail = true;

	protected function composeForm($f, $send)
	{
		$this->composeFormContact($f, $send);
		$this->composeFormEmail($f, $send);
	}

	protected function composeFormContact($f, $send)
	{
		$f->add('check', 'sexe', array('item' => array(
			'F' => 'Mme, Mlle',
			'M' => 'M.'
		)));

		$altern_case_rx = ".*[A-Z][^A-Z\s]+";
		$altern_case_msg = "Merci de respecter minuscules, majuscules et accents pour vos nom et prénom";

		$f->add('text', 'nom_civil', $altern_case_rx);
		$f->add('text', 'prenom_civil', $altern_case_rx);
		$f->add('date', 'date_naissance');

		$send->attach(
			'sexe',           "Veuillez renseigner le champs Mme Mlle M.",   '',
			'nom_civil',      "Veuillez renseigner votre nom",               $altern_case_msg,
			'prenom_civil',   "Veuillez renseigner votre prénom",            $altern_case_msg,
			'date_naissance', 'Veuillez renseigner votre date de naissance', ''
		);
	}

	protected function composeFormEmail($f, $send)
	{
		$f->add('email', 'email');

		$send->attach('email', $this->mandatoryEmail ? "Veuillez renseigner votre email" : '', '');
	}

	protected function save($data)
	{
		$db = DB();

		$sql = "SELECT contact_id, statut_inscription
				FROM contact_contact c
				WHERE " . $this->sqlWhereMatchingContact($data) . "
				ORDER BY " . $this->sqlOrderMatchingContact($data) . "
				LIMIT 1";
		$contact = $db->queryRow($sql);

		$email = array(
			'email' => $data['email'],
			'description' => '',
		);

		unset($data['email']);

		if (!$contact)
		{
			$db->autoExecute(
				'contact_contact',
				$data + array(
					'origine' => 'registration',
					'login' => tribes::buildLogin($data)
				)
			);

			$contact = (object) array(
				'contact_id' => $db->lastInsertId(),
				'statut_inscription' => '',
			);
		}
		else if ('accepted' !== $contact->statut_inscription)
		{
			$sql = "UPDATE contact_email   SET contact_confirmed_data=''
					WHERE contact_id={$contact->contact_id}";
			$db->exec($sql);

			$sql = "UPDATE contact_adresse SET contact_confirmed_data=''
					WHERE contact_id={$contact->contact_id}";
			$db->exec($sql);
		}

		$email += array(
			'contact_id' => $contact->contact_id,
			'token'      => p::strongid(8),
			'origine'    => 'registration',
			'contact_confirmed_data' => serialize($email),
		);

		$sql = "INSERT INTO contact_email (" . implode(',', array_keys($email)) . ", token_expires)
				VALUES ('" . implode("','", $email) . "', NOW() + INTERVAL " . tribes::PENDING_PERIOD . ")
				ON DUPLICATE KEY UPDATE
					token=VALUES(token),
					token_expires=VALUES(token_expires),
					contact_confirmed_data=VALUES(contact_confirmed_data)";
		$db->exec($sql);


		if ('accepted' === $contact->statut_inscription)
		{
			$sql = "SELECT 1
					FROM contact_email
					WHERE contact_id={$contact->contact_id}
						AND is_active=1
						AND admin_confirmed
						AND is_obsolete<1
					LIMIT 1";
			if ($db->queryOne($sql))
			{
				s::set('password_contact_id', $contact->contact_id);
				return 'registration/collision';
			}
		}

		// password_token est mis à jour avec la même valeur que celui de l'email.
		// De cette façon, on peut savoir, sur la base de ce token, quel email
		// parmi ceux disponibles est à l'origine de la dernière inscription.

		$sql = "UPDATE contact_contact
				SET statut_inscription='',
					date_naissance='{$data['date_naissance']}',
					password_token='{$email['token']}',
					password_token_expires=NOW() + INTERVAL " . tribes::PENDING_PERIOD . ",
					contact_confirmed=NOW(),
					contact_confirmed_data=" . $db->quote(serialize($data)) . "
				WHERE contact_id={$contact->contact_id}";
		$db->exec($sql);

		pMail::sendAgent(
			array('To' => $email['email']),
			'email/registration/receipt',
			array('password_token' => $email['token'])
		);

		s::set('registration_token', $email['token']);

		return 'registration/receipt';
	}

	protected static function sqlWhereMatchingContact($data)
	{
		$login = tribes::buildLogin($data);

		return "login LIKE " . DB()->quote($login . '%') . "
			 OR login LIKE " . DB()->quote(substr($login, 0, -10) . '0000-00-00%');
	}

	protected static function sqlOrderMatchingContact($data)
	{
		return 'login DESC';
	}
}
