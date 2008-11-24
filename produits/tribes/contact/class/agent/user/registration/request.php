<?php

class extends agent_pForm
{
	public $get = '__1__:i:1';

	function control()
	{
		if ($this->get->__1__)
		{
			$sql = "SELECT *
					FROM contact
					WHERE contact_id={$this->get->__1__}
						AND statut_inscription='demande'";
			$this->data = DB()->queryRow($sql);
		}

		$this->data || p::redirect('index');
	}

	protected function composeForm($o, $f, $send)
	{
		$o->prenom_civil = $this->data->prenom_civil;
		$o->nom_civil = $this->data->nom_civil;

		//$f->add('text', 'prenom_usuel');
		$f->add('select', 'statut_inscription', array(
			'firstItem' => '-- Faites une action --',
			'item' => array('accepted' => 'accepter', 'refused' => 'refuser')
		));

		$send->attach(
		//	'prenom_usuel', '', '',
			'statut_inscription', 'faire une action', ''
		);

		return $o;
	}

	protected function save($data)
	{
		$db = DB();

		$password_token = p::strongid(8);

		$sql = "UPDATE contact
				SET statut_inscription='{$data['statut_inscription']}',
					admin_confirmed=NOW(),
					password_token='{$password_token}',
					password_token_date=NOW()
				WHERE contact_id={$this->data->contact_id}";
		$db->exec($sql);

		$email = $db->queryone("SELECT email FROM contact_email WHERE contact_id={$this->data->contact_id}");

		pMail::sendAgent(
			array('To' => $email),
			"email/user/registration/{{$data['statut_inscription']}}",
			array('password_token' => $password_token)
		);

		return 'user/registration/requests';
	}
}
