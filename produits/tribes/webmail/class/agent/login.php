<?php

class agent_login extends self
{
    protected function login($contact)
    {
        if ($contact->acces && $CONFIG['tribes.webmailUrl'])
        {
            self::webmailLogin($contact);
        }

        return parent::login($contact);
    }

    protected static function webmailLogin($contact)
    {
        setcookie(
            'tribes_webmail',
            self::MD5Encrypt($contact->user . $CONFIG['tribes.emailDomain'] . ':' . $contact->password, $CONFIG['tribes.webmailSecret']),
            0,
            $CONFIG['tribes.webmailPath'],
            $CONFIG['session.cookie_domain']
        );
    }


    /*
     * Code inspired from Login:Auto Plugin for Squirrelmail
     * By Jay Guerette <JayGuerette@pobox.com>
     *
     */

    protected static function MD5Keycrypt($txt, $key)
    {
        $val = $txt;
        $key = md5($key);
        $keylen = strlen($key);
        $txtlen = strlen($txt);

        for ($i = 0; $i < $txtlen; ++$i)
        {
            $val[$i] = $txt[$i] ^ $key[$i % $keylen];
        }

        return $val;
    }

    static function MD5Encrypt($txt, $key)
    {
        $val = $txt . $txt;
        $cryptkey = md5(uniqid(mt_rand() . pack('d', lcg_value()), true));
        $keylen = strlen($cryptkey);
        $txtlen = strlen($txt);

        for ($i = 0, $j = 0; $i < $txtlen; ++$i)
        {
            $val[$j++] = $cryptkey[$i % $keylen];
            $val[$j++] = $txt[$i] ^ $cryptkey[$i % $keylen];
        }

        return base64_encode(self::MD5Keycrypt($val, $key));
    }

    static function MD5Decrypt($txt, $key)
    {
        $txt = base64_decode($txt);
        $txtlen = strlen($txt);

        if ($txtlen % 2) return false;

        $val = substr($txt, 0, $txtlen>>1);
        $txt = self::MD5Keycrypt($txt, $key);

        for ($i = 0, $j = 0; $i < $txtlen; ++$i)
        {
            $val[$j++] = $txt[$i++] ^ $txt[$i];
        }

        return $val;
    }
}
