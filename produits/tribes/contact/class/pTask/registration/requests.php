<?php

class extends pTask_periodic
{
	function execute()
	{
		$sql = "SELECT 1
				FROM contact_contact
				WHERE password!=''
					AND acces=''
				LIMIT 1";

		if (DB()->queryOne($sql))
		{
			tribes::startFakeSession();
			notification::send('registration/requests');
		}
	}
}
