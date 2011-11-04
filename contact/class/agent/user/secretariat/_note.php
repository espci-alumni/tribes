<?php

class agent_user_secretariat___x5Fnote extends agent_user_secretariat
{
    function compose($o)
    {
        $sql = "SELECT h.*, prenom_usuel AS origine_prenom, nom_usuel As origine_nom, login AS origine_login, origine_contact_id, date_contact
                FROM contact_historique h
                    JOIN contact_contact c ON c.contact_id=h.origine_contact_id
                WHERE h.contact_id={$this->contact_id}
                    AND historique='user/blocnote'
                    ORDER BY historique_id DESC";

        $o->notes = new loop_sql($sql, array($this, 'filterRow'));

        return $o;
    }

    function filterRow($o)
    {
        $o->details = unserialize($o->details);
        $o->note = $o->details['note'];

        unset($o->details);

        return $o;
    }
}
