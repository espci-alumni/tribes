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
        $cotisation_token = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 8);
        $this->contact_id or $data['cotisation_token'] = $cotisation_token;

        return parent::save($data, $message, $id);
    }
}
