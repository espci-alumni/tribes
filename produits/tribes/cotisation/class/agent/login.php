<?php

class extends self
{
	protected function login($contact)
	{
		parent::login($contact);

		$sql = "SELECT cotisation_date>=NOW() - INTERVAL 1 YEAR
				FROM contact_contact
				WHERE contact_id={$contact->contact_id}";
		s::set('is_cotisant', DB()->queryOne($sql));
	}
}
