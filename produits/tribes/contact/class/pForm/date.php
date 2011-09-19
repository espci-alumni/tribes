<?php

class extends self
{
    protected function get()
    {
        $a = parent::get();

        // Disabled because jQuery.ui.datepicker will do the job

        unset($a->onchange, $a->_placeholder);

        return $a;
    }

}
