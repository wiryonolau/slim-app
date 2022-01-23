<?php
declare(strict_types=1);

namespace Itseasy\Model;

use DateTime;
use DateTimeZone;
use Exception;

class RecordModel extends AbstractModel
{
    protected static $defaultTimezone = "UTC";
    private $_timezone;

    protected $tech_creation_date;
    protected $tech_modification_date;

    public function setTimezone(string $timezone) : self
    {
        $this->_timezone = new DateTimeZone($timezone);
        return $this;
    }

    public function getTimezone() : DateTimeZone
    {
        if (is_null($this->_timezone)) {
            $this->setTimezone(self::$defaultTimezone);
        }
        return $this->_timezone;
    }

    public function setTechCreationDate($date = null) : void
    {
        $this->tech_creation_date = $this->dateToObject(
            $date,
            $this->getTimezone()
        );
    }

    public function getTechCreationDate(
        bool $as_object = false,
        string $format = "Y-m-d H:i:s",
        string $timezone="UTC"
    ) {
        if (is_null($this->tech_creation_date)) {
            $this->setTechCreationDate();
        }
        return $this->formatDate(
            $this->tech_creation_date,
            $as_object,
            $format,
            $timezone
        );
    }

    public function setTechModificationDate($date = null) : void
    {
        $this->tech_modification_date = $this->dateToObject(
            $date,
            $this->getTimezone()
        );
    }

    public function getTechModificationDate(
        bool $as_object = false,
        string $format = "Y-m-d H:i:s",
        string $timezone ="UTC"
    ) {
        if (is_null($this->tech_modification_date)) {
            $this->setTechModificationDate();
        }

        return $this->formatDate(
            $this->tech_modification_date,
            $as_object,
            $format,
            $timezone
        );
    }
}
