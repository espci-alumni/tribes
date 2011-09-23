<?php

class agent_admin_user_secretariat___x5Fnote extends agent_admin_user_secretariat
{
    protected function composeForm($o, $f, $send)
    {
        $f->add('textarea', 'note');

        $send->attach('note', '', '');

        $o = parent::composeForm($o, $f, $send);
        $o->notes->addFilter(array($this, 'filterRow'));

        return $o;
    }

    protected function save($data)
    {
        if (is_array($ids =& $_POST['f_del_historique']))
        {
            $ids = array_map('intval', $ids);
            $ids = implode(',', $ids);

            $sql = "DELETE FROM contact_historique WHERE historique_id IN ({$ids})";

            DB()->exec($sql);
        }

        $data['contact_id'] = $this->contact_id;

        !empty($data['note']) && notification::send('user/blocnote', $data);

        return '';
    }

    function filterRow($o)
    {
        $o->f_del = new pForm_check($this->form, 'f_del_historique', array(
            'item' => array($o->historique_id => 'Supprimer'),
            'multiple' => true
        ));

        return $o;
    }
}
