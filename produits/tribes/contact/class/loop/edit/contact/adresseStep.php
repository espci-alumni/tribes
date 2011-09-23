<?php

class loop_edit_contact_adresseStep extends loop_edit_contact_adresse
{
    function __construct($f, $send)
    {
        $this->allowAddDel = false;
        $this->send = $send;

        $default = array(
            'adresse_id' => 0,
            'description' => 'Adresse personnelle',
        );

        loop_edit::__construct($f, new loop_array(array($default), 'filter_rawArray'));
    }

    function populateForm($a, $data, $counter)
    {
        parent::populateForm($a, $data, $counter);

        $a->hide_is_active = true;
        $a->f_is_active->setValue(1);
    }
}
