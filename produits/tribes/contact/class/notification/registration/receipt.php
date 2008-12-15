<?php

class extends notification
{
	protected function doSend()
	{
		parent::doSend();

		$sql = "SELECT email
				FROM contact_email
				WHERE token='{$this->context['token']}'";

		$this->mail(DB()->queryOne($sql));
	}
}
