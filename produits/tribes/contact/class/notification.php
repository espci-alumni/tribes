<?php

class
{
	static function send($message, $context)
	{
		$m = "notification/{$message}";

		is_array($context) || $context = (array) $context;

		if (patchworkPath("class/{$m}.php"))
		{
			$m = patchwork_file2class($m);
			new $m($message, $context);
		}
		else
		{
			new self($message, $context);
		}
	}

	protected function __construct($message, $context)
	{
		if (empty($context['contact_id']))
		{
			W("No contact_id specified for notification: {$message}");
			return;
		}

		if (!empty($context['email.To']))
		{
			pMail::sendAgent(
				array('To' => $context['email.To']),
				"email/{$message}",
				$context
			);
		}

		$h = $context['contact_id'];

		unset($context['contact_id']);

		$h = array(
			'historique'         => $message,
			'contact_id'         => $h,
			'origine_contact_id' => tribes::getConnectedId(false),
			'details'            => serialize($context),
		);

		$h['origine_contact_id'] || $h['origine_contact_id'] = $h['contact_id'];

		$db = DB();

		$sql = 'INSERT INTO contact_historique (date_contact,' . implode(',', array_keys($h)) . ')
				VALUES (NOW()';
		foreach ($h as $k => $h) $sql .= ',' . $db->quote($h);
		$sql .= ')';
		$db->exec($sql);
	}
}
