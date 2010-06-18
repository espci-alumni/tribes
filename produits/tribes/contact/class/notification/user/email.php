<?php

class extends notification
{
	protected function doSend()
	{
		parent::doSend();

		$c =& $this->context;

		if (!empty($c['token'], $c['email']))
		{
			$this->mail($c['email']);
		}
	}
}
