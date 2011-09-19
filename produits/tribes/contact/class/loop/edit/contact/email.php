<?php

class extends loop_edit
{
    public $adminMode = false;

    protected

    $type = 'email',
    $exposeLoopData = true,
    $send,
    $contact_id;


    function __construct($f, $contact_id, $send)
    {
        $this->contact_id = $contact_id;

        $loop = new loop_contact_email($contact_id);

        $this->defaultLength = s::get('contact_id') == $contact_id ? 1 : 0;

        parent::__construct($f, $loop);

        $this->send = $send;
    }

    function populateForm($a, $data, $counter)
    {
        $f = $this->form;
        $f->setDefaults($data);
        $f->add('email', 'email', array('readonly' => $data->id, 'isdata' => false));
        $f->add('check', 'is_active', array('item' => array(1 => 'Adresse de correspondance'), 'multiple' => true));

        $this->send->attach('email', '', "Email non valide");
    }
}
