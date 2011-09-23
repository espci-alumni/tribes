<?php

class loop_edit_contact_adresseDiff extends loop_edit_contact_adresse
{
    protected

    $allowAddDel = false,
    $send;


    function __construct($f, $contact_id, $send)
    {
        $sql = "SELECT adresse_id,
                    description AS c_description,
                    adresse AS c_adresse,
                    ville_avant AS c_ville_avant,
                    ville AS c_ville,
                    ville_apres AS c_ville_apres,
                    pays AS c_pays,
                    tel_portable AS c_tel_portable,
                    tel_fixe AS c_tel_fixe,
                    tel_fax AS c_tel_fax,
                    is_shared,
                    IF (admin_confirmed,admin_confirmed,'') AS admin_confirmed,
                    contact_data
                FROM contact_adresse
                WHERE contact_id={$contact_id}
                    AND admin_confirmed<=contact_modified
                    AND is_obsolete<=0
                    AND contact_data!=''
                ORDER BY sort_key";

        $loop = new loop_sql($sql, array($this, 'filterAdresse'));

        loop_edit::__construct($f, $loop);

        $this->send = $send;
    }

    function populateForm($a, $data, $counter)
    {
        parent::populateForm($a, $data, $counter);

        $this->form->add('check', 'decision', array(
            'isdata' => false,
            'item' => array(
                '1' => 'Publier',
                '0' => 'Refuser'
            )
        ));

        $this->form->getElement('is_shared')->setValue($data->is_shared);

        $this->send->attach('decision', "Merci de publier ou refuser chacune des sections", '');
    }

    function filterAdresse($o)
    {
        if (!empty($o->contact_data) && $v = unserialize($o->contact_data))
            foreach ($v as $k => $v)
                isset($o->$k) || $o->$k = $v;

        !(int) $o->admin_confirmed && $o->new_adresse = 1;

        return $o;
    }
}
