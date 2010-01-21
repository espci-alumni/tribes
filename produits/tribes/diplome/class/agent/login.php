<?php

class extends self
{
	protected function login($contact)
	{
		parent::login($contact);

		$sql = "SELECT promotion FROM contact_contact WHERE contact_id={$contact->contact_id}";
		s::set('promotion', DB()->queryOne($sql));
	}
}
