<?php

class pTask_user_requests extends pTask_periodic
{
    function execute()
    {
        $sql = "SELECT 1
                FROM contact_contact
                WHERE password!=''
                    AND acces!=''
                    AND admin_confirmed<=contact_modified
                ORDER BY contact_modified";

        if (DB()->queryOne($sql))
        {
            tribes::startFakeSession();
            notification::send('user/requests');
        }
    }
}
