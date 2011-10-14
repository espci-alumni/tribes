<?php

class pipe_diffFormat
{
    static function php($f, $old, $new)
    {
        $old = (string) $old;
        $new = (string) $new;

        $f = strtr($f, array(
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
