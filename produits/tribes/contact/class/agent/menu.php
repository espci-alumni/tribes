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
	}

	function compose($o)
	{
		$o->prenom_usuel = s::get('prenom_usuel');
		$o->nom_usuel    = s::get('nom_usuel');
		$o->acces        = s::get('acces');

		$o->accueil_url = $o->acces ? self::ACCUEIL_CONNECTED : 'user/edit';
		$o->public_url  = self::ACCUEIL_PUBLIC;

		$o->iframe_src = s::flash('iframe_src');
		$o->iframe_src || $o->iframe_src = s::flash('referer');
		$o->iframe_src || $o->iframe_src = p::base($o->accueil_url, 1);

		$o->onglets = new loop_array(self::$onglets, 'filter_rawArray');

		return $o;
	}
}
