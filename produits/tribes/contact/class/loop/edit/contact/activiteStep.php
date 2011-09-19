<?php

class extends loop_edit_contact_activite
{
    function __construct($f, $send)
    {
        $this->allowAddDel = false;
        $this->send = $send;

        $default = array(
            'activite_id' => 0,
            'statut' => $f->getElement('statut_activite')->getValue(),
            'hide_statut' => 1,
        );

        loop_edit::__construct($f, new loop_array(array($default), 'filter_rawArray'));
    }

    function populateForm($a, $data, $counter)
    {
        parent::populateForm($a, $data, $counter);
        $this->form->add('city', 'ville', array('isdata' => false));
        $this->send->attach('ville', $this->adminMode ? '' : "Veuillez choisir ou ajouter une ville", '');
    }
}
