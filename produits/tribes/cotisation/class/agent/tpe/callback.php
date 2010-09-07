<?php

class extends agent_tpe_response
{
	function compose($o)
	{
		if (IS_POSTING)
		{
			if ($o = self::composeResponse($o, $_POST))
			{
				list($o, $token, $euro, $is_ok, $ref) = $o;
				$token = explode('/', $token, 2);

				$this->saveResponse($token[0], $token[1], $euro, $is_ok, $ref) || $o = array();
			}
		}

		return $o;
	}

	protected function saveResponse($type, $token, $euro, $is_ok, $ref)
	{
		if ('C' === $type) return $this->saveCotisation($token, $euro, $is_ok, $ref);
	}

	protected function saveCotisation($token, $euro, $is_ok, $ref)
	{
		$db = DB();

		$token = $db->quote($token);
		$ref   = $db->quote($ref);

		if ($is_ok)
		{
			$euro = sprintf('%0.2f', $euro);

			$sql = 0 < $is_ok ? 'CB' : 'TST';
			$sql = "UPDATE contact_contact c, cotisation p SET
						c.cotisation_expires=GREATEST(NOW(), c.cotisation_expires) + INTERVAL p.nb_mois MONTH,
						p.paiement_euro={$euro},
						p.paiement_date=NOW(),
						p.paiement_mode='{$sql}',
						p.paiement_ref ={$ref}
					WHERE c.contact_id=p.contact_id
						AND p.token={$token}";
			if ($db->exec($sql))
			{
				$sql = "SELECT * FROM cotisation WHERE token={$token}";
				notification::send('user/cotisation', (array) $db->queryRow($sql));

				return true;
			}
		}
		else
		{
			$sql = "UPDATE cotisation p SET
						p.paiement_mode='ERR',
						p.paiement_ref ={$ref}
					WHERE p.token={$token}";
			if ($db->exec($sql))
			{
				return true;
			}
		}

		return false;
	}
}
