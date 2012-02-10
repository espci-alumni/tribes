<?php

class loop_edit_contact_adresseStep extends loop_edit_contact_adresse
{
    function __construct($f, $send)
    {
        $this->allowAddDel = false;
        $this->send = $send;

        $default = array(
            'adresse_id' => 0,
            'description' => 'Adresse de correspondance',
        );

        loop_edit::__construct($f, new loop_array(array($default), 'filter_rawArray'));
    }
}
