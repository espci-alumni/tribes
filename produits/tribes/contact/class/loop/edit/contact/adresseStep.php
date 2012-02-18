<?php

class loop_edit_contact_adresseStep extends loop_edit_contact_adresse
{
    function __construct($f, $send, $contact_id)
    {
        $this->allowAddDel = false;
        $this->send = $send;

        $sql = "SELECT a.*, a.adresse_id AS id
                FROM contact_adresse a
                    JOIN contact_contact c ON a.adresse_id=c.corresp_adresse_id
                WHERE c.contact_id={$contact_id}";

        if ($default = DB()->fetchAssoc($sql))
        {
            if (!empty($default['contact_data']) && $v = unserialize($default['contact_data']))
                foreach ($v as $k => $v)
                    $default[$k] = $v;
        }
        else
        {
            $default = array(
                'adresse_id' => 0,
                'description' => 'Adresse personnelle',
            );
        }

        loop_edit::__construct($f, new loop_array(array($default), 'filter_rawArray'));
    }

    function populateForm($a, $data, $counter)
    {
        parent::populateForm($a, $data, $counter);
        $this->form->add('hidden', 'correspondance', array('default' => 1, 'readonly' => 1));
        $this->form->getElement('description')->attach('correspondance', '', '');
        $a->hide_email_list = 1;
    }
}
