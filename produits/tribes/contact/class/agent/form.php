<?php

class extends agent
{
	protected $data = array();

	function compose($o)
	{
		$f = new pForm($o);
		$f->setDefaults($this->data);

		$save = $f->add('submit', 'save');

		$o = $this->composeForm($o, $f, $save);

		if ($save->isOn() && $this->formIsOk($f))
		{
			$a = $this->data ? 'save' : 'create';

			list($save, $b) = (array) $this->save($save->getData()) + array(null, null);

			if (null === $save) W(get_class($this) . '->save() result must be non-null');
			else if (false !== $save)
			{
				$b && s::flash('headerMessage', true !== $b ? $b : $a);
				p::redirect($save);
			}
		}

		return $o;
	}

	protected function composeForm($o, &$f, &$save)
	{
		return $o;
	}

	protected function formIsOk($f)
	{
		return true;
	}

	protected function save($data)
	{
		return false;
	}
}
