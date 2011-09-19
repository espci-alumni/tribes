<?php

class
{
    static function php($f, $old, $new)
    {
        $old = p::string($old);
        $new = p::string($new);

        $f = strtr(p::string($f), array(
            '{new}' => ($old !== $new) ? '%1%2' : '&nbsp;',
            '{old}' => $old
        ));

        return $f;
    }

    static function js()
    {
        ?>/*<script>*/

function($f, $old, $new)
{
    $old = str($old);
    $new = str($new);

    return str($f).replace('{old}', $old).replace('{new}', ($old !== $new) ? '%1%2' : '&nbsp;');
}

<?php    }
}
