<?php

class extends agent_user_edit
{
	public $get = array('__1__:i:1' => 0);

	protected

	$requiredAuth = 'admin',
	$confirmed = true;

	protected static

	$acces = array(
		'membre' => 'Membre',
		'admin'  => 'Administrateur',
	);


	function control()
	{
		$this->contact_id = $this->get->__1__;

		parent::control();
	}

	protected function composeForm($o, $f, $send)
	{
		if (!empty($this->data->login))
		{
			$o = $this->composeLogin($o, $f, $send);
			$o = $this->composeNewPassword($o, $f, $send);
		}

		$o = $this->composeContact($o, $f, $send);
		$o = $this->composeEmail($o, $f, $send);
		$o = $this->composeAdresse($o, $f, $send);
		$o = $this->composeActivite($o, $f, $send);

		$this->emails   ->adminMode = true;
		$this->adresses ->adminMode = true;
		$this->activites->adminMode = true;

		return $o;
	}

	protected function composeContact($o, $f, $send)
	{
		$o = parent::composeContact($o, $f, $send);

		$f->add('select', 'acces', array('item' => self::$acces));

		$send->attach(
			'acces', "Veuillez spécifier le type d'accès fourni à l'utilisateur", ''
		);

		return $o;
	}
}
