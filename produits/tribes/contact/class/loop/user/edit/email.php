<?php

class extends loop_user_edit
{
	function filterRow($o)
	{
		$o = parent::filterRow($o);
		unset($o->token);

		return $o;
	}
}
