<?php

class extends self
{
	function save($data, $message = null, &$id = 0)
	{
		$message = parent::save($data, $message, $id);

		if (empty($this->contact_id) || empty($data['email'])) return $message;

		$sql = "SELECT email, user, e.is_active, e.is_obsolete, e.contact_confirmed
				FROM contact_email e JOIN contact_contact USING (contact_id)
				WHERE contact_id={$this->contact_id} AND email='{$data['email']}' AND user!=''";
		if ($row = DB()->queryRow($sql))
		{
			$sql = substr($CONFIG['tribes.emailDomain'], 1);

			if ($row->is_obsolete > 0 || !(int) $row->contact_confirmed)
			{
				$sql = "DELETE FROM a USING postfix_alt a
							JOIN postfix_user USING (user_id)
						WHERE alt='{$row->email}'
							AND domain='{$sql}'
							AND user='{$row->user}'";
			}
			else
			{
				$sql = "SELECT user_id FROM postfix_user WHERE domain='{$sql}' AND user='{$row->user}'";
				$sql = "INSERT INTO postfix_alt (alt,user_id,forward,created)
						VALUES ('{$row->email}',({$sql}),{$row->is_active},NOW())
						ON DUPLICATE KEY UPDATE forward={$row->is_active}";
			}

			DB($CONFIG['tribes.emailDSN'])->exec($sql);
		}

		return $message;
	}

	function delete($row_id)
	{
		if (!$this->confirmed)
		{
			$sql = "SELECT email, user
					FROM contact_email JOIN contact_contact USING (contact_id)
					WHERE contact_id={$this->contact_id} AND email_id={$row_id}";
			if ($row = DB()->queryRow($sql))
			{
				$sql = substr($CONFIG['tribes.emailDomain'], 1);
				$sql = "DELETE FROM a USING postfix_alt a
							JOIN postfix_user USING (user_id)
						WHERE alt='{$row->email}'
							AND domain='{$sql}'
							AND user='{$row->user}'";
				DB($CONFIG['tribes.emailDSN'])->exec($sql);
			}
		}

		parent::delete($row_id);
	}
}
