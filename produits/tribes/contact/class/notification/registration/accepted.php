<?php

class extends notification_registration_receipt
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

		return parent::doSend();
	}
}
