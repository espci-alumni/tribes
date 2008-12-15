<?php

class extends notification
{
	protected function doSend()
	{
		parent::doSend();

		$this->mail('iekhad@hotmail.fr');
	}
}
