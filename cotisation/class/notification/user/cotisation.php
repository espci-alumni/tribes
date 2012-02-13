<?php

class notification_user_cotisation extends notification
{
    protected function doSend()
    {
        parent::doSend();

        if (!empty($this->context['paiement_date']) && 0 <= $this->context['paiement_euro'])
            $this->updateCotisationExpires();

        // Empêche d'avoir deux bulletins en attente de paiement en même temps
        $sql = "DELETE FROM cotisation WHERE NOT paiement_date AND paiement_mode='' AND contact_id={$this->contact_id}";
        DB()->exec($sql);

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
        $sql = "UPDATE contact_contact
                SET cotisation_expires='{$sql}-12-31'
                WHERE contact_id={$this->contact_id}";
        DB()->exec($sql);
    }
}
