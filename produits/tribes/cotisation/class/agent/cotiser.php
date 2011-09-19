<?php

class extends agent_registration
{
    function control()
    {
        parent::control();

        tribes::getConnectedId() && p::redirect('cotiser/bulletin');
    }

    protected function composeForm($o, $f, $send)
    {
        $o = parent::composeForm($o, $f, $send);

        $send = $f->add('submit', 'send_login');

        $o = agent_login::composeForm($o, $f, $send);

        if ($send->isOn())
        {
            $data = agent_login::save($send->getData());

            if ('login/failed' === $data)
            {
                p::redirect('cotiser/failed');
            }
            else if (false !== $data)
            {
                s::set(array(
                    'cotisation_email' => s::get('email'),
                    'cotisation_next_step' => $data,
                ));

                p::redirect('cotiser/bulletin');
            }
        }

        return $o;
    }

    protected function save($data)
    {
        $data = parent::save($data);

        if (false === $data) return false;

        s::set(array(
            'cotisation_contact_id' => $this->data->contact_id,
            'cotisation_email' => $this->data->email,
            'cotisation_next_step' => $data,
        ));

        return 'cotiser/bulletin';
    }
}
