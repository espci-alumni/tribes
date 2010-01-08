<?php

class extends self
{
	function compose($o)
	{
		$o = parent::compose($o);

		$sql = "SELECT ecole   AS c_ecole,
					promotion  AS c_promotion,
					programme  AS c_programme,
					specialite AS c_specialite
				FROM contact_contact
				WHERE contact_id={$this->contact_id}";

		$sql = (array) DB()->queryRow($sql);

		$o = (object) ((array) $o + $sql);

		return $o;
	}
}
