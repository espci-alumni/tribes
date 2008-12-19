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
		$o = $this->composeFormContact($o, $f, $send);
		$o = $this->composeFormEmail($o, $f, $send);

		return $o;
	}

	protected function composeFormContact($o, $f, $send)
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

		return $o;
	}

	protected function composeFormEmail($o, $f, $send)
	{
		$f->add('email', 'email');

		$send->attach('email', $this->mandatoryEmail ? "Veuillez renseigner votre email" : '', '');

		return $o;
	}

	protected function save($data)
	{
		$db = DB();

		$sql = "SELECT contact_id, statut_inscription, photo_token
				FROM contact_contact c
				WHERE " . $this->sqlWhereMatchingContact($data) . "
				ORDER BY " . $this->sqlOrderMatchingContact($data) . "
				LIMIT 1";
		$contact = $db->queryRow($sql);

		$data += array(
			'nom_etudiant' => $data['nom_civil'],
			'nom_usuel'    => $data['nom_civil'],
			'prenom_usuel' => $data['prenom_civil'],
		);

		if (!$contact)
		{
			$sql = new tribes_contact(0);
			$sql->save(
				$data + array('origine' => 'registration'),
				false
			);

			$contact = (object) array(
				'contact_id' => $sql->contact_id,
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

			@unlink(patchworkPath('data/photo/') . $contact->photo_token . '.contact.jpg');
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
			$token = p::strongid(8);

			$sql = "UPDATE contact_email
					SET token='{$token}',
						token_expires=NOW()+INTERVAL 5 MINUTES
					WHERE contact_id={$contact->contact_id}
						AND admin_confirmed
						AND contact_confirmed
						AND is_obsolete<=0
					LIMIT 1";
			if ($db->exec($sql))
			{
				return "registration/collision/{$token}";
			}
		}

		// token est mis à jour avec la même valeur que celui de l'email.
		// De cette façon, on peut savoir, sur la base de ce token, quel email
		// parmi ceux disponibles est à l'origine de la dernière inscription.

		$sql = new tribes_contact($contact->contact_id, false);
		$sql->save($data, 'registration/receipt');

		return 'registration/receipt/' . substr($data['token'], 0, 4);
	}

	protected static function sqlWhereMatchingContact($data)
	{
		$login = tribes_contact::buildLogin($data);

		return "login LIKE " . DB()->quote($login . '%') . "
			 OR login LIKE " . DB()->quote(substr($login, 0, -10) . '0000-00-00%');
	}

	protected static function sqlOrderMatchingContact($data)
	{
		return 'login DESC';
	}
}
