<?php

class extends self
{
    protected function login($contact)
    {
        parent::login($contact);

        if ($contact->acces && $CONFIG['tribes.phpbbDb'])
        {
            self::phpbbLogin($contact);
        }
    }

    protected static function phpbbLogin($contact)
    {
        $db = DB();
        $phpbbDb = $CONFIG['tribes.phpbbDb'];
        $phpbb = $phpbbDb . '.' . $CONFIG['tribes.phpbbPrefix'];
        $is_admin = tribes::connectedIsAuth('admin');

        $sql = "SELECT u.user_id, user_email, username,
                    g1.group_id IS NOT NULL AND g2.group_id IS NOT NULL AS is_admin,
                    g1.group_id IS NOT NULL OR g2.group_id IS NOT NULL AS is_moderator
                FROM {$phpbb}users u
                    LEFT JOIN {$phpbb}user_group g1 ON g1.user_id=u.user_id AND g1.group_id=4
                    LEFT JOIN {$phpbb}user_group g2 ON g2.user_id=u.user_id AND g2.group_id=5
                WHERE username_clean='{$contact->user}'";
        $user = $db->queryRow($sql);

        if ($user)
        {
            $user_id = $user->user_id;

            if ($user->user_email != $contact->email || $user->username != $contact->login)
            {
                $data = array(
                    'username' => $contact->login,
                    'user_email' => $contact->email,
                    'user_email_hash' => crc32(strtolower($contact->email)) . strlen($contact->email),
                );

                $db->autoExecute(
                    $phpbb . 'users',
                    $data,
                    MDB2_AUTOQUERY_UPDATE,
                    "user_id={$user_id}"
                );
            }

            $sql = '';

            if ($is_admin && !$user->is_admin)
            {
                $sql = "INSERT IGNORE INTO {$phpbb}user_group (user_id,group_id,user_pending)
                        VALUES ({$user_id},4,0),({$user_id},5,0)";
            }
            else if (!$is_admin && $user->is_moderator)
            {
                $sql = "DELETE FROM {$phpbb}user_group WHERE user_id={$user_id} AND group_id IN (4,5)";
            }

            $sql && $db->exec($sql);
        }
        else
        {
            $user_id = agent_admin_registration_request::phpbbCreateAccount($contact);
        }

        $data = array(
            'session_id' => md5(p::strongId()),
            'session_user_id' => $user_id,
            'session_last_visit' => $_SERVER['REQUEST_TIME'],
            'session_start' => $_SERVER['REQUEST_TIME'],
            'session_time' => $_SERVER['REQUEST_TIME'],
            'session_ip' => $_SERVER['REMOTE_ADDR'],
            'session_browser' => $_SERVER['HTTP_USER_AGENT'],
            'session_page' => 'index.php',
            'session_autologin' => 1,
            'session_admin' => $is_admin ? 1 : 0,
        );

        $db->autoExecute($phpbb . 'sessions', $data, MDB2_AUTOQUERY_INSERT);

        setcookie($phpbbDb . '_u', $user_id, 0, $CONFIG['tribes.phpbbPath'], $CONFIG['session.cookie_domain']);
        setcookie($phpbbDb . '_sid', $data['session_id'], 0, $CONFIG['tribes.phpbbPath'], $CONFIG['session.cookie_domain']);
    }
}
