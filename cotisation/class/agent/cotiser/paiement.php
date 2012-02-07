<?php

// TODO: exploiter SESSION::get('cotisation_next_step');

class agent_cotiser_paiement extends agent_pForm
{
    public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

    protected $data;


    function control()
    {
        $this->get->__1__ || Patchwork::redirect('cotiser');

        $sql = "SELECT
                    p.token,
                    p.type,
                    p.cotisation,
                    p.soutien,
                    p.paiement_euro,
                    p.email,
                    p.commentaire,
                    c.contact_id,
                    c.sexe,
                    c.prenom_usuel AS prenom,
                    c.nom_usuel AS nom
                FROM cotisation p
                    JOIN contact_contact c ON c.contact_id=p.contact_id
                WHERE p.token='{$this->get->__1__}'";
        $this->data = DB()->queryRow($sql);
        $this->data || Patchwork::redirect('cotiser');
    }

    function compose($o)
    {
        if ($this->data->cotisation > 0) return $this->data;
        else return parent::compose($this->data);
    }

    protected function composeForm($o, $f, $send)
    {
        $f->add('check', 'confirm', array('multiple' => true, 'item' => array(1 => "Je certifie que les informations ci-dessus correspondent à ma situation actuelle.")));
        $send->attach('confirm', 'Merci de préciser si les informations de ce récapitulatif correspondent à votre situation actuelle.', '');

        return $o;
    }

    protected function save($data)
    {
        $db = DB();

        $sql = "UPDATE cotisation SET soutien=0, paiement_date=NOW() WHERE token=" . $db->quote($this->data->token);
        $db->exec($sql);

        $sql = "SELECT * FROM cotisation WHERE token=" . $db->quote($this->data->token);
        notification::send('user/cotisation', (array) $db->queryRow($sql));

        return 'cotiser/merci';
    }
}
