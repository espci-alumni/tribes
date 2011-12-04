<?php

class agent_login extends self
{
    protected function login($contact)
    {
        if ($contact->acces && $CONFIG['tribes.mediaWikiDb'])
        {
            self::mediaWikiLogin($contact);
        }

        return parent::login($contact);
    }

    protected static function mediaWikiLogin($contact)
    {
        $db = DB();
        $mediaWikiDb = $CONFIG['tribes.mediaWikiDb'];
        $is_admin = tribes::connectedIsAuth('admin');

        $data = array(
            'user_name' => ucfirst($contact->user),
            'user_real_name' => $contact->login,
            'user_email' => $contact->email,
            'user_token' => Patchwork::strongid(),
        );

        $sql = "SELECT u.user_id,
                    g.ug_group IS NOT NULL AS is_admin
                FROM {$mediaWikiDb}.user u
                    LEFT JOIN {$mediaWikiDb}.user_groups g ON g.ug_user=u.user_id AND ug_group='bureaucrat'
                WHERE user_name='{$data['user_name']}'";
        $user = $db->queryRow($sql);

        if ($user)
        {
            $user_id = $user->user_id;

            $db->autoExecute(
                $mediaWikiDb . '.user',
                $data,
                MDB2_AUTOQUERY_UPDATE,
                "user_id={$user_id}"
            );

            $sql = '';

            if ($is_admin && !$user->is_admin)
            {
                $sql = "INSERT IGNORE INTO {$mediaWikiDb}.user_groups (ug_user,ug_group)
                        VALUES ({$user_id},'bureaucrat')";
            }
            else if (!$is_admin && $user->is_admin)
            {
                $sql = "DELETE FROM {$mediaWikiDb}.user_groups WHERE ug_user={$user_id} AND ug_group='bureaucrat'";
            }

            $sql && $db->exec($sql);
        }
        else
        {
            $user_id = agent_admin_registration_request::mediaWikiCreateAccount($contact, $data['user_token']);
        }

        setcookie($mediaWikiDb . 'UserID', $user_id, 0, $CONFIG['tribes.mediaWikiPath'], $CONFIG['session.cookie_domain']);
        setcookie($mediaWikiDb . 'UserName', $data['user_name'], 0, $CONFIG['tribes.mediaWikiPath'], $CONFIG['session.cookie_domain']);
        setcookie($mediaWikiDb . 'Token', $data['user_token'], 0, $CONFIG['tribes.mediaWikiPath'], $CONFIG['session.cookie_domain']);
        setcookie($mediaWikiDb . 'LoggedOut', '', 1, '/');
    }
}
