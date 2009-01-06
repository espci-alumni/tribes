<?php

class extends pTask_periodic
{
	function execute()
	{
		$sql = "SELECT 1
				FROM contact_contact
				WHERE statut_inscription='demande'
				LIMIT 1";
		
		DB()->queryOne($sql) && notification::send('registration/requests');
	}
}
