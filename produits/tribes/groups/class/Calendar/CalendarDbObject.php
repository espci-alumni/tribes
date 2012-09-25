<?php

namespace Calendar;

use Doctrine\DBAL\Connection;

class CalendarDbObject
{
    static function getTableName()
    {
        return $CONFIG['calendar_table_name'];
    }

    /**
     * @return \Doctrine\DBAL\Connection
     */
    static function getDb()
    {
        return DB();
    }

    static function js2PhpTime($jsdate)
    {
        if (preg_match('@(\d+)/(\d+)/(\d+)\s+(\d+):(\d+)@', $jsdate, $matches) == 1)
        {
            return $ret = mktime($matches[4], $matches[5], 0, $matches[1], $matches[2], $matches[3]);
        }
        else if (preg_match('@(\d+)/(\d+)/(\d+)@', $jsdate, $matches) == 1)
        {
            return $ret = mktime(0, 0, 0, $matches[1], $matches[2], $matches[3]);
        }
    }

    static function php2JsTime($phpDate)
    {
        return date("m/d/Y H:i", $phpDate);
    }

    static function php2MySqlTime($phpDate)
    {
        return date("Y-m-d H:i:s", $phpDate);
    }

    static function mySql2PhpTime($sqlDate)
    {
        $arr = date_parse($sqlDate);

        return mktime($arr["hour"], $arr["minute"], $arr["second"], $arr["month"], $arr["day"], $arr["year"]);
    }

    static function getValuesForDb($data)
    {
        if (
            array_key_exists('start_day', $data)
            && array_key_exists('start_time', $data)
            && array_key_exists('end_day', $data)
            && array_key_exists('end_day', $data)
        )
        {
            $data['start'] = self::php2MySqlTime(self::js2PhpTime($data['start_day'].' '.$data['start_time']));
            $data['end'] = self::php2MySqlTime(self::js2PhpTime($data['end_day'].' '.$data['end_time']));

            unset($data['start_day']);
            unset($data['start_time']);
            unset($data['end_day']);
            unset($data['end_time']);
        }

        //£TODO to implement
        return $data;
    }

    static function getTextContentForDb($string)
    {
        return filter_var($string, FILTER_SANITIZE_SPECIAL_CHARS);
    }
}
