<?php

class agent_user_password extends agent_pForm
{
    public $get = '__1__:c:[-_A-Za-z0-9]{8}';

    protected $tokenPattern = 'user/password/%s';

    function control()
    {
        $this->get->__1__ || Patchwork::forbidden();

        $t = sprintf($this->tokenPattern, $this->get->__1__);

        $sql = "SELECT c.contact_id, c.login, c.nom_usuel, c.prenom_usuel
                FROM contact_contact c
                    JOIN contact_email e USING (contact_id)
                WHERE e.token='{$t}'
                    AND e.token_expires>NOW()";

        $this->data = DB()->fetchAssoc($sql) or Patchwork::redirect('error/token');
        $this->data = (object) $this->data;

        tribes_email::confirm($t, false);
    }

    protected function composeForm($o, $f, $send)
    {
        $o->login = $this->data->login;
        $o->prenom = $this->data->prenom_usuel;
        $o->nom = $this->data->nom_usuel;

        $f->add('password', 'password');
        $f->add('password', 'con_pwd', array('isdata' => false));

        $send->attach(
            'password', 'Veuillez saisir un nouveau mot de passe', '',
            'con_pwd', 'Veuillez confirmer votre mot de passe', ''
        );

        return $o;
    }

    protected function formIsOk($f)
    {
        if ($f->getElement('password')->getValue() !== $f->getElement('con_pwd')->getValue())
        {
            $f->getElement('con_pwd')->setError('Confirmation échouée');
            return false;
        }

        return true;
    }

    protected function save($data)
    {
        isset($data['etape_suivante']) or $data['etape_suivante'] = 'registration/contact';

        $contact = new tribes_contact($this->data->contact_id);
        $contact->save($data, 'user/password/confirmation');

        tribes_email::confirm("user/password/{$this->get->__1__}");

        return array('login', 'Mot de passe mis à jour');
    }
}
