<?php

class agent_admin_import extends agent_pForm
{
    protected $requiredAuth = 'admin';

    static $importRx = array(
        'col_title' => '[^\t]{0,100}',
    );


    protected function composeForm($o, $f, $send)
    {
        $o->colList = implode(', ', array_keys(self::$importRx));

        $f->add('textarea', 'tsv', array('maxlength' => 1<<24));

        $send->attach('tsv', 'Aucune donnée à importer', "Format d'entrée non valide");

        return $o;
    }

    protected function formIsOk($f)
    {
        $r = $f->getElement('tsv')->getValue();
        $r = explode("\n", $r);
        $rx = '/^' . implode("\t", self::$importRx) . '$/u';

        foreach ($r as $r)
        {
            if ('' === trim($r)) continue;

            $r = preg_replace("' *\t *'", "\t", $r);

            if (!preg_match($rx, $r))
            {
                do $rx = array_pop(self::$importRx);
                while (null !== $rx && !preg_match("/^" . implode("\t", self::$importRx)  . "\t/u", $r, $m));

                $f->getElement('tsv')->setError("Entrée non-valide à partir de " . substr($r, strlen($m[0])) . " (ligne commençant par {$m[0]})");

                return false;
            }
        }

        return true;
    }

    function save($data)
    {
        set_time_limit(0);
        tribes_contact::$alias = array();

        $data = explode("\n", $data['tsv']);

        foreach ($data as $data)
        {
            if ('' === trim($data)) continue;

            $i = 0;
            $line = array();
            $data = explode("\t", $data);

            foreach (self::$importRx as $k => $v) $line[$k] = trim($data[$i++]);

            $this->saveContact((object) $line);
        }

        return array('', 'Import effectué');
    }

    protected function saveContact($line)
    {
        // To be implemented for each tribes instance
    }
}
