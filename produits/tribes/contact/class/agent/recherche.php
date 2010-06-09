<?php

class extends agent
{
	protected static $definition = array(
		'Informations personnelles' => array(
			'prenom' => array(
				'table' => 'c',
				'name' => 'Prénom',
				'select' => 'prenom_civil',
				'where' => 'prenom_civil, prenom_usuel',
				'show' => 1,
			),
			'nom' => array(
				'table' => 'c',
				'name' => 'Nom',
				'select' => "IF(nom_usuel!=nom_civil,CONCAT(nom_usuel,' (',nom_civil,')'),nom_usuel)",
				'where' => 'nom_civil, nom_usuel, nom_etudiant',
				'show' => 1,
			),
			'nom_usuel' => array(
				'table' => 'c',
				'name' => 'Nom usuel',
				'select' => '',
				'where' => '',
				'show' => 0,
			),
			'sexe' => array(
				'table' => 'c',
				'name' => 'Sexe',
				'select' => '',
				'where' => '',
				'show' => 0,
			),
			'date_naissance' => array(
				'table' => 'c',
				'name' => 'Date de naissance',
				'select' => '',
				'where' => '',
				'show' => 0,
			),
			'date_deces' => array(
				'table' => 'c',
				'name' => 'Date de décés',
				'select' => '',
				'where' => '',
				'show' => 0,
			),
			'conjoint' => array(
				'table' => 'c',
				'name' => 'Conjoint (O/N)',
				'select' => "IF(conjoint_email,'O','N')",
				'where' => '',
				'show' => 0,
			),
			'statut_inscription' => array(
				'table' => 'c',
				'name' => 'Statut d\'inscription',
				'select' => '',
				'where' => '',
				'show' => 0,
			),
		),
	);

	function compose($o)
	{
		$f = new pForm($o);
		$f->add('text', 'where');
		$send = $f->add('submit', 'send');
		$send->attach('where', '', '');

		if ($send->isOn())
		{
			$data = $send->getData();
			$this->composeQuery($o, $data['where']);
		}

		return $o;
	}

	function composeQuery($o, $user_query)
	{
		$def = array();
		$select = array();
		$where  = array();

		foreach (self::$definition as $k => &$v)
		{
			foreach ($v as $k => &$v)
			{
				$def[$k] =& $v;

				if ($v['show'])
				{
					$select[$k] = '' !== $v['select'] ? $v['select'] . ' AS ' . $k : $k;
				}
			}
		}

		$rx = 'like|and|not|xor|or|regexp|' . implode('|', array_keys($def));
		$rx = '"[^"\\\\]*(?:\\\\.[^"\\\\]*)*"|\'[^\'\\\\]*(?:\\\\.[^\'\\\\]*)*\''
			. '|[-()=><!\s%&|]+|\b(?:' . $rx . ')\b';

		preg_match_all("/{$rx}/i", $user_query, $champs);

		$champs =& $champs[0];

		if (implode('', $champs) === $user_query)
		{
			foreach ($champs as $k => $token)
			{
				if (isset($def[$token]))
				{
					$v =& $def[$token];

					if ('' !== $v['select'])
					{
						$champs[$k] = $v['select'];
						$select[$token] = $v['select'] . ' AS ' . $token;
					}
					else
					{
						$select[$token] = $token;
					}
				}
			}

			$o->headers = new loop_array(array_keys($select));

			$select = implode(',', $select);
			$where  = implode('', $champs);

			$sql = "SELECT contact_id,{$select} FROM contact_contact WHERE {$where}";

			$o->resultats = new loop_sql($sql, array($this, 'filterResultat'), 0, 15);
		}
		else
		{
			E('!!! Erreur de syntaxe');
		}
	}

	function filterResultat($o)
	{
		$contact_id = $o->contact_id;

		unset($o->contact_id);

		$o = (object) array(
			'contact_id' => $contact_id,
			'fields' => new loop_array((array) $o),
		);

		return $o;
	}
}
