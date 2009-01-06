<?php

class extends pTask_periodic
{
	function execute()
	{
		$sql = "SELECT 1
				FROM contact_contact
				WHERE statut_inscription='accepted'
					AND admin_confirmed<contact_confirmed
				ORDER BY contact_confirmed";
		
		DB()->queryOne($sql) && notification::send('user/requests');
	}
}
