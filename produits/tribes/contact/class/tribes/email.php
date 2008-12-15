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

	function save($data, $message = null, $id = 0)
	{
		if (!$this->confirmed)
		{
			isset($data['token']) || $data['token'] = p::strongid(8);
		}

		$sql = "UPDATE contact_email
				SET is_obsolete=-1
				WHERE is_obsolete=1 AND " . (
					!$id
					? "contact_id={$this->contact_id} AND email=" . DB()->quote($data['email'])
					: "email_id={$id}"
				);
		DB()->exec($sql);

		return parent::save($data, $message, $id);
	}


	static function confirm($token, $resetToken = true)
	{
		$sql = "SELECT email_id, contact_id, contact_data, email
				FROM contact_email
				WHERE token='{$token}'
					AND token_expires>=NOW()";
		if ($data = DB()->queryRow($sql))
		{
			$sql = new self($data->contact_id, true);

			$data = array_merge(
				$data->contact_data
					? unserialize($data->contact_data)
					: array(),
				array(
					'email_id'    => $data->email_id,
					'is_obsolete' => 0,
				)
			);

			$resetToken && $data['token'] = '';

			$sql->save($data, 'user/email/confirmation');

			return true;
		}
		else return false;
	}
}
