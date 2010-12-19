<?php

class extends agent
{
	protected static

	$request_bin  = 'data/atossips/request',
	$response_bin = 'data/atossips/response',

	$parameters = array(
		// XXX Deux principaux paramètres à ajuster
		// 'merchant_id' => '012345678901234',
		'pathfile'     => 'data/atossips/pathfile',

		// --- Géré automatiquement ---
		// 'transaction_id'       => '123456',     // généré par le binaire
		// 'amount'               => '123456',     // en centimes
		// 'normal_return_url'    => 'http://...', // RETURN_URL
		// 'cancel_return_url'    => 'http://...', // CANCEL_URL
		// 'automatic_return_url' => 'http://...', // AUTO_RESPONSE_URL
		// 'customer_id'          => '',           // 19 pristine req. to resp. data (forbidden chars: [|;:"])
		// 'customer_email'       => '',

		// --- Valeurs par défaut du fichier parmcom.default ---
		// 'bgcolor'           => 'ffffff',
		// 'block_align'       => 'center',
		// 'block_order'       => '1,2,3,4,5,6,7,8',
		// 'condition'         => 'SSL',
		// 'currency_code'     => '978', // CURRENCY | 978 pour €
		// 'header_flag'       => 'no',
		// 'language'          => 'fr',
		// 'merchant_country'  => 'fr',
		// 'payment_means'     => 'CB,1,VISA,1,MASTERCARD,1',
		// 'target'            => '_top',
		// 'textcolor'         => '000000',

		// --- Autres paramètres (transaction) ---
		// 'capture_day'         => '', // nombre de jours avant transaction
		// 'capture_mode'        => '', // mode d’envoi en banque de la transaction.
		// 'data'                => '', // 2048 chars for advanced parameters

		// --- Autres paramètres (session) ---
		// 'customer_ip_address' => '', //   19 pristine req. to resp. data
		// 'order_id'            => '', //   32 pristine req. to resp. data (forbidden chars: [|;:"])
		// 'return_context'      => '', //  256 pristine req. to resp. data (forbidden chars: [|;:"])
		// 'caddie'              => '', // 2048 pristine req. to resp. data

		// --- Autres paramètres (pages sur le serveur SIPS) ---
		// 'advert'              => '', // ADVERT      | bannière centrée
		// 'logo_id'             => '', // LOGO        | image alignée à gauche
		// 'logo_id2'            => '', // LOGO2       | image alignée à droite
		// 'background_id'       => '', // BACKGROUND  | image de fond
		// 'submit_logo'         => '', // SUBMIT_LOGO | image du bouton Valider
		// 'cancel_return_logo'  => '', // CANCEL_LOGO | image du bouton Annuler
		// 'normal_return_logo'  => '', // RETURN_LOGO | image du bouton Retour
		// 'receipt_complement'  => '', // RECEIPT     | 3072 HTML data affichées sur l'accusé de réception
		// 'templatefile'        => '', // TEMPLATE    | fichier HTML de personnalisation des pages
	);

	protected $requiredAuth = false;


	// Methods related to request handling

	protected static function composeTpe($o, $ref, $euro, $email)
	{
		// Disable Firefox back-forward cache
		header('Cache-Control: no-store');

		$request_bin = patchworkPath(self::$request_bin);
		$p           = self::$parameters;

		$p['pathfile']               = patchworkPath($p['pathfile']);
		$p['amount']                 = $euro * 100;
		$p['normal_return_url']      = p::__BASE__() . 'cotiser/merci?T$=' . p::getAntiCSRFtoken();
		$p['cancel_return_url']      = p::__BASE__() . 'cotiser/paiement/' . $ref . '?T$=' . p::getAntiCSRFtoken();
		$p['automatic_response_url'] = p::__BASE__() . 'tpe/callback';
		$p['customer_id']            = $ref;
		$p['customer_email']         = $email;

		foreach ($p as $k => $v) $request_bin .= ' ' . escapeshellarg("{$k}={$v}");

		$v = explode('!', `{$request_bin}`);
		$o->atossips_html = $v[3];

		return $o;
	}
}
