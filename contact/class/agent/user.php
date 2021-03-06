<?php

class agent_user extends agent
{
    public $get = '__1__:i:1';

    protected

    $contact,
    $requiredAuth = true;

    protected static $selectFields = 'contact_id, login, sexe, prenom_usuel, nom_usuel, nom_etudiant, acces, photo_token, cv_token';


    function control()
    {
        $this->get->__1__ || Patchwork::forbidden();

        parent::control();

        if (!SESSION::get('acces') && $this->get->__1__ != $this->connected_id) Patchwork::forbidden();
        if ('admin' !== SESSION::get('acces')) Patchwork::forbidden();

        $sql = "SELECT " . self::$selectFields . "
                FROM contact_contact
                WHERE contact_id={$this->get->__1__}";
        $this->contact = DB()->fetchAssoc($sql) or Patchwork::forbidden();
        $this->contact = (object) $this->contact;
    }

    function compose($o)
    {
        $o = $this->contact;
        $o->email = $o->login && !empty($CONFIG['tribes.emailDomain']) ? $o->login . $CONFIG['tribes.emailDomain'] : '';
        $o->connected_is_admin = $this->connected_is_admin;
        $o->connected_is_user = $this->connected_id == $this->contact->contact_id;

        $file = explode('.', $o->photo_token) + array(1 => 'jpg', 'jpg');
        $o->photo_token = implode('.', $file);
        $o->hasPhoto = file_exists(patchworkPath('data/photo/') . "{$file[0]}.{$file[1]}");

        $file = explode('.', $o->cv_token) + array(1 => 'pdf', 'pdf');
        $o->cv_token = implode('.', $file);
        $o->hasCv = file_exists(patchworkPath('data/cv/') . "{$file[0]}.{$file[1]}");

        $o->adresses = new loop_user_adresse($this->contact->contact_id, !empty($CONFIG['tribes.emailDomain']));
        $o->activites = new loop_user_activite($this->contact->contact_id);

        return $o;
    }
}
