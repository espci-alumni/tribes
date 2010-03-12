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

	$type = array(
		  '0-statut_1' => "Statut n°1",
		 '10-statut_2' => "Statut n°2",
		'100-statut_3' => "Statut n°3",
	),
	$soutien = array(
		 50 =>  '50€',
		100 => '100€',
		200 => '200€',
	);

	protected $contact_id;


	function control()
	{
		$this->contact_id = tribes::getConnectedId();
		$this->contact_id || $this->contact_id = s::get('cotiser_contact_id');
		$this->contact_id || p::redirect('cotiser');
	}

	function compose($o)
	{
		$sql = "SELECT
					nom_civil,
					prenom_civil,
					promotion
				FROM contact_contact
				WHERE contact_id={$this->contact_id}";
		$o = DB()->queryRow($sql);

		$sql = "SELECT *
				FROM cotisation
				WHERE contact_id={$this->contact_id}
				ORDER BY cotisation_date";
		$o->cotisations = new loop_sql($sql, array($this, 'filterCotisation'));

		return parent::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		$item = array('item' => self::$type);
		$f->add('check', 'type_cotisation', $item);

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
			'type_cotisation',    'Merci de choisir votre catégorie de cotisation', '',
			'soutien_suggestion', '', '',
			'soutien',            '', '',
			'commentaire',        '', ''
		);

		return $o;
	}

	protected function save($data)
	{
		$data['token']           = p::strongId(8);
		$data['contact_id']      = $this->contact_id;
		$data['cotisation']      = intval($data['type_cotisation']);
		$data['cotisation_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
		$data['email']           = s::get('email') ? s::get('email') : s::get('cotiser_email');

		if ($data['soutien_suggestion']) $data['soutien'] = $data['soutien_suggestion'];
		unset ($data['soutien_suggestion']);

		if (0 == $data['cotisation'])
		{
			$data['paiement_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);

			$sql = DB()->quote(self::$type[$data['type_cotisation']]);
			$sql = "UPDATE contact_contact SET
						cotisation_date=NOW(),
						cotisation_type={$sql}
					WHERE contact_id={$this->contact_id}";
			DB()->exec($sql);
		}

		DB()->autoExecute('cotisation', $data);

		notification::send('cotisation/confirmation', $data);

		return $data['cotisation'] || $data['soutien']
			? 'cotiser/paiement/' . $data['token']
			: 'cotiser/merci';
	}

	function filterCotisation($o)
	{
		isset(self::$type[$o->type_cotisation]) && $o->type_cotisation = self::$type[$o->type_cotisation];

		return $o;
	}
}
