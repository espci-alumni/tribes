<?php

class extends agent_user_edit
{
    protected function composeForm($o, $f, $send)
    {
        $o = $this->composeLogin($o, $f, $send);
        $o = $this->composeEmail($o, $f, $send);
        $o = $this->composePassword($o, $f, $send);

        return $o;
    }

    protected function save($data)
    {
        $this->contact->save($data); // Save the login

        $this->saveEmail($data);

        return '';
    }

    protected function saveEmail($data)
    {
        parent::saveEmail($data);

        $db = DB();

        $data = array_keys($this->deletedEmail);

        foreach ($data as $data)
        {
            $data = $db->quote("\n" . $data . "\n");

            $sql = "UPDATE contact_adresse
                    SET email_list=SUBSTRING(REPLACE(CONCAT('\n',email_list),{$data},'\n'),2)
                    WHERE contact_id={$this->contact_id}";

            $db->exec($sql);
        }
    }
}
