<?php

class agent_admin_user_secretariat___x5Fcotisation extends agent_admin_user_secretariat
{
    protected static $paiement_mode = array(
        'ESP' => 'Espèces',
        'CHQ' => 'Chèque',
        'VIR' => 'Virement',
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

        $send->attach(
            'cotisation_date', '', '',
            'type', 'Merci de saisir le type de cotisation', '',
            'paiement_euro', '', 'Merci de saisir un nombre entier ou décimal',
            'paiement_date', '', '',
            'paiement_mode', 'Merci de saisir le mode de paiement', '',
            'paiement_ref', '', ''
        );

        return parent::composeForm($o, $f, $send);
    }

    protected function save($data)
    {
        $db = DB();

        if (isset($data['paiement_euro']) && '' !== $data['paiement_euro'])
        {
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

            if ('0-remboursement' === $data['type']) $data['paiement_euro'] = -$data['paiement_euro'];
            if (empty($data['conjoint_email'])) unset($data['conjoint_email']);

            $data['cotisation_date'] || $data['cotisation_date'] = date('Y-m-d H:i:s', $_SERVER['REQUEST_TIME']);
            $data['paiement_date'] || $data['paiement_date'] = $data['cotisation_date'];

            list($data['cotisation'], $data['type']) = explode('-', $data['type'], 2);

            $data += array(
                'soutien' => $data['paiement_euro'] - $data['cotisation'],
                'contact_id' => $this->contact_id,
            );

            $db->autoExecute('cotisation', $data);

            notification::send('user/cotisation', $data);
        }

        return '';
    }
}
