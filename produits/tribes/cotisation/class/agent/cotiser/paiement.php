<?php

// TODO : exploiter SESSION::get('cotisation_next_step');
// Trouver un moyen (dans cette page ou une autre) pour que l'user soit invité à mettre à jour
// sa fiche dans l'annuaire, sans rompre le processus mental en cours !
// Peut-être simplement un lien dans cotiser/merci ?

class agent_cotiser_paiement extends agent
{
    public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

    protected $data;


    function control()
    {
        $this->get->__1__ || patchwork::redirect('cotiser');

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
        $this->data || patchwork::redirect('cotiser');
    }

    function compose($o)
    {
        return $this->data;
    }
}
