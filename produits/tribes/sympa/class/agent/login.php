<?php

class extends self
{
    protected function login($contact)
    {
        if ($contact->acces && $CONFIG['tribes.emailDSN'])
        {
            self::sympaLogin($contact);
        }

        return parent::login($contact);
    }

    protected static function sympaLogin($contact)
    {
        $db = DB($CONFIG['tribes.emailDSN']);

        $data = array(
            'id_session' => mt_rand(1000000, 9999999) . mt_rand(1000000, 9999999),
            'start_date_session' => $_SERVER['REQUEST_TIME'],
            'date_session' => $_SERVER['REQUEST_TIME'],
            'remote_addr_session' => $_SERVER['REMOTE_ADDR'],
            'robot_session' => substr($CONFIG['tribes.emailDomain'], 1),
            'email_session' => $contact->login . $CONFIG['tribes.emailDomain'],
            'hit_session' => 1,
            'data_session' => ';auth="classic";data=""',
        );

        $db->autoExecute('sympa.session_table', $data);

        setcookie('sympa_session', $data['id_session'], 0, '/', $CONFIG['session.cookie_domain']);
    }
}
