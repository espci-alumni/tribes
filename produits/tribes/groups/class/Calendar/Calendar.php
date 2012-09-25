<?php

namespace Calendar;

class Calendar extends CalendarDbObject
{
    public function getTimeBoundaries($post_data)
    {
        $showdate = $post_data['showdate'];
        $viewtype = $post_data['viewtype'];
        //$timezone = $post_data['timezone'];

        $php_time = self::js2PhpTime($showdate);

        switch ($viewtype)
        {
            case "month":
                $start = mktime(0, 0, 0, date("m", $php_time) - 1, 1, date("Y", $php_time));
                $end = mktime(0, 0, -1, date("m", $php_time) + 1, 1, date("Y", $php_time));
                break;
            case "week":
                //suppose first day of a week is monday
                $monday = date("d", $php_time) - date('N', $php_time) + 1;
                $start = mktime(0, 0, 0, date("m", $php_time), $monday, date("Y", $php_time));
                $end = mktime(0, 0, -1, date("m", $php_time), $monday + 7, date("Y", $php_time));
                break;
            case "day":
                $start = mktime(0, 0, 0, date("m", $php_time), date("d", $php_time), date("Y", $php_time));
                $end = mktime(0, 0, -1, date("m", $php_time), date("d", $php_time) + 1, date("Y", $php_time));
                break;
        }

        if (isset($start) && isset($end))
            return array('start' => $start, 'end' => $end);
        else
            return null;
    }

    /**
     * @param $start
     * @param $end
     * @param $list
     * @return array
     */
    public static function getEvents($start, $end, $list)
    {
        $sql = 'SELECT * FROM '.CalendarDbObject::getTableName().' WHERE list = :list AND start BETWEEN :from AND :to';
        /** @var $stmt \Doctrine\DBAL\Driver\Statement */
        $stmt = CalendarDbObject::getDb()->prepare($sql);
        $stmt->bindValue('list', $list);
        $stmt->bindValue('from', self::php2MySqlTime($start));
        $stmt->bindValue('to', self::php2MySqlTime($end));
        $stmt->execute();
        $events = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        return $events;
    }

    /**
     * @param $id
     * @return Event
     */
    public static function getEvent($id)
    {
        $sql = 'SELECT * FROM '.CalendarDbObject::getTableName().' WHERE id = ?';
        /** @var $stmt \Doctrine\DBAL\Driver\Statement */
        $stmt = CalendarDbObject::getDb()->prepare($sql);
        $stmt->bindValue(1, (int)$id);
        $stmt->execute();
        $e = Event::hydrateArrayToObject($stmt->fetch());

        return $e;
    }

    public function deleteEvent($id)
    {
        $res = CalendarDbObject::getDb()->delete(
            CalendarDbObject::getTableName(),
            array('id' => $id)
        );

        return $res;
    }
}
