<?php

class tribes_contact extends tribes_common
{
    protected

    $table = 'contact',

    $dataFields = array(
        'sexe',
        'nom_civil',
        'prenom_civil',
        'nom_usuel',
        'prenom_usuel',
        'nom_etudiant',
        'date_naissance',
        'date_deces',
        'conjoint_email',
        'statut_activite',
        'login',
    );

    static

    $alias = array(
        array('prenom_usuel', 'nom_usuel'),
        array('prenom_usuel', 'nom_etudiant'),
        array('prenom_usuel', 'nom_civil'),
        array('prenom_civil', 'nom_usuel'),
        array('prenom_civil', 'nom_etudiant'),
        array('prenom_civil', 'nom_civil'),
    );


    function __construct($contact_id, $confirmed = false)
    {
        $this->metaFields += array(
            'conjoint_email' => 'stringNull',
            'acces' => 'string',
            'password' => 'saltedHash',
            'photo_token' => 'string',
            'cv_token' => 'string',
            'cv_text' => 'string',
            'etape_suivante' => 'string',
        );

        parent::__construct($contact_id, $confirmed);

        $contact_id || $this->contact_id = 0;
    }

    function save($data, $message = null, &$id = 0)
    {
        $db = DB();

        if (!$this->contact_id)
        {
            $data['photo_token'] = Patchwork::strongid(8);
            $data['cv_token'] = Patchwork::strongid(8);
        }

        if (empty($data['login'])) unset($data['login']);

        if ( !$this->confirmed
            && !empty($data['login'])
            && !empty($this->contactData['login'])
            && $data['login'] !== $this->contactData['login'])
        {
            $login = str_replace('-', '', $data['login']);

            $sql = "SELECT 1
                    FROM contact_alias
                    WHERE contact_id={$this->contact_id}
                        AND alias='{$login}'";

            if ($db->queryOne($sql))
            {
                $this->contactData['login'] = $data['login'];
                $login = $db->quote(serialize($this->contactData));

                $sql = "UPDATE contact_contact
                        SET login='{$data['login']}', contact_data={$login}
                        WHERE contact_id={$this->contact_id}";
                $db->exec($sql);
            }
        }

        $message = parent::save($data, $message, $this->contact_id);

        if (self::ACTION_INSERT === $message || self::ACTION_UPDATE === $message)
        {
            if ($this->confirmed)
            {
                if (!empty($data['login']))
                {
                    $login = str_replace('-', '', $data['login']);

                    $sql = "INSERT IGNORE INTO contact_alias (alias,contact_id)
                            VALUES ('{$login}',{$this->contact_id})";
                    $db->exec($sql);
                }

                for ($i = 0; $i < count(self::$alias); ++$i)
                {
                    $sql = self::$alias[$i];

                    if (empty($data[$sql[0]])) continue;
                    if (empty($data[$sql[1]])) continue;

                    $login = tribes::makeIdentifier($data[$sql[0]], "- 'a-z")
                     . '.' . tribes::makeIdentifier($data[$sql[1]], "- 'a-z");
                    $login = preg_replace("/[- ']+/", '-', $login);
                    $sql = "INSERT IGNORE INTO contact_alias (contact_id, alias)
                            VALUES ({$this->contact_id},'" . str_replace('-', '', $login) . "')";

                    if ($db->exec($sql))
                    {
                        $sql = "UPDATE contact_contact
                                SET login='{$login}', user=REPLACE(login,'-','')
                                WHERE contact_id={$this->contact_id}
                                    AND login=''";
                        $db->exec($sql);
                    }
                }
            }
            else
            {
                $this->updateContactModified($this->contact_id);
            }
        }

        return $message;
    }

    protected function filterDiplomeData($data)
    {
        if (empty($data['statut_activite'])) unset($data['statut_activite']);
        else $data['statut_activite'] = u::ucfirst($data['statut_activite']);

        return $data;
    }

    function delete($contact_id)
    {
        $sql = "DELETE FROM contact_alias WHERE contact_id={$contact_id}";
        DB()->exec($sql);

        parent::delete($contact_id);
    }
}
