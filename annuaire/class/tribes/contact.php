<?php

class tribes_contact extends self
{
    function delete($row_id)
    {
        parent::delete($row_id);

        tool_url::touch($CONFIG['tribes.annuaire.syncUrl'] . '?deleted_ref=' . $row_id);
    }
}
