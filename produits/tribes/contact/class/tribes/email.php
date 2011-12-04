<?php

class tribes_email extends tribes_common
{
    protected

    $table = 'email',
    $dataFields = array(
        'email',
    );

    function __construct($contact_id, $confirmed = false)
    {
        $this->metaFields += array(
            'token' => 'stringNull',
            'token_expires' => 'sql',
        );

        parent::__construct($contact_id, $confirmed);
    }

    function save($data, $message = null, &$id = 0)
    {
        if (!$id && !empty($data['email_id'])) $id = $data['email_id'];

        if (!$id && !isset($data['email']))
        {
            user_error(__METHOD__ . '() input error: please provide email or email_id.');
            return;
        }
        else if (isset($data['email'])) $data['email'] = strtolower($data['email']);

        $sql = "UPDATE contact_email
                SET is_obsolete=-1, admin_confirmed=0
                WHERE is_obsolete=1
                    AND contact_id={$this->contact_id}
                    AND email" . (!$id ? "=" . DB()->quote($data['email']) : "_id={$id}");
        DB()->exec($sql);

        if (!$this->confirmed && (!isset($data['token']) || !isset($data['email'])))
        {
            $sql = "SELECT email_id, email, admin_confirmed,
                        token IS NULL OR token_expires<=NOW() AS token_has_expired
                    FROM contact_email
                    WHERE contact_id={$this->contact_id}
                        AND email" . (!$id ? "=" . DB()->quote($data['email']) : "_id={$id}");
            if ($sql = DB()->queryRow($sql))
            {
                $id = $sql->email_id;
                $data['email'] = $sql->email;

                if (!isset($data['token']) && !(int) $sql->admin_confirmed && $sql->token_has_expired)
                {
                    $data['token'] = 'confirm/email/' . Patchwork::strongid(8);
                }
            }
            else if ($id) return;
            else isset($data['token']) || $data['token'] = 'confirm/email/' . Patchwork::strongid(8);
        }

        empty($data['token']) || $data += array('token_expires' => 'NOW() + INTERVAL ' . tribes::PENDING_PERIOD);

        $data += array('admin_confirmed' => false);

        return parent::save($data, $message, $id);
    }

    function delete($row_id)
    {
        parent::delete($row_id);

        if (!$this->confirmed)
        {
            $sql = "UPDATE contact_email
                    SET token=NULL
                    WHERE contact_id={$this->contact_id}
                        AND is_obsolete=1";
            DB()->exec($sql);
        }
    }


    static function confirm($token, $resetToken = true)
    {
        $sql = "SELECT 1
                FROM contact_email
                WHERE is_active
                    AND contact_id=e.contact_id
                    AND is_obsolete<=0
                    AND contact_data!=''
                LIMIT 1";

        $sql = "SELECT email_id, contact_id, contact_data, email, contact_confirmed,
                    ($sql) AS has_active_email
                FROM contact_email e
                WHERE token='{$token}'
                    AND token_expires>=NOW()";
        $row = DB()->queryRow($sql);
        if (!$row) return false;

        $email = new self($row->contact_id, true);

        $data = $row->contact_data ? unserialize($row->contact_data) : array();

        $resetToken && $data['token'] = '';
        $data['is_obsolete'] = 0;
        $data['admin_confirmed'] = true;
        $row->has_active_email || $data['is_active'] = 1;

        if ($row->contact_id && $row->contact_id == tribes::getConnectedId())
        {
            $data['contact_confirmed'] = true;
            $row->contact_confirmed = true;
        }

        $email->save($data, 'user/email/confirmation', $row->email_id);

        if (!(int) $row->contact_confirmed)
        {
            SESSION::flash('confirmed_email_id', $row->email_id);
            Patchwork::redirect('login/confirmEmail');
        }

        return true;
    }
}
