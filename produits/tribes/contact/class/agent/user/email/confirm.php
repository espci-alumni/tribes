<?php

class extends agent_pForm
{
	protected $form, $send, $emails;

	protected function composeForm($o, $f, $send)
	{
		$this->form = $f;
		$this->send = $send;

		$sql = "SELECT email_id, email
				FROM contact_email
				WHERE contact_id={$this->connected_id}
					AND NOT contact_confirmed
					AND admin_confirmed
					AND is_obsolete<=0
				ORDER BY sort_key";

		$o->emails = $this->emails = new loop_sql($sql, array($this, 'filterRow'));

		return $o;
	}

	protected function save($data)
	{
		$email = new tribes_email($this->connected_id);

		while ($data = $this->emails->loop())
		{
			if ($data->f_email_confirmed->getValue())
			{
				$email->save(array('contact_confirmed' => true), null, $data->email_id);
			}
			else
			{
				$email->delete($data->email_id);
			}
		}

		$sql = s::flash('referer');

		return $sql ? $sql : agent_index::ACCUEIL_CONNECTED;
	}

	function filterRow($o)
	{
		$this->form->pushContext($o, 'email_' . $o->email_id);

		$this->form->add('check', 'email_confirmed', array(
			'item' => array(1 => 'Oui', 0 => 'Non')
		));

		$this->send->attach('email_confirmed', $o->email . ' : Cet email vous appartient-il ?', '');

		$this->form->pullContext();

		return $o;
	}
}
