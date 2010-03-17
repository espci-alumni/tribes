<?php

class extends agent_tpe___x5Fadapter
{
	public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

	function compose($o)
	{
		$sql = "SELECT
					cotisation + soutien - paiement_euro AS euro,
					token,
					email
				FROM cotisation
				WHERE token='{$this->get->__1__}'";
		$o = DB()->queryRow($sql);

		return $o ? self::composeTpe($o, 'C/' . $o->token, $o->euro, $o->email) : array();
	}
}
