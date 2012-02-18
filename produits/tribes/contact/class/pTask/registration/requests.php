<?php

class pTask_registration_requests extends pTask_periodic
{
    function execute()
    {
        $sql = "SELECT 1
                FROM contact_contact
                WHERE password!=''
                    AND acces=''
                LIMIT 1";

        if (DB()->fetchColumn($sql))
        {
            tribes::startFakeSession();
            notification::send('registration/requests');
        }
    }
}
