<?php

class extends loop_user_email
{
	protected

	$table = 'adresse',
	$extraSelect = 'contact_modified, is_active, is_shared';

	function filterRow($o)
	{
		$o = parent::filterRow($o);

		(int) $o->contact_modified  || $o->contact_modified  = 0;

		return $o;
	}
}
