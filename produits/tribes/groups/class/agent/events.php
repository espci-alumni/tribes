<?php

use Calendar\CalendarDbObject;
use Calendar\Calendar;
use Calendar\Event;

class agent_events extends agent
{
    const contentType = 'application/json';

    const MSG_EVENT_UPDATED = 'Événement mis à jour.';
    const MSG_EVENT_MOVED = 'Événement déplacé.';
    const MSG_EVENT_CREATED = 'Événement créé.';
    const MSG_EVENT_DELETED = 'Événement supprimé.';
    const MSG_OOPS = 'Une erreur est advenue.';

    public $get = array(
        'list:c' => 0,
        'method:c' => 0,
    );


    public function compose($o)
    {
        $o = parent::compose($o);

        switch ($this->get->method)
        {
            case 'quickadd':
                $o->DATA = $this->composeQuickAdd();
                break;
            case 'delete':
                $o->DATA = $this->composeDelete();
                break;
            case 'quickupdate':
                $o->DATA = $this->composeQuickUpdate();
                break;
            case 'save':
                $o->DATA = $this->composeSave();
                break;
            default:
                $o->DATA = $this->composeList();
        }

        return $o;
    }

    private function composeSave()
    {
        $ret = array();

        $msg = '';
        $e = Calendar::getEvent((int)$_POST['id']);
        if (!$e->getId())
        {
            $e = new Event();
            $msg = self::MSG_EVENT_CREATED;
            $e->setCreator(SESSION::get('contact_id'));
            $e->setList($this->get->list);
        }
        else
        {
            $msg = self::MSG_EVENT_UPDATED;
        }

        $e = Event::hydrateArrayToObject(CalendarDbObject::getValuesForDb($_POST), $e);

        $res = $e->persist();

        if ($res)
        {
            $ret['Msg'] = $msg;
            $ret['IsSuccess'] = true;
            $ret['Data'] = CalendarDbObject::getDb()->lastInsertId();
        }
        else
        {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = self::MSG_OOPS;
        }

        return json_encode($ret);
    }

    private function composeQuickUpdate()
    {
        $ret = array();

        $e = Calendar::getEvent((int)$_POST['calendarId']);
        $res = $e->move($_POST['CalendarStartTime'], $_POST['CalendarEndTime'], (int)$_POST['timezone']);

        if ($res)
        {
            $ret['IsSuccess'] = true;
            $ret['Msg'] = self::MSG_EVENT_MOVED;
        }
        else
        {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = self::MSG_OOPS;
        }

        return json_encode($ret);
    }

    private function composeDelete()
    {
        $c = new Calendar();
        $res = $c->deleteEvent((int)$_POST['calendarId']);

        if ($res)
            return '{"IsSuccess":true,"Msg":"'.self::MSG_EVENT_DELETED.'"}';
        else
            return '{"IsSuccess":false,"Msg":"'.self::MSG_OOPS.'"}';
    }

    private function composeQuickAdd()
    {
        $ret = array();

        $e = new Event();

        $e->setList($this->get->list);
        $e->setCreator(SESSION::get('contact_id'));
        $e->setSubject(CalendarDbObject::getTextContentForDb($_POST['CalendarTitle']));
        $e->setStart(CalendarDbObject::php2MySqlTime(CalendarDbObject::js2PhpTime($_POST['CalendarStartTime'])));
        $e->setEnd(CalendarDbObject::php2MySqlTime(CalendarDbObject::js2PhpTime($_POST['CalendarEndTime'])));
        $e->setIsAllDay((int)$_POST['IsAllDayEvent']);
        $e->setTimezone((int)$_POST['timezone']);

        $res = $e->persist();

        if ($res)
        {
            $ret['IsSuccess'] = true;
            $ret['Msg'] = self::MSG_EVENT_CREATED;
            $ret['Data'] = CalendarDbObject::getDb()->lastInsertId();
        }
        else
        {
            $ret['IsSuccess'] = false;
            $ret['Msg'] = self::MSG_OOPS;
        }

        return json_encode($ret);
    }

    private function composeList()
    {
        /** @var $c Calendar */
        $c = new Calendar();
        $boundaries = $c->getTimeBoundaries($_POST);
        $start = $boundaries['start'];
        $end = $boundaries['end'];

        if ($boundaries)
        {
            $events = Calendar::getEvents($start, $end, $this->get->list);

            $ret = array();
            $ret['events'] = array();
            $ret["issort"] = true;
            $ret["start"] = CalendarDbObject::php2JsTime($boundaries['start']);
            $ret["end"] = CalendarDbObject::php2JsTime($boundaries['end']);
            $ret['error'] = null;

            foreach ($events as $it)
            {
                $ret['events'][] = array(
                    $it['id'],
                    $it['subject'],
                    CalendarDbObject::php2JsTime(CalendarDbObject::mySql2PhpTime($it['start'])),
                    CalendarDbObject::php2JsTime(CalendarDbObject::mySql2PhpTime($it['end'])),
                    $it['is_all_day'],
                    0, //more than one day event
                    //$it['InstanceType'],
                    0, //Recurring event,
                    $it['color'],
                    1, //editable
                    $it['location'],
                    '' //$attends
                );
            }

            return json_encode($ret);
        }

        //£TODO : quel JSON retourner si les boundaries sont foireuses ?
    }
}
