<?php

class extends agent_pForm
{
	protected

	$maxage = -1,
	$requiredAuth = false,
	$mandatoryEmail = true;

	protected static

	$altern_case_rx = ".*[A-Z][^A-Z\s]+",
	$altern_case_msg = "Merci de respecter minuscules, majuscules et accents pour vos nom et prénom";


	protected function composeForm($o, $f, $send)
	{
		$this->composeFormContact($f, $send);
		$this->composeFormEmail($f, $send);

		return $o;
	}

	protected function composeFormContact($f, $send)
	{
		$f->add('check', 'sexe', array('item' => array(
			'F' => 'Mme, Mlle',
			'M' => 'M.'
		)));

		$f->add('text', 'nom_civil',    self::$altern_case_rx);
		$f->add('text', 'prenom_civil', self::$altern_case_rx);
		$f->add('date', 'date_naissance');

		$send->attach(
			'sexe',           "Veuillez renseigner le champs Mme Mlle M.",   '',
			'nom_civil',      "Veuillez renseigner votre nom",               self::$altern_case_msg,
			'prenom_civil',   "Veuillez renseigner votre prénom",            self::$altern_case_msg,
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

		if (!$contact)
		{
			$sql = $data + array(
				'nom_etudiant' => $data['nom_civil'],
				'nom_usuel'    => $data['nom_civil'],
				'prenom_usuel' => $data['prenom_civil'],
				'origine' => 'registration',
				'login' => tribes::buildLogin($data)
			);

			unset($sql['email']);

			$db->autoExecute('contact_contact', $sql);

			$contact = (object) array(
				'contact_id' => $db->lastInsertId(),
				'statut_inscription' => '',
			);
		}
		else if ('accepted' !== $contact->statut_inscription)
		{
			$sql = "UPDATE contact_email   SET contact_data=''
					WHERE contact_id={$contact->contact_id}";
			$db->exec($sql);

			$sql = "UPDATE contact_adresse SET contact_data=''
					WHERE contact_id={$contact->contact_id}";
			$db->exec($sql);
		}

		$data += array(
			'statut_inscription' => '',
			'token'              => p::strongid(8),
			'origine'            => 'registration',
		);

		$sql = new tribes_email($contact->contact_id, false);
		$sql->save($data, false);

		if ('accepted' === $contact->statut_inscription)
		{
			$sql = "SELECT 1
					FROM contact_email
					WHERE contact_id={$contact->contact_id}
						AND admin_confirmed
						AND contact_confirmed
						AND is_obsolete<=0
					LIMIT 1";
			if ($db->queryOne($sql))
			{
				s::set('password_contact_id', $contact->contact_id);
				return 'registration/collision';
			}
		}

		// token est mis à jour avec la même valeur que celui de l'email.
		// De cette façon, on peut savoir, sur la base de ce token, quel email
		// parmi ceux disponibles est à l'origine de la dernière inscription.

		$sql = new tribes_contact($contact->contact_id, false);
		$sql->save($data, 'registration/receipt');

		s::set('registration_token', $data['token']);

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
