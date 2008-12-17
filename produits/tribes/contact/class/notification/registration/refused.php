<?php

class extends notification
{
	protected function doSend()
	{
		parent::doSend();

		$this->mail($this->context['email']);
	}
}
