<?php

class extends self
{
	protected function composeDiplome($o, $f, $send)
	{
		$f->add('text', 'ecole');
		$f->add('text', 'promotion');
		$f->add('text', 'programme');
		$f->add('text', 'specialite');

		$send->attach(
			'ecole',      '', '',
			'promotion',  '', '',
			'programme',  '', '',
			'specialite', '', ''
		);

		return $o;
	}
}
