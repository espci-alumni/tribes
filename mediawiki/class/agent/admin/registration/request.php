<?php

class agent_admin_registration_request extends self
{
    protected function createAccount($contact)
    {
        if ($CONFIG['tribes.mediaWikiDb'])
        {
            try
            {
                self::mediaWikiCreateAccount($contact);
            }
            catch (Exception $e)
            {
                E('tribes/mediawiki exception', $e);
            }
        }

        return parent::createAccount($contact);
    }

    static function mediaWikiCreateAccount($contact, $user_token = '')
    {
        $db = DB();
        $mediaWikiDb = $CONFIG['tribes.mediaWikiDb'];

        $data = array(
            'user_name' => ucfirst($contact->user),
            'user_real_name' => $contact->login,
            'user_email' => $contact->email,
            'user_token' => $user_token,
            'user_email_authenticated' => date('YmdHis'),
        );

        $db->insert($mediaWikiDb . '.user', $data);
        $user_id = $db->lastInsertId();

        $sql = "INSERT IGNORE INTO {$mediaWikiDb}.user_groups (ug_user,ug_group)
                VALUES ({$user_id},'user')";
        $db->exec($sql);

        return $user_id;
    }
}
