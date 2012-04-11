<?php

class tribes_contact extends self
{
    function save($data, $message = null, &$id = 0)
    {
        $message = parent::save($data, $message, $id);

        switch ($message)
        {
        case self::ACTION_INSERT:
            if (!$this->confirmed) return $message;

        case self::ACTION_CONFIRM:
        case self::ACTION_UPDATE:
            $sql = "SELECT user, login
                    FROM contact_contact
                    WHERE contact_id={$this->contact_id}
                        AND admin_confirmed
                        AND contact_confirmed
                        AND user!=''";
            if (!$contact = DB()->fetchAssoc($sql)) return $message;
            $contact = (object) $contact;
        }

        $domain = substr($CONFIG['tribes.emailDomain'], 1);

        $db = DB($CONFIG['tribes.email.dsn']);

        $update = array();
        $aliases = array();

        if (!empty($data['password']))
        {
            $update['password'] = crypt($data['password']);
        }

        if ($this->confirmed)
        {
            if (isset($data['prenom_usuel'], $data['nom_usuel']))
            {
                $update['display'] = $data['prenom_usuel'] . ' ' . $data['nom_usuel'] . ' - ' . $domain;
            }

            if (!empty($data['login']))
            {
                $aliases[] = $data['login'];

                $update['canonic'] = $contact->user !== $data['login'] ? $data['login'] : null;
            }

            for ($i = 0; $i < count(self::$alias); ++$i)
            {
                if (empty($data[self::$alias[$i][0]])) continue;
                if (empty($data[self::$alias[$i][1]])) continue;

                $alias = tribes::makeIdentifier($data[self::$alias[$i][0]], "- 'a-z") . '.'
                       . tribes::makeIdentifier($data[self::$alias[$i][1]], "- 'a-z");
                $aliases[] = preg_replace("/[- ']+/", '-', $alias);
            }
        }

        if ($aliases)
        {
            $sql = "SELECT IF(local,2,1) FROM postfix_alias WHERE alias='{$contact->user}' AND domain='{$domain}'";
            $is_local = $db->fetchColumn($sql);
            $is_local = $is_local ? $is_local - 1 : $CONFIG['tribes.emailLocalRestricted'];

            foreach ($aliases as $aliases)
            {
                $alias = str_replace('-', '', $aliases);

                $sql = "INSERT INTO postfix_alias (alias,domain,type,local,recipient,hyphen,created)
                        VALUES ('{$alias}','{$domain}','alias',{$is_local},'{$contact->user}@{$domain}','" . ($alias !== $aliases ? $aliases : '') . "',NOW())
                        ON DUPLICATE KEY UPDATE hyphen=VALUES(hyphen)";
                $db->exec($sql);
            }
        }

        if ($update)
        {
            $update['user'] = $contact->user;
            $update['domain'] = $domain;

            !empty($contact->login)
                && $contact->login !== $contact->user
                && $update['canonic'] = $contact->login;

            $sql = array('created', 'NOW()', 'created=created');

            foreach ($update as $alias => $update)
            {
                $sql[0] .= ',' . $alias;
                $sql[1] .= ',' . (isset($update) ? $db->quote($update) : 'NULL');
                $sql[2] .= ',' . "{$alias}=VALUES({$alias})";
            }

            $sql = "INSERT INTO postfix_user ({$sql[0]})
                    VALUES ({$sql[1]})
                    ON DUPLICATE KEY UPDATE {$sql[2]}";
            $db->exec($sql);
        }

        return $message;
    }

    function delete($contact_id)
    {
        $sql = "SELECT GROUP_CONCAT(alias SEPARATOR \"','\")
                FROM contact_alias
                WHERE contact_id={$contact_id}
                GROUP BY contact_id";
        if ($sql = DB()->fetchColumn($sql))
        {
            $domain = substr($CONFIG['tribes.emailDomain'], 1);
            $sql = "DELETE FROM postfix_alias
                    WHERE domain='{$domain}' AND alias IN ('{$sql}')";
            DB($CONFIG['tribes.email.dsn'])->exec($sql);
        }

        parent::delete($contact_id);
    }
}
