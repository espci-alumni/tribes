<?php

class agent_cotiser extends agent_registration
{
    public $get = array('__1__:c:[-_A-Za-z0-9]{8}');

    function control()
    {
        parent::control();

        if ($this->get->__1__)
        {
            $sql = "SELECT contact_id AS cotisation_contact_id,
                        CONCAT(login,'{$CONFIG['tribes.emailDomain']}') AS cotisation_email
                    FROM contact_contact
                    WHERE cotisation_token='{$this->get->__1__}'";
            if ($user = DB()->fetchAssoc($sql))
            {
                SESSION::set($user);
                Patchwork::redirect('cotiser/bulletin');
            }
        }

        tribes::getConnectedId() && Patchwork::redirect('cotiser/bulletin');
        
        Patchwork::redirect('https://www.espci.org/page/cotiser');
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
                Patchwork::redirect('cotiser/failed');
            }
            else if (false !== $data)
            {
                SESSION::set(array(
                    'cotisation_email' => SESSION::get('email'),
                    'cotisation_next_step' => $data,
                ));

                Patchwork::redirect('cotiser/bulletin');
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
