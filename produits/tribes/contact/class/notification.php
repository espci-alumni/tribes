<?php

class
{
	protected $message, $contact_id, $context;

	static function send($message, $context)
	{
		$m = "notification/{$message}";

		is_array($context) || $context = (array) $context;

		$m = patchworkPath("class/{$m}.php") ? patchwork_file2class($m) : __CLASS__;

		$message = new $m($message, $context);
		$message->doSend();
	}

	protected function __construct($message, $context)
	{
		if (empty($context['contact_id']))
		{
			W("No contact_id specified for notification: {$message}");
			return;
		}

		isset($context['password']) && $context['password'] = (bool) $context['password'];

		$this->message = $message;
		$this->contact_id = $context['contact_id'];
		$this->context =& $context;
	}

	protected function doSend()
	{
		$this->store();
	}

	protected function store()
	{
		$h = $this->context;

		unset($h['contact_id'], $h['token']);

		$h = array(
			'historique'         => $this->message,
			'contact_id'         => $this->contact_id,
			'origine_contact_id' => tribes::getConnectedId(false),
			'details'            => serialize($h),
		);

		$h['origine_contact_id'] || $h['origine_contact_id'] = $h['contact_id'];

		$db = DB();

		$sql = 'INSERT INTO contact_historique (date_contact,' . implode(',', array_keys($h)) . ')
				VALUES (NOW()';
		foreach ($h as $k => $h) $sql .= ',' . $db->quote($h);
		$sql .= ')';
		$db->exec($sql);
	}

	protected function mail($email, $data = array())
	{
		$data += $this->context;

		pMail::sendAgent(
			array('To' => $email),
			"email/{$this->message}",
			$data
		);
	}
}
