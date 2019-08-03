<?php

use Patchwork\Utf8 as u;

class tribes_contact extends self
{
    function __construct($contact_id, $confirmed = false)
    {
        $this->metaFields += array(
            'cotisation_token' => 'string',
        );

        parent::__construct($contact_id, $confirmed);
    }

    function save($data, $message = null, &$id = 0)
    {
        $token = '';
        for ($i = 0; $i < 8; ++$i) {
            $token .= substr(str_shuffle("abcdefghjkmnpqrstuvwxyz"), 0, 1);
        }
        $this->contact_id or $data['cotisation_token'] = $token;

        return parent::save($data, $message, $id);
    }
}
