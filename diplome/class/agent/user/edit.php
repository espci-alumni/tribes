<?php

class agent_user_edit extends self
{
    protected function composeContact($o, $f, $send)
    {
        $o = parent::composeContact($o, $f, $send);
        $o = $this->composeDiplome($o, $f, $send);

        return $o;
    }

    protected function composeDiplome($o, $f, $send)
    {
        $f->add('text', 'ecole');
        $f->add('text', 'promotion');
        $f->add('text', 'programme');
        $f->add('text', 'specialite');

        $send->attach(
            'ecole', '', '',
            'promotion', '', '',
            'programme', '', '',
            'specialite', '', ''
        );

        return $o;
    }
}
