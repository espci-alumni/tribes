<?php

class extends agent_user_blocnote
{
	public $get = array('__1__:i:1');
	protected $f;

	function composeForm($o, $f, $send)
	{
		$this->contact_id = $this->get->__1__;
		$f->add('textarea', 'note');
		
		$send->attach('note', '', '');

		$sql = "SELECT h.*, prenom_usuel AS origine_prenom, nom_usuel As origine_nom, login AS origine_login, origine_contact_id, date_contact
				FROM contact_historique h
					JOIN contact_contact c ON c.contact_id=h.origine_contact_id
				WHERE h.contact_id={$this->contact_id}
					AND historique='user/blocnote'
					ORDER BY historique_id DESC";

		$o->notes = new loop_sql($sql, array($this, 'filterRow'));

		return $o;
	}

	function filterRow($o)
	{
		$this->f = new pForm($o);
		$o = parent::filterRow($o);
		$o->f_del = new pForm_check($this->f, 'f_del',
				array(
					'item' => array($o->historique_id => 'suppression'),
					'default' => '',
					'multiple' => true
				)
			);
		
		return $o;
	}
	function save($data)
	{
		E($data);
		return '';
	}
}
