<?php

class pTask_user_resetToken extends pTask_periodic
{
    function execute()
    {
        $sql = "UPDATE contact_email
                SET token=NULL
                WHERE token_expires<=NOW()";
        DB()->exec($sql);
    }
}
