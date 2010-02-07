<?php

class extends agent_pForm
{
	protected

	$maxage = -1,
	$requiredAuth = false;


	protected function composeForm($o, $f, $send)
	{
		$o = $this->composeContact($o, $f, $send);
		$o = $this->composeEmail($o, $f, $send);

		return $o;
	}

	protected function composeContact($o, $f, $send)
	{
		$f->add('check', 'sexe', array('item' => array(
			'F' => 'Mme, Mlle',
			'M' => 'M.'
		)));

		$f->add('name', 'nom_civil');
		$f->add('name', 'prenom_civil');
		$f->add('date', 'date_naissance');

		$send->attach(
			'sexe',           "Veuillez renseigner le champs Mme Mlle M.", '',
			'nom_civil',      "Veuillez renseigner votre nom", '',
			'prenom_civil',   "Veuillez renseigner votre prénom", '',
			'date_naissance', $this->connected_is_admin ? '' : 'Veuillez renseigner votre date de naissance', ''
		);

		return $o;
	}

	protected function composeEmail($o, $f, $send)
	{
		$f->add('email', 'email');

		$send->attach('email', "Veuillez renseigner votre email", '');

		return $o;
	}

	protected function save($data)
	{
		$db = DB();

		$sql = "SELECT contact_id, statut_inscription, photo_token, cv_token
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

			@unlink(patchworkPath('data/photo/') . $contact->photo_token . '.jpg~');
			@unlink(patchworkPath('data/cv/'   ) . $contact->cv_token    . '.pdf~');

			$data['photo_token'] = p::strongid(8);
			$data['cv_token']    = p::strongid(8);
		}

		$data += array(
			'statut_inscription' => '',
			'token'              => p::strongid(8),
		);

		$sql = new tribes_email($contact->contact_id, false);
		$sql->save($data, false);

		if ('accepted' === $contact->statut_inscription)
		{
			$token = p::strongid(8);

			$sql = "UPDATE contact_email
					SET token='{$token}',
						token_expires=NOW()+INTERVAL 5 MINUTE
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
		$reference = tribes_contact::buildReference($data);

		return "reference LIKE " . DB()->quote($reference . '%') . "
			 OR reference LIKE " . DB()->quote(substr($reference, 0, -10) . '0000-00-00%');
	}

	protected static function sqlOrderMatchingContact($data)
	{
		return 'reference DESC';
	}
}
