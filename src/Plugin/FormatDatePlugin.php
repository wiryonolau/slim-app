<?php

declare(strict_types=1);

namespace Itseasy\Plugin;

use DateTimeInterface;
use DateTimeZone;

class FormatDatePlugin implements PluginInterface
{
    public static $name = "formatDate";

    public function getName() : string
    {
        return self::$name;
    }

    public function __invoke(
        ?DateTimeInterface $date,
        bool $as_object = false,
        string $format="Y-m-d H:i:s",
        string $timezone = "UTC"
    ) {
        if (is_null($date)) {
            return $date;
        }

        if ($as_object) {
            return $date;
        }

        if ($timezone != $date->getTimezone()->getName()) {
            $date->setTimezone(new DateTimeZone($timezone));
        }

        return $date->format($format);
    }
}
