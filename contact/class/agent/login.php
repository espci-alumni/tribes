<?php

class agent_login extends agent_pForm
{
    protected

    $maxage = -1,
    $requiredAuth = false;

    protected static

    $sessionFields = 'c.contact_id, password, login, user, nom_usuel, prenom_usuel, etape_suivante, acces, photo_token, cv_token';


    protected function composeForm($o, $f, $send)
    {
        $f->add('text', 'login');
        $f->add('password', 'password');

        $send->attach(
            'login', 'Veuillez saisir votre identifiant', '',
            'password', 'Veuillez saisir votre mot de passe', ''
        );

        return $o;
    }

    protected function save($data)
    {
        if (!empty($CONFIG['tribes.emailDomain']))
        {
            $sql = $CONFIG['tribes.emailDomain'];

            if (0 === strcasecmp($sql, substr($data['login'], -strlen($sql))))
            {
                $data['login'] = substr($data['login'], 0, -strlen($sql));
            }

            $sql = str_replace('-', '', $data['login']);

            $sql = strpos($sql, '@')
                ? ("contact_email u ON u.contact_confirmed AND u.email=" . DB()->quote($data['login']))
                : ("contact_alias u ON u.alias=" . DB()->quote($sql));
        }
        else
        {
            $sql = "contact_email u ON u.contact_confirmed AND u.email=" . DB()->quote($data['login']);
        }

        $sql = "SELECT " . self::$sessionFields . "
                FROM contact_contact c
                    JOIN {$sql} AND u.contact_id=c.contact_id
                WHERE password!=''";
        $sql = DB()->query($sql);

        while ($row = $sql->fetch())
            if (Patchwork::matchSaltedHash($data['password'], $row['password']))
                break;

        if (!$row) return 'login/failed';
        $row = (object) $row;

        $contact_id = $row->contact_id;

        $row->saltedPassword = $row->password;
        $row->password = $data['password'];
        $row->referer = SESSION::flash('referer');

        if (!empty($CONFIG['tribes.emailDomain']))
            $row->email = $row->login ? $row->login . $CONFIG['tribes.emailDomain'] : '';
        else
            $row->email = $row->login ? $data['login'] : '';

        if ($sql = SESSION::flash('confirmed_email_id'))
        {
            $email = new tribes_email($contact->contact_id);
            $email->save(array('contact_confirmed' => true), null, $sql);
        }

        SESSION::regenerateId(true, true);
        SESSION::set($row);

        $row->acces && $this->login($row);

        if (!empty($row->etape_suivante)) return "user/step/{$row->etape_suivante}";

        // TODO: Vérifier qu'on a au moins un email actif pour ce contact

        $sql = "SELECT 1
                FROM contact_email
                WHERE contact_id={$contact_id}
                    AND NOT contact_confirmed
                    AND admin_confirmed
                    AND is_obsolete<=0";
        if (DB()->fetchColumn($sql)) return 'user/step/emailConfirmation';

        $sql = SESSION::flash('referer');

        return $row->acces ? ($sql ? $sql : agent_menu::ACCUEIL_CONNECTED) : 'user/edit/contact';
    }

    protected function login($contact)
    {
        // Hook for superpositions
    }
}
