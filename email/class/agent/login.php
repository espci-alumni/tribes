<?php

class agent_login extends self
{
    protected function login($contact)
    {
        if ($contact->acces && $CONFIG['tribes.email.dsn'])
        {
            try
            {
                self::emailLogin($contact);
            }
            catch (Exception $e)
            {
                E('tribes/email exception', $e);
            }
        }

        return parent::login($contact);
    }

    protected static function emailLogin($contact)
    {
        $db = DB($CONFIG['tribes.email.dsn']);

        $domain = substr($CONFIG['tribes.emailDomain'], 1);

        $sql = $db->quote($contact->prenom_usuel . ' ' . $contact->nom_usuel . ' - ' . $domain);
        $sql = "INSERT INTO postfix_user
                    (user,domain,canonic,display,password,created)
                VALUES (
                    '{$contact->user}',
                    '{$domain}',
                    IF('{$contact->login}' NOT IN (user,''),'{$contact->login}',null),
                    {$sql},
                    '" . crypt($contact->password) . "',
                    NOW()
                )
                ON DUPLICATE KEY UPDATE
                    password=VALUES(password),
                    display=VALUES(display),
                    canonic=VALUES(canonic)";
        if (1 === $db->exec($sql))
        {
            // Si l'insertion a réussi

            // Synchronisation des emails alternatifs

            $user_id = $db->lastInsertId();

            $sql = "SELECT email, is_active
                    FROM contact_email
                    WHERE contact_id={$contact->contact_id}
                        AND is_obsolete<=0
                        AND contact_confirmed";

            $ins = array();

            foreach (DB()->query($sql) as $row)
            {
                $ins[] = "('{$row['email']}',{$user_id},{$row['is_active']},NOW())";
            }

            if ($ins)
            {
                $sql = "INSERT IGNORE INTO postfix_alt
                            (alt,user_id,forward,created)
                        VALUES " . implode(',', $ins);
                $db->exec($sql);
            }


            // Synchronisation des alias

            $sql = "SELECT alias AS hyphen,
                        REPLACE(alias,'-','') AS alias
                    FROM contact_alias
                    WHERE contact_id={$contact->contact_id}";
            $result = DB()->query($sql);

            $ins = array();

            foreach (DB()->query($sql) as $row)
            {
                $ins[] = "(
                    '{$row['alias']}',
                    '{$domain}',
                    'alias',
                    1,
                    '{$contact->user}@{$domain}',
                    '" . ($row['alias'] !== $row['hyphen'] ? $row['hyphen'] : '') . "',
                    NOW()
                )";
            }

            if ($ins)
            {
                $sql = "INSERT IGNORE INTO postfix_alias
                            (alias,domain,type,local,recipient,hyphen,created)
                        VALUES " . implode(',', $ins);
                $db->exec($sql);
            }
        }
    }
}
