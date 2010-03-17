<?php

// TODO/XXX (non critique)
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
		// TODO ! Vérifier si l'user est à jour de sa cotisation, et dans ce cas lui afficher un message adéqua

		$sql = "SELECT
					sexe,
					nom_usuel AS nom,
					prenom_usuel AS prenom
				FROM contact_contact
				WHERE contact_id={$this->contact_id}";
		$o = DB()->queryRow($sql);

		$sql = "SELECT *
				FROM cotisation
				WHERE contact_id={$this->contact_id}
					AND paiement_date
				ORDER BY cotisation_id";
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
			'cotisation'      => intval($data['type']),
			'cotisation_date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			'email'           => s::get('email') ? s::get('email') : s::get('cotisation_email'),
		);

		$data['type'] = explode('-', $data['type'], 2);
		$data['type'] = $data['type'][1];

		if ($data['soutien_suggestion']) $data['soutien'] = $data['soutien_suggestion'];
		unset($data['soutien_suggestion']);

		if (0 == $data['cotisation'])
		{
			$data['paiement_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

			$sql = DB()->quote($data['type']);
			$sql = "UPDATE contact_contact SET
						cotisation_date=NOW(),
						cotisation_type={$sql}
					WHERE contact_id={$this->contact_id}";
			DB()->exec($sql);

			notification::send('cotisation/confirmation', $data);
		}

		DB()->autoExecute('cotisation', $data);

		return $data['cotisation'] || $data['soutien']
			? 'cotiser/paiement/' . $data['token']
			: 'cotiser/merci';
	}
}
