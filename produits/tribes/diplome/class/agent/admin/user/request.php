<?php

class extends self
{
	protected function composeDiplome($o, $f, $send)
	{
		$sql = "SELECT ecole   AS c_ecole,
					promotion  AS c_promotion,
					programme  AS c_programme,
					specialite AS c_specialite
				FROM contact_contact
				WHERE contact_id={$this->contact_id}";

		foreach (DB()->queryRow($sql) as $k => $v)
			isset($o->$k) || $o->$k = $v;

		return agent_user_edit::composeDiplome($o, $f, $send);
	}
}
