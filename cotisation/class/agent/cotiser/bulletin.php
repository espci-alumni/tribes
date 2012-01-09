<?php

// TODO : cas mot de passe perdu
// Dans le cas où cette page s'affiche suite à un passage par registration
// alors que le contact est déjà accepté, il faudrait ajouter une question
// dans le formulaire "Envoyer un nouveau mot de passe ? O/N".
// Et envoyer un lien de récuparation de mot de passe, le cas échéant.
// cf. notification::send('user/password/request',...) dans agent/registration/collision

class agent_cotiser_bulletin extends agent_pForm
{
    protected static

    $soutien = array(
         50 => '50€',
        100 => '100€',
        200 => '200€',
    );

    protected $contact_id;


    function control()
    {
        $this->contact_id = tribes::getConnectedId();
        $this->contact_id || $this->contact_id = SESSION::get('cotisation_contact_id');
        $this->contact_id || Patchwork::redirect('cotiser');

        $this->data = SESSION::get('cotisation_bulletin');
    }

    function compose($o)
    {
        $sql = "SELECT email
                FROM contact_email
                WHERE contact_id={$this->contact_id}
                    AND is_active
                    AND contact_confirmed
                LIMIT 1";

        $sql = "SELECT
                    sexe,
                    nom_usuel AS nom,
                    prenom_usuel AS prenom,
                    ({$sql}) AS email,
                    IF (cotisation_expires>=NOW()+INTERVAL 1 DAY, cotisation_expires, 0) AS cotisation_expires
                FROM contact_contact
                WHERE contact_id={$this->contact_id}";
        $o = DB()->queryRow($sql);

        SESSION::get('cotisation_email') || SESSION::set('cotisation_email', $o->email);

        $sql = "SELECT *
                FROM cotisation
                WHERE contact_id={$this->contact_id}
                    AND paiement_date
                ORDER BY cotisation_id DESC";
        $o->cotisations = new loop_sql($sql);

        return parent::compose($o);
    }

    protected function composeForm($o, $f, $send)
    {
        $item = array('item' => tribes::getCotisationTypes($this->contact_id));
        $f->add('check', 'type', $item);

        $item= array('item' => self::$soutien + array(
            0 => (object) array(
                'caption' => 'Autre',
                'onclick' => 'this.form.f_soutien.focus()',
            )
        ));
        $f->add('check', 'soutien_suggestion', $item);

        $f->add('text', 'soutien', array('valid' => 'int', 0));
        $f->add('textarea', 'commentaire');

        $send->attach(
            'type', 'Merci de choisir votre catégorie de cotisation', '',
            'soutien_suggestion', '', '',
            'soutien', '', '',
            'commentaire', '', ''
        );

        return $o;
    }

    protected function save($data)
    {
        SESSION::set('cotisation_bulletin', $data);

        $data += array(
            'token' => Patchwork::strongId(8),
            'contact_id' => $this->contact_id,
            'cotisation_date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
            'email' => SESSION::get('cotisation_email'),
        );

        list($data['cotisation'], $data['type']) = explode('-', $data['type'], 2);

        if ($data['soutien_suggestion']) $data['soutien'] = $data['soutien_suggestion'];
        unset($data['soutien_suggestion']);

        if (0 == $data['cotisation'])
        {
            $data['paiement_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
            $data['paiement_euro'] = 0;
        }

        DB()->autoExecute('cotisation', $data);
        empty($data['paiement_date']) || notification::send('user/cotisation', $data);

        return 0 == $data['cotisation'] || $data['soutien']
            ? 'cotiser/paiement/' . $data['token']
            : 'cotiser/merci';
    }
}
