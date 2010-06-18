<?php

class extends loop_edit_contact_adresse
{
	function __construct($f, $send)
	{
		$this->allowAddDel = false;
		$this->send = $send;

		$default = array(
			'adresse_id'  => 0,
			'description' => 'Personnel',
		);

		loop_edit::__construct($f, new loop_array(array($default), 'filter_rawArray'));
	}
}
