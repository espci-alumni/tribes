<?php

class extends tribes_common
{
	protected

	$table = 'email',
	$dataFields = array(
		'email',
	);

	function __construct($contact_id, $confirmed = 0)
	{
		$this->metaFields += array(
			'token'         => 'stringNull',
			'token_expires' => 'sql',
		);

		parent::__construct($contact_id, $confirmed);
	}

	function save($data, $message = null, &$id = 0)
	{
		if (!$id && !empty($data['email_id'])) $id = $data['email_id'];

		if (!$id && !isset($data['email']))
		{
			W(__METHOD__ . '() input error: please provide email or email_id.');
			return;
		}
		else if (isset($data['email'])) $data['email'] = strtolower($data['email']);

		$sql = "UPDATE contact_email
				SET is_obsolete=-1, admin_confirmed=0
				WHERE is_obsolete=1
					AND contact_id={$this->contact_id}
					AND " . (
						!$id
						? "email=" . DB()->quote($data['email'])
						: "email_id={$id}"
					);
		DB()->exec($sql);

		if (!$this->confirmed && (!isset($data['token']) || !isset($data['email'])))
		{
			$sql = "SELECT email, admin_confirmed
					FROM contact_email
					WHERE contact_id={$this->contact_id}
						AND " . (
							!$id
							? "email=" . DB()->quote($data['email'])
							: "email_id={$id}"
						);
			if ($sql = DB()->queryRow($sql))
			{
				$data['email'] = $sql->email;

				if (!isset($data['token']) && !(int) $sql->admin_confirmed)
				{
					 $data['token'] = p::strongid(8);
				}
			}
			else if ($id) return;
			else isset($data['token']) || $data['token'] = p::strongid(8);
		}

		return parent::save($data, $message, $id);
	}

	function delete($row_id)
	{
		parent::delete($row_id);

		if (!$this->confirmed)
		{
			$sql = "UPDATE contact_email
					SET token=NULL
					WHERE contact_id={$this->contact_id}
						AND is_obsolete=1";
			DB()->exec($sql);
		}
	}


	static function confirm($token, $resetToken = true)
	{
		$sql = "SELECT email_id, contact_id, contact_data, email, contact_confirmed
				FROM contact_email
				WHERE token='{$token}'
					AND token_expires>=NOW()";
		$row = DB()->queryRow($sql);
		if (!$row) return false;

		$email = new self($row->contact_id, true);

		$data = $row->contact_data ? unserialize($row->contact_data) : array();

		$data['is_obsolete'] = 0;

		$resetToken && $data['token'] = '';

		if ($row->contact_id == tribes::getConnectedId())
		{
			$data['contact_confirmed'] = true;
			$row->contact_confirmed = true;
		}

		$email->save($data, 'user/email/confirmation', $row->email_id);

		if ($resetToken && !(int) $row->contact_confirmed)
		{
			s::flash('confirmed_email_id', $row->email_id);
			p::redirect('login/confirmEmail');
		}

		return true;
	}
}
