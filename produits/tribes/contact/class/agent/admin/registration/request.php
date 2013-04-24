<?php

class agent_admin_registration_request extends agent_admin_user_edit
{
    protected static

    $mergeTableInsert = array(),
    $mergeTableUpdate = array();


    protected $doublon_contact_id = 0;


    protected function composeForm($o, $f, $send)
    {
        $o = $this->composeLogin($o, $f, $send);
        $o = $this->composeContact($o, $f, $send);
        $o = $this->composeEmail($o, $f, $send);
        $o = $this->composeAdresse($o, $f, $send, true);
        $o = $this->composeActivite($o, $f, $send, true);

        $this->emails->adminMode = true;
        $this->adresses ->adminMode = true;
        $this->activites->adminMode = true;

        $f->add('textarea', 'message');
        $f->add('select', 'acces', array('item' => array(
            'membre' => 'Utilisateur',
            'admin' => 'Administrateur',
        )));

        $send->attach(
            'message', '', '',
            'acces', "Veuillez spécifier le type d'accès fourni à l'utilisateur", ''
        );

        $doublon_contact_items = self::buildDoublonData($f, clone $this->data);
        $doublon_contact_items = tribes::getDoublonSuggestions($this->contact_id, $doublon_contact_items);
        $doublon_contact_items += array(
            $this->contact_id => '(ajouter un nouveau nom au fichier)',
        );

        $refuse = $f->add('submit', 'refuse');

        $refuse->attach('message', '', '');

        if ($refuse->isOn())
        {
            $refuse = $this->save($refuse->getData());
            Patchwork::redirect($refuse[0]);
        }

        $f->add('check', 'doublon_contact_id', array('item' => $doublon_contact_items));
        $f->add('submit', 'updateDoublons');

        $send->attach('doublon_contact_id', 'Fusionner avec : merci de choisir un des noms proposés', '');

        return $o;
    }

    protected function composeLogin($o, $f, $send)
    {
        if (empty($this->data->login))
        {
            if ($this->data->login = $this->getFreeLogin($would_be_login)) $would_be_login = false;
            else $this->data->login = $would_be_login;
        }

        $o = parent::composeLogin($o, $f, $send);

        if (!$send->getStatus() && !empty($would_be_login))
        {
            $this->loginField->setError("Attention, identifiant déjà utilisé");
        }

        return $o;
    }

    protected function formIsOk($f)
    {
        if (!isset($_POST['f_doublon_contact_id'])) return false;

        $d = (int) $_POST['f_doublon_contact_id'];
        if ($d < 0) return false;

        if ($d === 0) return true; // Rejet de la demande

        $db = DB();

        $sql = "SELECT 1 FROM contact_contact WHERE contact_id={$d}";
        if (!$db->fetchColumn($sql)) return false;

        $this->doublon_contact_id = $d;

        if ($this->isLoginCollision($d)) return false;

        $this->loginField = false;

        return parent::formIsOk($f);
    }

    protected function save($data)
    {
        if ($this->doublon_contact_id)
        {
            $data['is_obsolete'] = 0;

            $this->saveContact($data);
            $this->saveEmail($data);
            $this->saveAdresse($data);
            $this->saveActivite($data);

            if ($this->doublon_contact_id != $this->contact_id)
            {
                $sql = "SELECT 1
                        FROM contact_contact
                        WHERE contact_id={$this->doublon_contact_id} AND acces";
                $accountCreated = DB()->fetchColumn($sql);

                self::mergeContacts($this->contact_id, $this->doublon_contact_id);
                $this->contact->delete($this->contact_id);
            }
            else $accountCreated = false;

            if (empty($CONFIG['tribes.emailDomain']))
            {
                $sql = "SELECT email
                        FROM contact_email
                        WHERE contact_id={$this->doublon_contact_id}
                            AND is_active
                            AND is_obsolete<=0
                            AND contact_confirmed
                        ORDER BY email_id LIMIT 1";
            }
            else
            {
                $sql = "CONCAT(login, '{$CONFIG['tribes.emailDomain']}')";
            }

            $sql = "SELECT contact_id, login, user, nom_usuel, prenom_usuel, acces,
                      ({$sql}) AS email
                    FROM contact_contact
                    WHERE contact_id={$this->doublon_contact_id}";
            $data = DB()->fetchAssoc($sql);

            $accountCreated || $this->createAccount((object) $data);

            $data['auth_login'] = empty($CONFIG['tribes.emailDomain']) ? $data['email'] : $data['login'];
            notification::send('registration/accepted', $data);
        }
        else
        {
            $data = array(
                'is_obsolete' => 1,
                'message' => $data['message'],
            );

            $this->contact->save($data, 'registration/refused', $this->contact_id);
        }

        return array('admin/registration/requests', true);
    }

    protected function saveContact($data)
    {
        $data += array(
            'is_active' => 1,
        );

        parent::saveContact($data);
    }


    static function __init()
    {
        $a = array(
            'is_obsolete' => "IF(VALUES(is_obsolete)=1 OR is_obsolete=1,1,IF(VALUES(is_obsolete)=-1 OR is_obsolete=-1,-1,0))",
        );

        self::$mergeTableInsert = array(
            'contact_email' => array('email_id', $a),
            'contact_adresse' => array('adresse_id', $a),
            'contact_activite' => array('activite_id', $a),
            'contact_contact' => array('contact_id', array(
                'is_obsolete' => "IF(VALUES(is_obsolete)=0 OR is_obsolete=0,0,IF(VALUES(is_obsolete)=-1 AND is_obsolete=-1,-1,1))",
                'acces' => "IF(VALUES(acces)='admin' OR acces='admin','admin',IF(VALUES(acces)='membre' OR acces='membre','membre',''))",
                'is_active' => "IF(VALUES(is_active)=1 OR is_active=1,1,0)",
            ))
        );

        self::$mergeTableUpdate = array(
            'contact_historique' => array('origine_contact_id' => "IF(origine_contact_id=%d,%d,origine_contact_id)"),
            'contact_alias' => array(),
        );
    }

    static function mergeContacts($from_contact_id, $to_contact_id)
    {
        $db = DB();

        foreach (self::$mergeTableInsert as $table => $info)
        {
            $sql = "SELECT * FROM {$table} WHERE contact_id={$from_contact_id}";
            foreach ($db->query($sql) as $from)
            {
                $db->delete($table, array($info[0] => $from[$info[0]]));

                $from['contact_id'] = $to_contact_id;
                $from = array_map(array($db, 'quote'), $from);

                $sql = "INSERT IGNORE INTO {$table} (" . implode(',', array_keys($from)) . ")
                        VALUES (" . implode(',', $from) . ")";

                foreach ($info[1] as $k => $v) $info[1][$k] = sprintf($v, $from_contact_id, $to_contact_id);

                $from = $info[1] + $from;
                $sql .= " ON DUPLICATE KEY UPDATE contact_id={$to_contact_id}";
                foreach ($from as $k => $v) if ("''" !== $v) $sql .= ",{$k}={$v}";

                $db->exec($sql);
            }
        }

        foreach (self::$mergeTableUpdate as $table => $info)
        {
            $sql = "UPDATE IGNORE {$table}
                    SET contact_id={$to_contact_id}";
            foreach ($info as $k => $v) $sql .= ",{$k}=" . sprintf($v, $from_contact_id, $to_contact_id);
            $sql .= " WHERE contact_id={$from_contact_id}";
            $db->exec($sql);

            $db->delete($table, array('contact_id' => $from_contact_id));
        }

        notification::send('contact/fusion', array('contact_id' => $to_contact_id));
    }

    protected static function buildDoublonData($f, $data)
    {
        $data->nom_civil = $f->getElement('nom_civil')->getValue();
        $data->nom_usuel = $f->getElement('nom_usuel')->getValue();
        $data->prenom_civil = $f->getElement('prenom_civil')->getValue();
        $data->prenom_usuel = $f->getElement('prenom_usuel')->getValue();

        return $data;
    }

    protected function getFreeLogin(&$would_be_login)
    {
        $db = DB();

        for ($i = 0; $i < count(tribes_contact::$alias); ++$i)
        {
            $login = tribes_contact::$alias[$i];

            if (empty($this->data->{$login[0]})) continue;
            if (empty($this->data->{$login[1]})) continue;

            $login = tribes::makeIdentifier($this->data->{$login[0]}, "- 'a-z") . '.'
                   . tribes::makeIdentifier($this->data->{$login[1]}, "- 'a-z");
            $login = preg_replace("/[- ']+/", '-', $login);

            0 === $i and $would_be_login = $login;

            $sql = "SELECT 1
                    FROM contact_alias
                    WHERE alias=REPLACE('{$login}','-','')";

            if (!$db->fetchColumn($sql)) return $login;
        }

        return false;
    }

    protected function createAccount($contact)
    {
        // Hook used by superpositions
    }
}
