<?php

class extends pTask_periodic
{
	function execute()
	{
		$sql = "SELECT 1
				FROM contact_contact
				WHERE statut_inscription='accepted'
					AND admin_confirmed<contact_modified
				ORDER BY contact_modified";

		if (DB()->queryOne($sql))
		{
			tribes::startFakeSession();
			notification::send('user/requests');
		}
	}
}
