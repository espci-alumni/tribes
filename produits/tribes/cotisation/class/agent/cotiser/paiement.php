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

class extends agent_cotiser_bulletin
{
	public $get = array('__1__:c:[-_A-Za-z0-9]{8}');


	function control()
	{
		$this->get->__1__ || p::redirect('cotiser');

		$sql = "SELECT p.cotisation_id,
					p.token,
					p.contact_id,
					p.type_cotisation,
					p.cotisation,
					p.soutien,
					p.paiement_euro,
					p.email,
					p.commentaire,
					c.prenom_civil,
					c.nom_civil,
					c.promotion
				FROM cotisation p
					JOIN contact_contact c ON c.contact_id=p.contact_id
				WHERE p.token='{$this->get->__1__}'";
		$this->data = DB()->queryRow($sql);
		$this->data || p::redirect('cotiser');

		$this->contact_id = $this->data->contact_id;
	}

	function compose($o)
	{
		$o = $this->data;

		isset(self::$type[$o->type_cotisation]) && $o->type_cotisation = self::$type[$o->type_cotisation];

		return agent_pForm::compose($o);
	}

	protected function composeForm($o, $f, $send)
	{
		return $o;
	}
	
	protected function save($data)
	{
		DB()->autoExecute(
			'cotisation',
			array(
				'paiement_type' => 'ANL',
				'paiement_date' => date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']),
			),
			MDB2_AUTOQUERY_UPDATE,
			"cotisation_id={$this->data->data_id}"
		);

		return 'cotiser/bulletin';
	}
}
