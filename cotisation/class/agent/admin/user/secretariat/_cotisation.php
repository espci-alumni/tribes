<?php

class agent_admin_user_secretariat___x5Fcotisation extends agent_admin_user_secretariat
{
    protected static $paiement_mode = array(
        'ESP' => 'Espèces',
        'CHQ' => 'Chèque',
        'VIR' => 'Virement',
        'NSP' => 'Autre',
    );

    protected function composeForm($o, $f, $send)
    {
        $type_options = agent_cotiser_bulletin::getCotisationTypeOptions($o);
        $type_options['item'] += array('0-don' => 'Don seul', '0-remboursement' => 'Remboursement');

        $f->add('date', 'cotisation_date');
        $f->add('check', 'type', $type_options);
        $f->add('text', 'paiement_euro', array('valid' => 'float'));
        $f->add('date', 'paiement_date');
        $f->add('check', 'paiement_mode', array('item' => self::$paiement_mode));
        $f->add('text', 'paiement_ref');
        $f->add('check', 'notif_disabled', array('item' => array(1 => "Désactiver l'email de notification"), 'multiple' => true, 'isdata' => true));

        $send->attach(
            'cotisation_date', '', '',
            'type', 'Merci de saisir le type de cotisation', '',
            'paiement_euro', '', 'Merci de saisir un nombre entier ou décimal',
            'paiement_date', '', '',
            'paiement_mode', 'Merci de saisir le mode de paiement', '',
            'paiement_ref', '', '',
            'notif_disabled', '', ''
        );

        $o = parent::composeForm($o, $f, $send);

        $o->cotisations->addFilter(array($this, 'filterCotisation'));

        if ('POST' === $_SERVER['REQUEST_METHOD'])
        {
            $cs = array();
            while ($c = $o->cotisations->loop())
            {
                if (isset($c->f_del) && $c->f_del->isOn())
                {
                    DB()->delete('cotisation', array('cotisation_id' => $c->cotisation_id));
                    Patchwork::redirect();
                }
            }

            $o->cotisations = new loop_array($cs, 'filter_rawArray');
        }

        return $o;
    }

    function filterCotisation($o)
    {
        if (in_array($o->paiement_mode, array('ESP','CHQ','VIR','NSP')))
        {
            $o->f_del = new pForm_submit($this->form, 'del_cotisation_' . $o->cotisation_id, array());
        }

        return $o;
    }

    protected function save($data)
    {
        $db = DB();

        if (isset($data['paiement_euro']) && '' !== $data['paiement_euro'])
        {
            $notif_disabled = !empty($data['notif_disabled']);

            $data = array(
                'token' => Patchwork::strongId(8),
                'cotisation_date' => $data['cotisation_date'],
                'type' => $data['type'],
                'paiement_euro' => $data['paiement_euro'],
                'paiement_date' => $data['paiement_date'],
                'paiement_mode' => $data['paiement_mode'],
                'paiement_ref' => $data['paiement_ref'],
                'conjoint_email' => isset($data['conjoint_email']) ? $data['conjoint_email'] : '',
            );

            if (empty($data['conjoint_email'])) unset($data['conjoint_email']);

            $data['cotisation_date'] || $data['cotisation_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
            $data['paiement_date'] || $data['paiement_date'] = $data['cotisation_date'];

            if ('0-remboursement' === $data['type'])
            {
                $data['type'] = 'remboursement';
                $data['paiement_euro'] = $data['cotisation'] = -$data['paiement_euro'];
            }
            else list($data['cotisation'], $data['type']) = explode('-', $data['type'], 2);

            $sql = "SELECT email
                    FROM contact_email
                    WHERE contact_id={$this->contact_id}
                        AND is_obsolete<=0
                        AND contact_confirmed
                    ORDER BY is_active DESC, is_obsolete DESC
                    LIMIT 1";

            $data += array(
                'soutien' => $data['paiement_euro'] - $data['cotisation'],
                'contact_id' => $this->contact_id,
                'email' => DB()->fetchColumn($sql),
            );

            $db->insert('cotisation', $data);

            if ($notif_disabled) $data['notif_disabled'] = true;
            notification::send('user/cotisation', $data);
        }

        return '';
    }
}
