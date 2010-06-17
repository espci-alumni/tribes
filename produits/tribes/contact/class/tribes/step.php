<?php

class
{
	protected 

	$steps = array(),
	$step = false;


	function __construct($step = false)
	{
		$this->step = $step;
	}

	function getNextStep()
	{
		$step  = $this->step;
		$steps = array_keys($this->steps);

		if ($steps && !$step) return $steps[0];

		if(!isset($this->steps[$step])) return false;

		$step = array_search($step, $steps);

		return isset($steps[++$step]) ? $steps[$step] : false;
	}

	function getTitle()
	{
		return $this->step ? $this->steps[$this->step] : false;
	}

	function getPosition()
	{
		return array_search($this->step, array_keys($this->steps));
	}

	function getLoop()
	{
		return new loop_array($this->steps);
	}
	
	function __toString()
	{
		return $this->step;
	}
}
