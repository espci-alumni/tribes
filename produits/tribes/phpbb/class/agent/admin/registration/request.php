<?php

class agent_admin_registration_request extends self
{
    protected function createAccount($contact)
    {
        if ($CONFIG['tribes.phpbbDb'])
        {
            try
            {
                self::phpbbCreateAccount($contact);
            }
            catch (Exception $e)
            {
                E('tribes/phpbb exception', $e);
            }
        }

        return parent::createAccount($contact);
    }

    static function phpbbCreateAccount($contact)
    {
        $db = DB();
        $phpbb = $CONFIG['tribes.phpbbDb'] . '.' . $CONFIG['tribes.phpbbPrefix'];

        $data = array(
            'username' => $contact->login,
            'username_clean' => $contact->user,
            'user_email' => $contact->email,
            'user_email_hash' => crc32(strtolower($contact->email)) . strlen($contact->email),
            'user_regdate' => $_SERVER['REQUEST_TIME'],
            'group_id' => 2,
        );

        $db->insert($phpbb . 'users', $data);
        $user_id = $db->lastInsertId();

        $sql = "INSERT IGNORE INTO {$phpbb}user_group (user_id,group_id,user_pending)
                VALUES ({$user_id},2,0)";
        $db->exec($sql);

        return $user_id;
    }
}
