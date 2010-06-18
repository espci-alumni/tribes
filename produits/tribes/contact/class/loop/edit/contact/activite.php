<?php

class extends loop_edit
{
	public $adminMode = false;

	protected

	$type = 'activite',
	$exposeLoopData = true,
	$adresses  = array(),
	$activites = array(),
	$send,
	$editAdresse = true;


	function __construct($f, $contact_id, $send, $new = false)
	{
		$loop = new loop_contact_activite($contact_id, $new > 0);

		$new && $this->allowAddDel = false;
		$this->defaultLength = s::get('contact_id') == $contact_id ? 1 : 0;

		parent::__construct($f, $loop);

		$this->loadAdresses($contact_id);

		$this->send = $send;
	}

	function populateForm($a, $data, $counter)
	{
		if ($data->is_shared < 0) unset($data->is_shared);

		$f = $this->form;
		$f->setDefaults($data);

		$s = $organisation = $f->add('QSelect', 'organisation', array(
			'isdata' => false,
			'src' => 'QSelect/organisation',
		));
		$f->add('text', 'service');
		$f->add('QSelect', 'titre', array(
			'src' => 'QSelect/activite/titre',
		));

		$sql = "SELECT
					EXISTS(SELECT * FROM item_lists WHERE type='activite/statut'  ) AS has_statut,
					EXISTS(SELECT * FROM item_lists WHERE type='activite/fonction') AS has_fonction,
					EXISTS(SELECT * FROM item_lists WHERE type='activite/secteur' ) AS has_secteur";
		$a = DB()->queryRow($sql);

		$sql = "SELECT `value` AS K, `group` AS G, `value` AS V
				FROM item_lists
				WHERE type='activite/%s'
				ORDER BY sort_key, `group`, `value`";

		if ($a->has_statut)
		{
			$f->add('select', 'statut', array(
				'firstItem' => '- Choisir dans la liste -',
				'sql'       => sprintf($sql, 'statut'),
			));
			$s->attach('statut', '', '');
		}

		if ($a->has_fonction)
		{
			$f->add('select', 'fonction', array(
				'firstItem' => '- Choisir dans la liste -',
				'sql'       => sprintf($sql, 'fonction'),
			));
			$s->attach('fonction', '', '');
		}

		if ($a->has_secteur)
		{
			$f->add('select', 'secteur', array(
				'firstItem' => '- Choisir dans la liste -',
				'sql'       => sprintf($sql, 'secteur'),
			));
			$s->attach('secteur', '', '');
		}

		$f->add('monthyear', 'date_debut');
		$f->add('monthyear', 'date_fin');

		if ($this->editAdresse)
		{
			$a = $this->activites
				? array("Coordonnées ci-dessus" => $this->activites)
				: array();

			$this->adresses && $a["Coordonnées existantes"] =& $this->adresses;
			$a["Nouvelles coordonnées" ] = array('new' => 'Nouvelles coordonnées');

			$organisation = $organisation->getValue();

			$this->activites[-$counter] = "Idem \"{$organisation}\" ci-dessus";

			$f->add('select', 'adresse_id', array(
				'firstItem' => '- Préciser vos coordonnées -',
				'item' => $a,
				'default' => $this->adresses ? null : 'new',
			));
		}

		$f->add('text', 'site_web');
		$f->add('QSelect', 'keyword', array(
			'src' => 'QSelect/suggestions/keyword',
		));
		$f->add('check', 'is_shared', array('item' => array(1 => 'Partagé', 0 => 'Confidentiel')));

		$this->send->attach(
			'organisation', $this->adminMode ? '' : "Veuillez renseigner le ou les organisations", '',
			'is_shared',    $this->adminMode ? '' : "Veuillez choisir le niveau de partage de cette activité", ''
		);

		$s->attach(
			'service',    '', '',
			'titre',      '', '',
			'date_debut', '', '',
			'date_fin',   '', '',
			'site_web',   '', '',
			'keyword',    '', '',
			'is_shared',  '', ''
		);
	}

	protected function loadAdresses($contact_id)
	{
		$a = new loop_contact_adresse($contact_id);

		while ($b = $a->loop())
		{
			$this->adresses[$b->adresse_id] = $b->adresse . ('' !== $b->adresse ? ', ' : '') . $b->ville . '...';
		}
	}
}
