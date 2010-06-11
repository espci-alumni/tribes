<?php

class extends agent_registration_collision
{
	public $get = array();

	function control() {}

	protected function composeForm($o, $f, $send)
	{
		$f->add('name', 'prenom_civil');
		$f->add('email', 'email');

		$send->attach(
			'prenom_civil', 'Veuillez renseigner votre prénom', '',
			'email',        'Veuillez renseigner votre email', ''
		);

		return $o;
	}

	protected function save($data)
	{
		$sql = agent_registration::sqlSelectMatchingContact($data);

		$this->data = (object) array(
			'contact_id' => DB()->queryOne($sql),
			'email'      => $data['email'],
		);

		return $this->data->contact_id
			? parent::save($data)
			: 'registration/password/error';
	}
}
