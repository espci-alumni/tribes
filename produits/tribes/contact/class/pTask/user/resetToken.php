<?php

class extends pTask_periodic
{
	function execute()
	{
		$sql = "UPDATE contact_contact
				SET token=NULL
				WHERE token_expires<NOW()
					AND statut_inscription!='demande'";
		DB()->exec($sql);

		$sql = "UPDATE contact_email
				SET token=NULL
				WHERE token_expires<NOW()";
		DB()->exec($sql);
	}
}