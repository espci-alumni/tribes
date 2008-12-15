<?php

class extends loop_sql
{
	protected

	$form, $table;

	function __construct($contact_id, $form)
	{
		$this->form = $form;
		$this->table = substr(get_class($this), strlen(__CLASS__)+1);

		$sql = "SELECT {$this->table}_id AS id, is_active, is_obsolete, admin_confirmed, contact_confirmed, contact_data
				FROM contact_{$this->table}
				WHERE contact_id={$contact_id} AND is_obsolete<=0 AND contact_data!=''
				ORDER BY sort_key";

		parent::__construct($sql, array($this, 'filterRow'));
	}

	function filterRow($o)
	{
		$o = (object) ((array) $o + (array) unserialize($o->contact_data));

		$o->f_row_id = new pForm_check($this->form, $this->table . '_id', array(
			'item' => array($o->id => ''),
			'multiple' => true
		));

		unset($o->contact_data);

		(int) $o->admin_confirmed   || $o->admin_confirmed = 0;
		(int) $o->contact_confirmed || $o->contact_confirmed = 0;

		return $o;
	}
}
