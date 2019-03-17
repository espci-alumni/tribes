<?php

class agent_cotiser_merci extends agent_tpe_callback
{
    const contentType = 'text/html';

    protected $template = 'cotiser/merci';

    function control()
    {
        if ('GET' === $_SERVER['REQUEST_METHOD'] && SESSION::get('cotisation_contact_id'))
        {
            SESSION::set(array(
                'cotisation_contact_id' => '',
                'cotisation_email' => '',
                'cotisation_next_step' => '',
            ));
        }

        parent::control();
    }
}
