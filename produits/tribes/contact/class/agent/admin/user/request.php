<?php

class agent_admin_user_request extends agent_admin_user_edit
{
    protected

    $adresses,
    $activites;


    function compose($o)
    {
        $o = $this->data;

        $sql = "SELECT sexe AS c_sexe,
                    prenom_usuel AS c_prenom_usuel,
                    nom_usuel AS c_nom_usuel,
                    prenom_civil AS c_prenom_civil,
                    nom_civil AS c_nom_civil,
                    nom_etudiant AS c_nom_etudiant,
                    date_naissance AS c_date_naissance,
                    login AS c_login
                FROM contact_contact
                WHERE contact_id={$this->contact_id}";

        foreach (DB()->fetchAssoc($sql) as $k => $v)
            isset($o->$k) || $o->$k = $v;

        return parent::compose($o);
    }

    protected function composeForm($o, $f, $send)
    {
        $o = $this->composeLogin($o, $f, $send);
        $o = agent_user_edit::composeContact($o, $f, $send);
        $o = $this->composeAdresse($o, $f, $send);
        $o = $this->composeActivite($o, $f, $send);

        $this->adresses ->adminMode = true;
        $this->activites->adminMode = true;

        return $o;
    }

    protected function composePhoto($o, $f, $send)
    {
        $file = explode('.', $this->data->photo_token) + array(1 => 'jpg', 'jpg');
        $o->photo_token = implode('.', $file);
        $o->hasPhoto = file_exists(patchworkPath('data/photo/') . "{$file[0]}.{$file[1]}");
        $o->newPhoto = file_exists(patchworkPath('data/photo/') . "{$file[0]}.{$file[2]}~");

        if ($o->newPhoto)
        {
            $f->add('check', 'decision_photo', array(
                'item' => array('1' => 'Accepter', '0' => 'Rejeter')
            ));

            $this->photoField = $f->add('file', 'photo', array('valid' => 'image', null, array('jpg','gif','png')));

            $send->attach(
                'decision_photo', 'Veuillez accepter ou rejeter la photo', '',
                'photo', '', "Format d'image non valide"
            );
        }

        return $o;
    }

    protected function composeCv($o, $f, $send)
    {
        $file = explode('.', $this->data->cv_token) + array(1 => 'pdf', 'pdf');
        $o->cv_token = implode('.', $file);
        $o->hasCv = file_exists(patchworkPath('data/cv/') . "{$file[0]}.{$file[1]}");
        $o->newCv = file_exists(patchworkPath('data/cv/') . "{$file[0]}.{$file[2]}~");

        if ($o->newCv)
        {
            $f->add('check', 'decision_cv', array(
                'item' => array('1' => 'Accepter', '0' => 'Rejeter')
            ));

            $this->cvField = $f->add('file', 'cv');

            $send->attach(
                'decision_cv', 'Veuillez accepter ou rejeter le CV', '',
                'cv', '', "Type de fichier non valide"
            );
        }

        return $o;
    }

    protected function composeAdresse($o, $f, $send, $new = false)
    {
        $this->adresses = new loop_edit_contact_adresseDiff($f, $this->contact_id, $send);

        return $o;
    }

    protected function composeActivite($o, $f, $send, $new = false)
    {
        $this->activites = new loop_edit_contact_activiteDiff($f, $this->contact_id, $send);

        return $o;
    }

    protected function save($data)
    {
        if (isset($this->photoField) && !$data['decision_photo'])
        {
            $file = explode('.', $this->data->photo_token) + array(1 => 'jpg', 'jpg');
            @unlink(patchworkPath('data/photo/') . "{$file[0]}.{$file[2]}~");
        }

        if (isset($this->cvField) && !$data['decision_cv'])
        {
            $file = explode('.', $this->data->cv_token) + array(1 => 'pdf', 'pdf');
            @unlink(patchworkPath('data/cv/') . "{$file[0]}.{$file[2]}~");
        }

        $this->saveContact($data);
        $this->saveAdresse($data);
        $this->saveActivite($data);

        return 'admin/user/requests';
    }
}
