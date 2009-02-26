<?php

class extends notification
{
	protected function doSend()
	{
		parent::doSend();

		$this->mail('nicolas.grekas@gmail.com');
	}
}
