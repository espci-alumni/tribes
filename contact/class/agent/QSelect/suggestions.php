<?php

class agent_QSelect_suggestions extends agent_QSelect
{
    public $get = array('__0__');

    protected $requiredAuth = false;

    protected static $types = array(
        'keyword' => true,
    );


    function control()
    {
        if (!isset(self::$types[$this->get->__0__])) patchwork::forbidden();

        $this->template = self::$types[$this->get->__0__] ? 'QSelect/Suggest.js' : 'QSelect/Search.js';

        parent::control();
    }

    function compose($o)
    {
        $sql = "SELECT suggestion AS VALUE
                FROM item_suggestions
                WHERE type='{$this->get->__0__}'
                ORDER BY suggestion";

        $o->DATA = new loop_sql($sql);

        if (self::$types[$this->get->__0__])
        {
            $o->separator = ', ';
            $o->separatorRx = '\s*[,;\\/]\s*';
        }

        return $o;
    }
}
