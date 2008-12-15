<?php

class extends notification
{
	protected function doSend()
	{
		parent::doSend();

		$c =& $this->context;

		if (!empty($c['token']))
		{
			$sql = "SELECT email FROM contact_email
					WHERE token='{$c['token']}'
						AND NOT admin_confirmed";

			if (('insert' === $c['action'] && !$c['admin_confirmed']) || $c['email'] = DB()->queryOne($sql))
			{
				$this->mail($c['email']);
			}
		}
	}
}
