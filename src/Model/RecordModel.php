<?php

namespace Itseasy\Model;

use DateTime;
use DateTimeZone;
use Exception;

class RecordModel extends AbstractModel
{
    protected $tech_timezone;
    protected $tech_creation_date;
    protected $tech_modification_date;

    public function __construct(bool $init = true, string $timezone = "UTC")
    {
        $this->tech_timezone = new DateTimeZone($timezone);
        if ($init) {
            $this->initTechDate();
        }
    }

    public function initTechDate()
    {
        $this->setTechCreationDate();
        $this->setTechModificationDate();
    }

    public function setTechTimezone(string $timezone) : self
    {
        $this->tech_timezone = new DateTimeZone($timezone);
        return $this;
    }

    public function setTechCreationDate($date = null) : void
    {
        if (!$date instanceof DateTime) {
            if (is_null($date)) {
                $date = new DateTime("now", $this->tech_timezone);
            } else {
                $date = DateTime::createFromFormat("Y-m-d H:i:s", $date, $this->tech_timezone);
            }
        }
        $this->tech_creation_date = $date;
    }

    public function getTechCreationDate(bool $as_object = false, string $format = "Y-m-d H:i:s", string $timezone="UTC")
    {
        if ($timezone != $this->tech_creation_date->getTimezone()->getName()) {
            $this->tech_creation_date->setTimezone(new DateTimeZone($timezone));
        }

        if ($as_object) {
            return $this->tech_creation_date;
        }

        return $this->tech_creation_date->format($format);
    }

    public function setTechModificationDate($date = null) : void
    {
        if (!$date instanceof DateTime) {
            if (is_null($date)) {
                $date = new DateTime("now", $this->tech_timezone);
            } else {
                $date = DateTime::createFromFormat("Y-m-d H:i:s", $date, $this->tech_timezone);
            }
        }
        $this->tech_modification_date = $date;
    }

    public function getTechModificationDate(bool $as_object = false, string $format = "Y-m-d H:i:s", string $timezone="UTC")
    {
        if ($timezone != $this->tech_modification_date->getTimezone()->getName()) {
            $this->tech_modification_date->setTimezone(new DateTimeZone($timezone));
        }

        if ($as_object) {
            return $this->tech_modification_date;
        }

        return $this->tech_modification_date->format($format);
    }

    public function getArrayCopy($exclude_timezone = true) : array
    {
        $result = parent::getArrayCopy();
        
        if ($exclude_timezone) {
            unset($result["tech_timezone"]);
        }
        $result["tech_creation_date"] = $this->getTechCreationDate();
        $result["tech_modification_date"] = $this->getTechModificationDate();
        return $result;
    }
}
