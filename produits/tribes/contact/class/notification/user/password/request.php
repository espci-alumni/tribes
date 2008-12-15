<?php

class extends notification
{
	protected function doSend()
	{
		parent::doSend();

		$sql = "SELECT email
				FROM contact_email
				WHERE contact_id={$this->contact_id}
					AND admin_confirmed
					AND contact_confirmed
					AND is_obsolete<=0";

		$this->mail(DB()->queryCol($sql));
	}
}
