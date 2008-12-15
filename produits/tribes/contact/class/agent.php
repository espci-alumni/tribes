<?php

class extends self
{
	protected $connected_id = true;

	function control()
	{
		$this->connected_id = $this->connected_id ? tribes::getConnectedId() : 0;
	}
}
