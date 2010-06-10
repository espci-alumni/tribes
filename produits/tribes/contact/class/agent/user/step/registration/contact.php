<?php

class extends agent_user_step_registration
{
	protected function composeForm($o,$f,$send)
	{
		$o = $this->composeContact($o, $f, $send);
		$o = parent::composePhoto($o, $f, $send);

		return $o;
	}

	protected function composeContact($o, $f, $send)
	{
		$f->add('name', 'nom_civil');
		$f->add('name', 'prenom_civil');
		$f->add('name', 'nom_etudiant');
		$f->add('name', 'nom_usuel');
		$f->add('name', 'prenom_usuel');
		$f->add('date', 'date_naissance');
		$f->add('email', 'conjoint_email');

		$send->attach(
				'nom_civil', "Veuillez renseigner votre nom civil", '',
				'prenom_civil', "Veuillez renseigner votre prenom civil", '',
				'nom_etudiant', "Veuillez renseigner le nom d'étudiant", '',
				'nom_usuel'   , "Veuillez renseigner le nom usuel"     , '',
				'prenom_usuel', "Veuillez renseigner le prénom usuel"  , '',
				'date_naissance', "Veuillez renseigner votre date de naissance", '',
				'conjoint_email', '', ''
		);
	
		return $o;
	}

	protected function save($data)
	{
		$this->savePhoto($data);
		$this->contact->save($data);

		return parent::save($data);
	}
}	
