<?php

class extends pTask_periodic
{
	function execute()
	{
		$sql = "SELECT 1
				FROM contact_contact
				WHERE statut_inscription='demande'
				LIMIT 1";

		if (DB()->queryOne($sql))
		{
			tribes::startFakeSession();
			notification::send('registration/requests');
		}
	}
}
