<?php

class agent_user_historique extends agent
{
    public $get = array('p:i:1' => 1);

    protected $contact_id;

    protected static $perPage = 20;

    function compose($o)
    {
        isset($this->contact_id) || $this->contact_id = $this->connected_id;

        $start = ($this->get->p - 1) * self::$perPage;
        $length = self::$perPage;

        $sql = "SELECT COUNT(*) FROM contact_historique WHERE contact_id={$this->contact_id}";
        $o->results_nb = DB()->fetchColumn($sql);
        $o->results_per_page = self::$perPage;
        $o->page = $this->get->p;

        $o->contact_id = $this->contact_id;

        $sql = "SELECT h.*, prenom_usuel, nom_usuel, login
                FROM contact_historique h
                    JOIN contact_contact c USING (contact_id)
                WHERE h.contact_id={$this->contact_id}
                ORDER BY historique_id DESC
                LIMIT {$start}, {$length}";

        $o->historiques = new loop_sql($sql, array($this, 'filterRow'));

        return $o;
    }

    function filterRow($o)
    {
        if ($o->origine_contact_id !== $o->contact_id)
        {
            $sql = "SELECT
                        login AS origine_login,
                        prenom_usuel AS origine_prenom,
                        nom_usuel AS origine_nom
                    FROM contact_contact
                    WHERE contact_id={$o->origine_contact_id}";

            foreach (DB()->fetchAssoc($sql) as $k => $v)
                isset($o->$k) || $o->$k = $v;
        }
        else
        {
            $o->origine_login = $o->login;
            $o->origine_prenom = $o->prenom_usuel;
            $o->origine_nom = $o->nom_usuel;
        }

        if ($o->details && $sql = unserialize($o->details))
        {
            empty($sql['photo_token']) || $sql['photo_token'] = implode('.', explode('.', $sql['photo_token']) + array(1 => 'jpg', 'jpg'));
            empty($sql['cv_token']) || $sql['cv_token'] = implode('.', explode('.', $sql['cv_token']) + array(1 => 'pdf', 'pdf'));

            $o->details = new loop_array(array($sql), array(__CLASS__, 'filterDetails'));
        }

        return $o;
    }

    static function filterDetails($o)
    {
        $o = $o->VALUE;
        foreach ($o as &$v) is_array($v) && $v = '[' . implode(', ', $v) . ']';
        return $o;
    }
}
