<?php

namespace Calendar;

class Event extends CalendarDbObject
{
    /**
     * @var int
     */
    private $id;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @var String
     */
    private $list;

    /**
     * @return String
     */
    public function getList()
    {
        return $this->list;
    }

    public function setList($list)
    {
        $this->list = $list;
    }

    /**
     * @var String
     */
    private $subject;

    /**
     * @return String
     */
    public function getSubject()
    {
        return $this->subject;
    }

    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @var String
     */
    private $location;

    /**
     * @return String
     */
    public function getLocation()
    {
        return $this->location;
    }

    public function setLocation($location)
    {
        $this->location = $location;
    }

    /**
     * @var String
     */
    private $description;

    /**
     * @return String
     */
    public function getDescription()
    {
        return $this->description;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @var String
     */
    private $color;

    /**
     * @return String
     */
    public function getColor()
    {
        return $this->color;
    }

    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @var String
     */
    private $start;

    public function getStart()
    {
        return $this->start;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    /**
     * @var String
     */
    private $end;

    public function getEnd()
    {
        return $this->end;
    }

    public function setEnd($end)
    {
        $this->end = $end;
    }

    /**
     * @var String
     */
    private $is_all_day;

    /**
     * @return String
     */
    public function getIsAllDay()
    {
        return $this->is_all_day;
    }

    public function setIsAllDay($is_all_day)
    {
        $this->is_all_day = $is_all_day;
    }

    /**
     * @var int
     */
    private $timezone;

    /**
     * @return int
     */
    public function getTimezone()
    {
        return $this->timezone;
    }

    public function setTimezone($timezone)
    {
        $this->timezone = $timezone;
    }

    /**
     * @var String
     */
    private $robot;

    /**
     * @return String
     */
    public function getRobot()
    {
        return $this->robot;
    }

    public function setRobot($robot)
    {
        $this->robot = $robot;
    }

    /**
     * @var int
     */
    private $creator;

    /**
     * @return String
     */
    public function getCreator()
    {
        return $this->creator;
    }

    public function setCreator($creator)
    {
        $this->creator = $creator;
    }

    /**
     * @var String
     */
    private $recurring_rule;

    public function getRecurringRule()
    {
        return $this->recurring_rule;
    }

    public function setRecurringRule($recurring_rule)
    {
        $this->recurring_rule = $recurring_rule;
    }

////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

    /**
     * @return String
     */
    public function persist()
    {
        if ($this->getId() && Calendar::getEvent($this->getId()))
        {
            $res = CalendarDbObject::getDb()->update(
                CalendarDbObject::getTableName(),
                self::hydrateObjectToArray($this),
                array('id' => $this->getId())
            );
        }
        else
        {
            $res = CalendarDbObject::getDb()->insert(
                CalendarDbObject::getTableName(),
                array(
                    'color' => $this->getColor(),
                    'creator' => $this->getCreator(),
                    'description' => $this->getDescription(),
                    'end' => $this->getEnd(),
                    'is_all_day' => $this->getIsAllDay(),
                    'list' => $this->getList(),
                    'location' => $this->getLocation(),
                    'recurring_rule' => $this->getRecurringRule(),
                    'robot' => substr($CONFIG['tribes.emailDomain'], 1),
                    'start' => $this->getStart(),
                    'subject' => $this->getSubject(),
                )
            );
        }

        return $res;
    }

    /**
     * @return Event
     */
    public static function hydrateArrayToObject($data, Event $e = null)
    {
        !$e && $e = new Event();

        isset($data['color']) && $e->setColor($data['color']);
        isset($data['creator']) && $e->setCreator($data['creator']);
        isset($data['description']) && $e->setDescription($data['description']);
        isset($data['end']) && $e->setEnd($data['end']);
        isset($data['id']) && $e->setId($data['id']);
        isset($data['is_all_day']) && $e->setIsAllDay($data['is_all_day']);
        isset($data['list']) && $e->setList($data['list']);
        isset($data['location']) && $e->setLocation($data['location']);
        isset($data['recurring_rule']) && $e->setRecurringRule($data['recurring_rule']);
        isset($data['robot']) && $e->setRobot($data['robot']);
        isset($data['start']) && $e->setStart($data['start']);
        isset($data['subject']) && $e->setSubject($data['subject']);

        return $e;
    }
    
    /**
     * @return array
     */
    public static function hydrateObjectToArray(Event $e)
    {
        $data = array();

        $data['color'] = $e->getColor();
        $data['creator'] = $e->getCreator();
        $data['description'] = $e->getDescription();
        $data['end'] = $e->getEnd();
        $data['id'] = $e->getId();
        $data['is_all_day'] = $e->getIsAllDay();
        $data['list'] = $e->getList();
        $data['location'] = $e->getLocation();
        $data['recurring_rule'] = $e->getRecurringRule();
        $data['robot'] = $e->getRobot();
        $data['start'] = $e->getStart();
        $data['subject'] = $e->getSubject();
        
        return $data;
    }

    public function move($start, $end)
    {
        $sql = 'UPDATE '.CalendarDbObject::getTableName().' SET start = :start, end = :end WHERE ID = :id';
        /** @var $stmt \Doctrine\DBAL\Driver\Statement */
        $stmt = CalendarDbObject::getDb()->prepare($sql);
        //£TODO : call these date conversion function outside of here !
        $stmt->bindValue('start', self::php2MySqlTime(self::js2PhpTime($start)));
        $stmt->bindValue('end', self::php2MySqlTime(self::js2PhpTime($end)));
        $stmt->bindValue('id', $this->getId());
        $res = $stmt->execute();

        return $res;
    }
}
