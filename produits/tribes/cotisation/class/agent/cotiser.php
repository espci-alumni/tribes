<?php

class agent_cotiser extends agent_registration
{
    function control()
    {
        parent::control();

        tribes::getConnectedId() && patchwork::redirect('cotiser/bulletin');
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
                patchwork::redirect('cotiser/failed');
            }
            else if (false !== $data)
            {
                SESSION::set(array(
                    'cotisation_email' => SESSION::get('email'),
                    'cotisation_next_step' => $data,
                ));

                patchwork::redirect('cotiser/bulletin');
            }
        }

        return $o;
    }

    protected function save($data)
    {
        $data = parent::save($data);

        if (false === $data) return false;

        SESSION::set(array(
            'cotisation_contact_id' => $this->data->contact_id,
            'cotisation_email' => $this->data->email,
            'cotisation_next_step' => $data,
        ));

        return 'cotiser/bulletin';
    }
}
