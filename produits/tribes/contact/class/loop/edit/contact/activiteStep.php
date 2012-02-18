<?php

class loop_edit_contact_activiteStep extends loop_edit_contact_activite
{
    function __construct($f, $send, $contact_id)
    {
        $this->allowAddDel = false;
        $this->send = $send;

        $sql = "SELECT a.*, a.activite_id AS id
                FROM contact_activite a
                    JOIN contact_contact c ON a.activite_id=c.principale_activite_id
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
                'activite_id' => 0,
            );
        }

        $default['statut'] = $f->getElement('statut_activite')->getValue();
        $default['hide_statut'] = 1;

        loop_edit::__construct($f, new loop_array(array($default), 'filter_rawArray'));
    }

    function populateForm($a, $data, $counter)
    {
        parent::populateForm($a, $data, $counter);
        $this->form->add('hidden', 'principale', array('default' => 1, 'readonly' => 1));
        $this->form->getElement('organisation')->attach('principale', '', '');
    }
}
