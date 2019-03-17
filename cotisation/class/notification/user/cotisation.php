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
        else if (empty($this->context['notif_disabled']))
        {
            if (empty($this->context['email']))  user_error('No email to notify', E_USER_WARNING);
            else $this->mail($this->context['email']);
        }
    }

    protected function updateCotisationExpires()
    {
        $year = (int) substr($this->context['cotisation_date'], 0, 4);
        $contact_id = (int) $this->contact_id;
        $sql = "UPDATE contact_contact SET cotisation_expires='{$year}-12-31' WHERE contact_id={$contact_id} AND cotisation_expires<'{$year}-12-31'";

        DB()->exec($sql);
    }
}
