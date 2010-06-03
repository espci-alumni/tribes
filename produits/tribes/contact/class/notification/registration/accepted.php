<?php

class extends notification
{
	protected function doSend()
	{
		if (!isset($this->context['login']))
		{
			$sql = "SELECT login
					FROM contact_contact
					WHERE contact_id={$this->contact_id}";
			$this->context['login'] = DB()->queryOne($sql);
		}

		parent::doSend();

		$sql = "SELECT email
				FROM contact_email
				WHERE contact_id={$this->contact_id}
					AND is_active
					AND contact_confirmed";
		$this->mail(DB()->queryCol($sql));
	}
}
