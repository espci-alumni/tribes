<?php

// TODO/XXX (non critique)
// Il serait intéressant que lorsque cette page s'affiche suite à un clic sur le lien
// présent dans l'email de confirmation, la même logique de confirmation de l'email
// que dans agent/registration/confirmation soit mise en oeuvre, pendant une période identique.
// Ceci nécessite bien sur de pouvoir distinguer ce cas du cas où l'user vient de cotiser/bulletin.

// TODO/XXX (non critique)
// Trouver un moyen (dans cette page ou une autre) pour que l'user soit invité à mettre à jour
// sa fiche dans l'annuaire, sans rompre le processus mental en cours !
// Peut-être simplement un lien dans cotiser/merci ?

class extends agent
{
	public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

	protected $data;


	function control()
	{
		$this->get->__1__ || p::redirect('cotiser');

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
		$this->data || p::redirect('cotiser');
	}

	function compose($o)
	{
		return $this->data;
	}
}
