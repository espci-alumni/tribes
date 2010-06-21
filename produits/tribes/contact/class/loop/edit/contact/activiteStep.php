<?php

class extends loop_edit_contact_activite
{
	function __construct($f, $send)
	{
		$this->allowAddDel = false;
		$this->editAdresse = false;
		$this->send = $send;

		$default = array(
			'activite_id' => 0,
		);

		loop_edit::__construct($f, new loop_array(array($default), 'filter_rawArray'));
	}
}
