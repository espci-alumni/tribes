<?php

class loop_edit_contact_activiteDiff extends loop_edit_contact_activite
{
    protected

    $allowAddDel = false,
    $editAdresse = false,
    $send;


    function __construct($f, $contact_id, $send)
    {
        $sql = "SELECT activite_id,
                    (
                        SELECT GROUP_CONCAT(
                            CONCAT(is_admin_confirmed, organisation)
                            ORDER BY af.sort_key
                            SEPARATOR '/'
                        )
                        FROM contact_organisation o
                            JOIN contact_affiliation af
                                ON af.organisation_id=o.organisation_id
                        WHERE af.activite_id=contact_activite.activite_id
                        GROUP BY ''
                    ) AS organisation,

                    ville AS c_ville,
                    pays AS c_pays,
                    service AS c_service,
                    titre AS c_titre,
                    date_debut AS c_date_debut,
                    date_fin AS c_date_fin,
                    site_web AS c_site_web,
                    keyword AS c_keyword,
                    is_shared,
                    admin_confirmed,
                    contact_data
                FROM contact_activite
                WHERE contact_id={$contact_id}
                    AND admin_confirmed<=contact_modified
                    AND is_obsolete<=0
                    AND contact_data!=''
                ORDER BY sort_key";
        $loop = new loop_sql($sql, array($this, 'filterActivite'));

        loop_edit::__construct($f, $loop);

        $this->send = $send;
    }

    function populateForm($a, $data, $counter)
    {
        parent::populateForm($a, $data, $counter);

        $this->form->add('check', 'decision', array(
            'isdata' => false,
            'item' => array(
                '1' => 'Publier',
                '0' => 'Refuser'
            )
        ));

        $this->form->getElement('is_shared')->setValue($data->is_shared);

        $this->send->attach('decision', "Merci de publier ou refuser chacune des sections", '');
    }

    function filterActivite($o)
    {
        !(int) $o->admin_confirmed && $o->new_activite = 1;

        $a = explode('/', $o->organisation);

        $org = array();
        $c_org = array();

        foreach ($a as $a)
        {
            if ('0' === $a[0]) $org[] = substr($a, 1);
            else $c_org[] = substr($a, 1);
        }

        $o->c_organisation = implode(' / ', $c_org);
        $o->organisation = implode(' / ', $org);

        if (!empty($o->contact_data) && $v = unserialize($o->contact_data))
            foreach ($v as $k => $v)
                $o->$k = $v;

        foreach ($o as $k => $v)
            if ('0000-00-00' === $v || '0000-00-00 00:00:00' === $v)
                $o->$k = '';

        return $o;
    }
}
