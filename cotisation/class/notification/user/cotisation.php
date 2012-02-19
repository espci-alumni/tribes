<?php

class notification_user_cotisation extends notification
{
    protected function doSend()
    {
        parent::doSend();

        if (!empty($this->context['paiement_date']) && 0 <= $this->context['paiement_euro'])
            $this->updateCotisationExpires();

        // Empêche d'avoir deux bulletins en attente de paiement en même temps
        DB()->delete('cotisation', array('paiement_date' => 0, 'paiement_mode' => '', 'contact_id' => $this->contact_id));

        if (!((float) $this->context['paiement_euro']) && ((float) $this->context['soutien']))
        {
            // Cotisation gratuite et soutien complémentaire déclaré :
            // on attend la réception du soutien pour envoyer l'email de notification.
        }
        else if (empty($this->context['email']))
        {
            user_error('No email to notify', E_USER_WARNING);
        }
        else
        {
            $this->mail($this->context['email']);
        }
    }

    protected function updateCotisationExpires()
    {
        $sql = substr($this->context['cotisation_date'], 0, 4);
        DB()->update(
            'contact_contact',
            array('cotisation_expires' => "{$sql}-12-31"),
            array('contact_id' => $this->contact_id)
        );
    }
}
