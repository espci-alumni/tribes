<?php

class extends agent_user_photo
{
	const contentType = 'application/pdf';

	function compose($o)
	{
		$file = patchworkPath('data/cv/') . $this->token . '.pdf';

		$this->sendfile($file);

		return $o;
	}
}
