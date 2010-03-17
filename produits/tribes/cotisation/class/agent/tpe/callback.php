<?php

class extends agent_tpe___x5Fadapter
{
	const contentType = 'text/plain';

	function compose($o)
	{
		if (IS_POSTING)
		{
			if ($o = self::composeResponse($o, $_POST))
			{
				list($o, $token, $euro, $mode, $ref) = $o;
				$token = explode('/', $token, 2);

				$this->saveResponse($token[0], $token[1], $euro, $mode, $ref) || $o = array();
			}
		}

		return $o;
	}

	protected function saveResponse($type, $token, $euro, $mode, $ref)
	{
		if ('C' === $type) return $this->saveCotisation($token, $euro, $mode, $ref);
	}

	protected function saveCotisation($token, $euro, $mode, $ref)
	{
		$db = DB();

		$token = $db->quote($token);
		$ref   = $db->quote($ref);

		if ('ERR' !== $mode)
		{
			$euro = sprintf('%0.2f', $euro);
			$mode = $db->quote($mode);

			$sql = "UPDATE contact_contact c, cotisation p SET
						c.cotisation_type=p.type,
						c.cotisation_date=NOW(),
						p.paiement_euro={$euro},
						p.paiement_date=NOW(),
						p.paiement_mode={$mode},
						p.paiement_ref ={$ref}
					WHERE c.contact_id=p.contact_id
						AND p.token={$token}";
			if ($db->exec($sql))
			{
				$sql = "SELECT * FROM cotisation WHERE token={$token}";
				notification::send('cotisation/confirmation', (array) $db->queryRow($sql));
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
