<?php

class extends agent
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	function control()
	{
		$this->get->__1__ || p::forbidden();

		$db = DB();

		$sql = "SELECT email, description
				FROM contact_email
				WHERE token='{$this->get->__1__}'
					AND token_expires>=NOW()";
		$sql = (array) $db->queryRow($sql);

		$sql || p::redirect('error/token');

		$sql = serialize($sql);

		$sql = "UPDATE contact_email
				SET contact_confirmed=NOW(),
					token=NULL,
					is_obsolete=0,
					contact_confirmed_data=" . $db->quote($sql) . "
				WHERE token='{$this->get->__1__}'
					AND token_expires>=NOW()";
		DB()->exec($sql) || p::redirect('error/token');

		// XXX Si non-admin-confirmé => authentification obligatoire
	}
}
