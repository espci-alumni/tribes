<?php

class extends agent
{
	const

	contentType = '',
	ACCUEIL_CONNECTED = 'user/edit/contact',
	ACCUEIL_PUBLIC = 'login';

	protected $requiredAuth = false;

	protected static $onglets = array();


	function control()
	{
		$this->connected_id = tribes::getConnectedId();
		$this->connected_id || p::redirect(self::ACCUEIL_PUBLIC);
		$this->connected_is_admin = tribes::connectedIsAuth('admin');
	}

	function compose($o)
	{
		$o->accueil_url = self::ACCUEIL_CONNECTED;
		$o->public_url  = self::ACCUEIL_PUBLIC;

		$o->iframe_src = s::flash('iframe_src');
		$o->iframe_src || $o->iframe_src = s::flash('referer');
		$o->iframe_src || $o->iframe_src = p::base(self::ACCUEIL_CONNECTED, 1);

		$o->onglets = new loop_array(self::$onglets, 'filter_rawArray');

		$o->connected_is_admin = $this->connected_is_admin;

		$o->prenom_usuel = s::get('prenom_usuel');
		$o->nom_usuel    = s::get('nom_usuel');

		return $o;
	}
}
