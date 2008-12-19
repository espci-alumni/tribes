<?php


class extends agent
{
	const contentType = 'image/jpeg';

	public $get = array('__1__:c:[A-Za-z0-9]{8}', 'contact:b');

	protected

	$maxage = -1,
	$requiredAuth = false;

	function compose($o)
	{
		$file = patchworkPath('data/photo/') . $this->get->__1__;

		if ($this->get->contact && file_exists($file . '.contact.jpg'))
		{
			p::readfile($file . '.contact.jpg', $this->contentType);
			return $o;
		}

		$file .= '.jpg';

		if (file_exists($file)) p::readfile($file, $this->contentType);
		else p::redirect('img/photo.gif');

		return $o;
	}
}
