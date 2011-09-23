<?php

class pipe_formatDate
{
    protected static $format = '$3/$2/$1';

    static function __constructStatic()
    {
        self::$format = T(self::$format);
    }

    static function php($s)
    {
        $s = p::string($s);
        $s = str_replace('0000-00-00', '', $s);
        return preg_replace("'(\d\d\d\d)-(\d\d)-(\d\d)'", self::$format, $s);
    }

    static function js()
    {
        ?>/*<script>*/

function($s)
{
    return str($s)
        .replace(/0000-00-00/g, '')
        .replace(/(\d\d\d\d)-(\d\d)-(\d\d)/g, <?php echo jsquote(self::$format); ?>);
}

<?php    }
}
