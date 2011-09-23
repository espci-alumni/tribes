<?php

class agent_admin_user_secretariat___x5Fadmin extends agent_admin_user_secretariat
{
    protected $contact;

    protected function composeForm($o, $f, $send)
    {
        $this->contact = new tribes_contact($this->contact_id, true);
        $f->setDefaults($this->contact->fetchRow('date_deces, is_active, acces'));

        $f->add('select', 'acces', array('item' => array(
            '' => 'Non-inscrit',
            'membre' => 'Utilisateur',
            'admin' => 'Administrateur',
        )));
        $f->add('select', 'is_active', array('item' => array(
            1 => "Présent dans l'annuaire et destinataire des communications",
            0 => "Hors de la communauté",
        )));
        $f->add('date', 'date_deces');

        $send->attach(
            'acces', '', '',
            'is_active', "Quelle participation dans la communauté ?", '',
            'date_deces', '', ''
        );

        return $o;
    }

    protected function save($data)
    {
        $this->contact->save($data);
        return '';
    }
}
