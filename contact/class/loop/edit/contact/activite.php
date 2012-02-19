<?php

class loop_edit_contact_activite extends loop_edit
{
    public $adminMode = false;

    protected

    $type = 'activite',
    $exposeLoopData = true,
    $send;


    function __construct($f, $contact_id, $send, $freeze = false)
    {
        $loop = new loop_contact_activite($contact_id);

        $freeze && $this->allowAddDel = false;
        $this->defaultLength = SESSION::get('contact_id') == $contact_id ? 1 : 0;

        parent::__construct($f, $loop);

        $this->send = $send;
    }

    function populateForm($a, $data, $counter)
    {
        if (isset($data->is_shared) && $data->is_shared < 0) unset($data->is_shared);
        empty($data->pays) || $data->ville .= ', ' . $data->pays;

        $f = $this->form;
        $f->setDefaults($data);

        $s = $organisation = $f->add('QSelect', 'organisation', array(
            'isdata' => false,
            'src' => 'QSelect/organisation',
        ));
        $f->add('city', 'ville', array('isdata' => false));
        $f->add('text', 'service');
        $f->add('QSelect', 'titre', array(
            'src' => 'QSelect/activite/titre',
        ));

        $sql = "SELECT
                    EXISTS(SELECT * FROM item_lists WHERE type='activite/statut') AS has_statut,
                    EXISTS(SELECT * FROM item_lists WHERE type='activite/fonction') AS has_fonction,
                    EXISTS(SELECT * FROM item_lists WHERE type='activite/secteur') AS has_secteur";
        $a = DB()->fetchAssoc($sql);

        $sql = "SELECT `value` AS K, `group` AS G, `value` AS V
                FROM item_lists
                WHERE type='activite/%s'
                ORDER BY sort_key, `group`, `value`";

        if ($a['has_statut'])
        {
            $f->add('select', 'statut', array(
                'firstItem' => '- Choisir dans la liste -',
                'sql' => sprintf($sql, 'statut'),
            ));
            $s->attach('statut', '', '');
        }

        if ($a['has_fonction'])
        {
            $f->add('select', 'fonction', array(
                'firstItem' => '- Choisir dans la liste -',
                'sql' => sprintf($sql, 'fonction'),
            ));
            $s->attach('fonction', '', '');
        }

        if ($a['has_secteur'])
        {
            $f->add('select', 'secteur', array(
                'firstItem' => '- Choisir dans la liste -',
                'sql' => sprintf($sql, 'secteur'),
            ));
            $s->attach('secteur', '', '');
        }

        $f->add('monthyear', 'date_debut');
        $f->add('monthyear', 'date_fin');

        $f->add('text', 'site_web');
        $f->add('QSelect', 'keyword', array(
            'src' => 'QSelect/suggestions/keyword',
        ));
        $f->add('check', 'is_shared', array('item' => array(1 => 'Partagé', 0 => 'Confidentiel')));

        $this->send->attach(
            'organisation', $this->adminMode ? '' : "Veuillez renseigner au moins une organisation", '',
            'ville', $this->adminMode ? '' : "Veuillez renseigner une ville", '',
            'is_shared', $this->adminMode ? '' : "Veuillez choisir le niveau de partage de cette activité", ''
        );

        $s->attach(
            'service', '', '',
            'titre', '', '',
            'date_debut', '', '',
            'date_fin', '', '',
            'site_web', '', '',
            'keyword', '', '',
            'is_shared', '', ''
        );
    }
}
