<?php

use Patchwork\Utf8 as u;

class tribes_contact extends self
{
    function __construct($contact_id, $confirmed = false)
    {
        $this->diplomeConstruct($contact_id, $confirmed);
        parent::__construct($contact_id, $confirmed);
    }

    protected function filterData($data)
    {
        $data = parent::filterData($data);
        return $this->filterDiplomeData($data);
    }

    protected function diplomeConstruct($contact_id, $confirmed)
    {
        $this->dataFields[] = 'ecole';
        $this->dataFields[] = 'promotion';
        $this->dataFields[] = 'programme';
        $this->dataFields[] = 'specialite';
    }

    protected function filterDiplomeData($data)
    {
        isset($data['ecole']) && $data['ecole'] = u::ucfirst($data['ecole']);
        isset($data['programme']) && $data['programme'] = u::ucfirst($data['programme']);
        isset($data['specialite']) && $data['specialite'] = u::ucfirst($data['specialite']);

        if (empty($data['promotion'])) unset($data['promotion']);
        else $data['promotion'] = u::ucfirst($data['promotion']);

        return $data;
    }
}
