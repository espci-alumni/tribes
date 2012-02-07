<?php

class notification_user_cotisation extends notification
{
    protected function doSend()
    {
        parent::doSend();

        empty($this->context['paiement_date']) || $this->updateCotisationExpires();

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
            $has_active = false;

            $sql = "SELECT email, is_active
                    FROM contact_email
                    WHERE contact_id={$this->contact_id}
                        AND is_obsolete<=0
                    ORDER BY is_active DESC, is_obsolete DESC";
            $result = DB()->query($sql);

            while ($row = $result->fetchRow())
            {
                if ($has_active && !$row->is_active) break;
                $this->mail($row->email);
                $row->is_active && $has_active = true;
            }
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
