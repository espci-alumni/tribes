<?php

use Calendar\Calendar;
use Calendar\Event;
use Calendar\CalendarDbObject;

class agent_events_edit extends agent
{
    public $get = array(
        'list:c' => 0,
        'id:i' => 0,
        'start:c' => 0,
        'end:c' => 0,
    );

    /** @var Event */
    private $event;

    public function control()
    {
        parent::control();

        /** @var $e Event */
        $e = Calendar::getEvent($this->get->id);

        /** @var $start int */
        $start = mktime(0, 0, 0, substr($this->get->start, 4, 2), substr($this->get->start, 6, 2), substr($this->get->start, 0, 4));
        /** @var $end int */
        $end = mktime(0, 0, 0, substr($this->get->end, 4, 2), substr($this->get->end, 6, 2), substr($this->get->end, 0, 4));
        $e->setStart(CalendarDbObject::php2MySqlTime($start));
        $e->setEnd(CalendarDbObject::php2MySqlTime($end));

        $e || patchwork::forbidden();

        $this->event = $e;
    }

    public function compose($o)
    {
        $o = parent::compose($o);

        $o->list = $this->get->list;
        $o->event_id = $this->event->getId();
        $o->event_subject = $this->event->getSubject();
        $o->event_color = $this->event->getColor();
        $o->event_location = $this->event->getLocation();
        $o->event_description = $this->event->getDescription();
        $o->event_is_all_day = $this->event->getIsAllDay();

        $s = explode(" ", CalendarDbObject::php2JsTime(CalendarDbObject::mySql2PhpTime($this->event->getStart())));
        $e = explode(" ", CalendarDbObject::php2JsTime(CalendarDbObject::mySql2PhpTime($this->event->getEnd())));
        $o->event_start_day = $s[0];
        $o->event_start_time = $s[1];
        $o->event_end_day = $e[0];
        $o->event_end_time = $e[1];

        return $o;
    }
}
