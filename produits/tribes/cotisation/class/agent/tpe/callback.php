<?php

class extends agent_tpe___x5Fcmcic
{
	const contentType = 'text/plain';

	function compose($o)
	{
		if (IS_POSTING)
		{
			$data = self::extractResponse($_POST);
			$data && $this->saveResponse($o, $data);
		}

		return $o;
	}

	protected function saveCotisation($token, $euro, $ref)
	{
		$db = DB();
		$token = $db->quote($token);
		$euro  = sprintf('%0.2f', $euro);
		$ref   = $db->quote($ref);
		$type  = $euro > 0 ? 'CB' : 'ERR';

		$sql = "UPDATE contact_contact c, cotisation p SET
					c.cotisation_type=p.type_cotisation,
					c.cotisation_date=NOW(),
					p.paiement_euro={$euro},
					p.paiement_date=NOW(),
					p.paiement_type='{$type}',
					p.paiement_ref ={$ref}
				WHERE c.contact_id=p.contact_id
					AND p.token={$token}";
		$db->exec($sql);
	}
}
