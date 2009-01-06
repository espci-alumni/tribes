<?php

class extends self
{
	protected $maxage = 0;

	function compose($o)
	{
		if (tribes::getConnectedId())
		{
			$o->form_logout = new pForm(false);
			$o->f_logout = $o->form_logout->add('submit', 'logout');

			if ($o->f_logout->isOn())
			{
				s::destroy();
				p::redirect('index');
			}
		}

		switch ($o->message = s::flash('headerMessage'))
		{
		case 'create': $o->message = 'Ajout effectué'; break;
		case 'save'  : $o->message = 'Modifications enregistrées'; break;
		}

		return parent::compose($o);
	}
}
