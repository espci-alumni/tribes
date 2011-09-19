<?php

class extends pForm_date
{
    protected function init(&$param)
    {
        $value =& $this->form->rawValues[$this->name];
        if ($value) $value = '01' . preg_replace("'[^0-9]'u", '', $value);

        if (isset($param['default']))
        {
            $param['default'] = preg_replace("'^\d{4}-\d{2}$'u", '$0-01', $param['default']);
            $param['default'] = preg_replace("'^(\d{2})-(\d{4})$'u", '$2-$1-01', $param['default']);
        }

        parent::init($param);
    }

    protected function get()
    {
        $a = parent::get();
        $a->value = preg_replace("'^\d{2}-(\d{2})-(\d{4})$'u", '$1-$2', $a->value);
        $a->onchange = 'var v=valid_date("01"+this.value.replace(/[^0-9]+/,""));if(v)this.value=v.substr(3);';
//        $a->_placeholder = T('mm-aaaa');
        $a->_class = "text monthyear";

        return $a;
    }
}
