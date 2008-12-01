<?php

class extends notification
{
	function __construct($message, $context)
	{
		isset($context['email.To']) || $context['email.To'] = tribes::getAdminEmails();

		parent::__construct($message, $context);
	}
}
