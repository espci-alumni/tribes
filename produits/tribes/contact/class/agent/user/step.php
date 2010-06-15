<?php
/*
* Cette classe est une classe générique définissant l'ensemble des fonctions
* permettant de créer l'enchaînement des étapes du processus d'inscription
* ou de mise à jour réclamées
* @getNextStep calcule le nom de l'étape suivant l'étape actuelle
* @saveStep met à jour en base de données l'avancement de l'inscription du contact en cours
*/
class extends agent_user_edit
{
	protected

	$step,
	$afterStep = 'login/bienvenue';


	function compose($o)
	{
		$this->step = $this->getStep();

		$o->step_ref      = $this->step->__toString();
		$o->step_title    = $this->step->getTitle();
		$o->step_position = $this->step->getPosition();

		$o->steps = $this->step->getLoop();

		return parent::compose($o);
	}
	
	protected function getStep()
	{
		return new tribes_step(patchwork_class2file(substr(get_class($this), strlen(__CLASS__)+1)));
	}

	protected function composeForm($o, $f, $send)
	{
		return $o;
	}

	protected function save($data)
	{
		if (!$this->step->__toString()) return $this->afterStep;

		$data = $this->step->getNextStep();

		$sql = "UPDATE contact_contact
				SET etape_suivante='{$data}'
				WHERE contact_id='{$this->contact_id}'
				AND etape_suivante='{$this->step}'";
		DB()->exec($sql);

		return false !== $data ? "user/step/{$data}" : $this->afterStep;
	}
}
