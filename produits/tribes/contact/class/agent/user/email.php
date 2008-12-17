<?php

class extends agent
{
	public $get = '__1__:c:[A-Za-z0-9]{8}';

	function control()
	{
		$this->get->__1__ || p::forbidden();

		tribes_email::confirm($this->get->__1__);
	}
}
