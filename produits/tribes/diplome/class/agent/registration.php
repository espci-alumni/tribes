<?php

class extends self
{
	protected function composeContact($o, $f, $send)
	{
		$o = parent::composeContact($o, $f, $send);
		$o = $this->composeDiplome($o, $f, $send);

		return $o;
	}

	protected function composeDiplome($o, $f, $send)
	{
		$f->add('text', 'promotion');

		$send->attach('promotion', 'Veuillez renseignez votre promotion', '');

		return $o;
	}
}
