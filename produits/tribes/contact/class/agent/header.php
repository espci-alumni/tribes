<?php

class extends self
{
	protected $maxage = 0;

	function compose($o)
	{
		switch ($o->message = s::flash('headerMessage'))
		{
		case 'create': $o->message = 'Ajout effectué'; break;
		case 'save'  : $o->message = 'Modifications enregistrées'; break;
		}

		return parent::compose($o);
	}
}
