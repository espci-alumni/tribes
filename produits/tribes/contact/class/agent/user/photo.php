<?php


class extends agent
{
	const contentType = 'image/jpeg';

	public $get = array(
		'__1__:c:[A-Za-z0-9]{8}'    => '#',
		'__2__:c:.+\.[a-z0-9]{3,4}' => '#',
	);

	protected

	$maxage = -1,
	$requiredAuth = false,

	$contact = false,
	$token;

	function control()
	{
		parent::control();

		$this->token = $this->get->__1__;
		$this->contact = '~' === $this->get->__2__[0];
	}

	function compose($o)
	{
		$file = patchworkPath('data/photo/') . $this->token . '.jpg';

		$this->sendfile($file);

		return $o;
	}

	protected function sendfile($file)
	{
		if ($this->contact)
		{
			$this->maxage = 0;

			if (file_exists($file . '~'))
			{
				p::readfile($file . '~', $this->contentType);
				return;
			}
		}

		if (file_exists($file)) p::readfile($file, $this->contentType);
		else p::redirect('img/photo.gif');
	}
}
