<?php

class agent_user_cv extends agent_user_photo
{
    const contentType = '';

    function compose($o)
    {
        $file = patchworkPath('data/cv/') . $this->file;

        switch (strrchr($this->file, '.'))
        {
        case '.pdf': $this->contentType = 'application/pdf'; break;
        case '.doc': $this->contentType = 'application/msword'; break;
        }

        $this->sendfile($file);

        return $o;
    }
}
