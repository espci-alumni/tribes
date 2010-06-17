<?php

// TODO : cas mot de passe perdu
// Dans le cas où cette page s'affiche suite à un passage par registration
// alors que le contact est déjà accepté, il faudrait ajouter une question
// dans le formulaire "Envoyer un nouveau mot de passe ? O/N".
// Et envoyer un lien de récuparation de mot de passe, le cas échéant.
// cf. notification::send('user/password/request',...) dans agent/registration/collision

class extends agent_pForm
{
	protected static

	$soutien = array(
		 50 =>  '50€',
		100 => '100€',
		200 => '200€',
	);

	protected $contact_id;


	function control()
	{
		$this->contact_id = tribes::getConnectedId();
		$this->contact_id || $this->contact_id = s::get('cotisation_contact_id');
		$this->contact_id || p::redirect('cotiser');

		$this->data = s::get('cotisation_bulletin');
	}

	function compose($o)
	{
		$sql = "SELECT email
				FROM contact_email
				WHERE contact_id={$this->contact_id}
					AND is_active
					AND contact_confirmed
				LIMIT 1";

		$sql = "SELECT
					sexe,
					nom_usuel AS nom,
					prenom_usuel AS prenom,
					({$sql}) AS email,
					IF (cotisation_expires>=NOW()+INTERVAL 1 DAY, cotisation_expires, 0) AS cotisation_expires
				FROM contact_contact
				WHERE contact_id={$this->contact_id}";
		$o = DB()->queryRow($sql);

		s::get('cotisation_email') || s::set('cotisation_email', $o->email);

		$sql = "SELECT *
				FROM cotisation
				WHERE contact_id={$this->contact_id}
					AND paiement_date
				ORDER BY cotisation_id DESC";
		$o->cotisations = new loop_sql($sql);

		return parent::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		$item = array('item' => tribes::getCotisationType());
		$f->add('check', 'type', $item);

		$item= array('item' => self::$soutien + array(
			0 => (object) array(
				'caption' => 'Autre',
				'onclick' => 'this.form.f_soutien.focus()',
			)
		));
		$f->add('check', 'soutien_suggestion', $item);

		$f->add('text',     'soutien', array('valid' => 'int', 0));
		$f->add('textarea', 'commentaire');

		$send->attach(
			'type',               'Merci de choisir votre catégorie de cotisation', '',
			'soutien_suggestion', '', '',
			'soutien',            '', '',
			'commentaire',        '', ''
		);

		return $o;
	}

	protected function save($data)
	{
		s::set('cotisation_bulletin', $data);

		$data += array(
			'token'           => p::strongId(8),
			'contact_id'      => $this->contact_id,
			'cotisation_date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			'email'           => s::get('cotisation_email'),
		);

		list($data['nb_mois'], $data['cotisation'], $data['type']) = explode('-', $data['type'], 3);

		if ($data['soutien_suggestion']) $data['soutien'] = $data['soutien_suggestion'];
		unset($data['soutien_suggestion']);

		if (0 == $data['cotisation'])
		{
			$data['paiement_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

			$sql = "UPDATE contact_contact SET
						cotisation_expires=NOW()+INTERVAL {$data['nb_mois']} MONTH
					WHERE contact_id={$this->contact_id}";
			DB()->exec($sql);

			notification::send('user/cotisation', $data);
		}

		DB()->autoExecute('cotisation', $data);

		return $data['cotisation'] || $data['soutien']
			? 'cotiser/paiement/' . $data['token']
			: 'cotiser/merci';
	}
}
