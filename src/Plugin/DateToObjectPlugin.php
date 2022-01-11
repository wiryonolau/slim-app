<?php

declare(strict_types=1);

namespace Itseasy\Plugin;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;

class DateToObjectPlugin implements PluginInterface
{
    public static $name = "dateToObject";

    public function getName() : string
    {
        return self::$name;
    }

    public function __invoke(
        $date = null,
        $timezone = "UTC",
        string $format="Y-m-d H:i:s",
        bool $immutable = false
    ) : DateTimeInterface {
        $dateClass = ($immutable ? DateTimeImmutable::class : DateTime::class);

        if (is_string($timezone)) {
            $timezone = new DateTimeZone($timezone);
        }

        if (is_null($date)) {
            $date = new $dateClass("now", $timezone);
        } else {
            $date = $dateClass::createFromFormat($format, $date, $timezone);
        }

        if (!$date instanceof DateTimeInterface) {
            throw new Exception("Invalid date format given");
        }

        return $date;
    }
}
