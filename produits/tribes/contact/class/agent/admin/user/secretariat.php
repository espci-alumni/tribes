<?php

class extends agent_user_secretariat
{
	public $get = array('__1__:i:1');

	function compose($o)
	{
		$this->contact_id = $this->get->__1__;

		$o = parent::compose($o);
		$o->contact_id = $this->contact_id;

		return $o;
	}

	function composeBlocnote($o, $f, $send)
	{
		$f->add('textarea', 'note');

		$send->attach('note', '', '');

		return parent::composeBlocnote($o, $f, $send);
	}

	function save($data)
	{
		$data['contact_id'] = $this->contact_id;

		!empty($data['note']) && notification::send('user/blocnote', $data);

		return '';
	}

	function filterRow($o)
	{
		$o = parent::filterRow($o);

		$o->f_del = new pForm_check($this->form, 'f_del', array(
			'item' => array($o->historique_id => 'suppression'),
			'multiple' => true
		));
		
		return $o;
	}
}
