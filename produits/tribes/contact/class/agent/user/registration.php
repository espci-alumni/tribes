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

		$f->add('text', 'nom_civil', $altern_case_rx);
		$f->add('text', 'prenom_civil', $altern_case_rx);
		$f->add('date', 'date_naissance');
		$f->add('email', 'email');

		$send->attach(
			'sexe',           "Veuillez renseigner le champs Mme Mlle M.",   '',
			'nom_civil',      "Veuillez renseigner votre nom",               $altern_case_msg,
			'prenom_civil',   "Veuillez renseigner votre prénom",            $altern_case_msg,
			'email',          "Veuillez renseigner votre email",             '',
			'date_naissance', 'Veuillez renseigner votre date de naissance', ''
		);

		return $o;
	}

	protected function save($data)
	{
		$db = DB();

		$sql = "SELECT contact_id, statut_inscription
				FROM contact c
				WHERE " . $this->sqlWhereMatchingContact($data) . "
				ORDER BY " . $this->sqlOrderMatchingContact($data) . "
				LIMIT 1";
		$contact = $db->queryRow($sql);

		$email['email'] = $data['email'];
		unset($data['email']);

		if (!$contact)
		{
			$db->autoExecute(
				'contact',
				$data + array(
					'origine' => 'registration'
					'login' => self::getLogin($data),
				)
			);

			$contact = (object) array(
				'contact_id' => $db->lastInsertId(),
				'statut_inscription' => '',
			);
		}

		$email += array(
			'contact_id' => $contact->contact_id,
			'token'      => p::strongid(8),
			'origine'    => 'registration',
		);

		$sql = "INSERT INTO contact_email (" . implode(',', array_keys($email)) . ", token_date)
				VALUES ('" . implode("','", $email) . "', NOW())
				ON DUPLICATE KEY UPDATE token=VALUES(token), token_date=VALUES(token_date)";
		$db->exec($sql);

		if ('accepted' === $contact->statut_inscription)
		{
			return 'user/registration/receipt/collision';
		}
		else
		{
			// password_token est mis à jour avec la même valeur que celui de l'email.
			// De cette façon, on peut savoir, sur la base de ce token, quel email
			// parmi ceux disponibles est à l'origine de la dernière inscription.

			$sql = "UPDATE contact
					SET statut_inscription='',
						password_token='{$email['token']}',
						password_token_date=NOW(),
						contact_confirmed=NOW(),
						contact_confirmed_data=" . $db->quote(serialize($data)) . "
					WHERE contact_id={$contact->contact_id}";
			$db->exec($sql);

			pMail::sendAgent(
				array('To' => $email['email']),
				'email/user/registration/receipt',
				array('password_token' => $email['token'])
			);

			return 'user/registration/receipt';
		}
	}

	protected static function sqlWhereMatchingContact($data)
	{
		$login = self::getLogin($data);

		return "login LIKE " . DB()->quote($login . '%') . "
			 OR login LIKE " . DB()->quote(substr($login, 0, -10) . '0000-00-00%')
	}

	protected static function sqlOrderMatchingContact($data)
	{
		return 'login DESC';
	}

	protected static function getLogin($data)
	{
		return tribes::filterLogin($data['prenom_civil'])
			. '.' . tribes::filterLogin($data['nom_civil'])
			. '.' . $data['date_naissance'];
	}
}
