<?php
declare(strict_types=1);

namespace Itseasy\Model;

use DateTime;
use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use Exception;
use Laminas\Stdlib\ArraySerializableInterface;
use ReflectionClass;
use ReflectionProperty;
use Throwable;

abstract class AbstractModel implements ArraySerializableInterface
{
    // Model Properties
    private $modelProperties = [];

    public function __get(string $name)
    {
        $method = $this->getPropertyClassMethod("get", $name);
        if (is_null($method)) {
            return $this->{$name};
        }
        return $this->{$method}();
    }

    public function __set(string $name, $value) : void
    {
        if (empty($this->getModelProperties()[$name])) {
            throw new Exception("\$$name is not valid property in ".get_class($this));
        }

        $this->populate([
            $name => $value
        ]);
    }

    public function populate(array $data) : void
    {
        foreach ($data as $k => $v) {
            if (empty($this->getModelProperties()[$k])) {
                continue;
            }

            $method = $this->getPropertyClassMethod("set", $k);
            if (!is_null($method)) {
                $this->{$method}($v);
            } elseif ($this->isCallable($this->{$k}, "populate")) {
                $this->{$k}->populate($v);
            } elseif ($this->{$k} instanceof ArraySerializableInterface) {
                $this->{$k}->exchangeArray($v);
            } else {
                $this->{$k} = $v;
            }
        }
    }

    public function exchangeArray(array $data) : array
    {
        $old = $this->getArrayCopy();
        $this->populate($data);
        return $old;
    }

    public function getArrayCopy() : array
    {
        $result = [];
        foreach ($this->getModelProperties() as $property => $scope) {
            $method = $this->getPropertyClassMethod("get", $property);
            if (!is_null($method)) {
                $result[$property] = $this->{$method}();
            } elseif ($this->isCallable($this->{$property}, "getArrayCopy")) {
                $result[$property] = $this->{$property}->getArrayCopy();
            } else {
                $result[$property] = $this->{$property};
            }
        }
        return $result;
    }

    public function dateToObject(
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

    public function formatDate(
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


    public function jsonToObject(
        $json,
        $objectPrototype = null,
        int $depth = 512,
        int $flags = 0
    ) {
        $flags |= JSON_THROW_ON_ERROR;

        if (is_array($json)) {
            return $json;
        }

        $json = json_decode($json, true, $depth, $flags);

        if (is_null($objectPrototype)) {
            return $json;
        }

        if (is_string($objectPrototype) and class_exists($objectPrototype)) {
            $objectPrototype = new $objectPrototype;
        }

        $obj = clone $objectPrototype;
        if ($this->isCallable($objectPrototype, "populate")) {
            $obj->populate($json);
            return $obj;
        }

        if ($objectPrototype instanceof ArraySerializableInterface) {
            $obj->exchangeArray($json);
            return $obj;
        }

        throw new Exception("objecProperty must implement ArraySerializableInterface or at least has populate function");
    }

    public function objectToJson(
        $object,
        bool $as_json = true,
        int $flags = 0,
        int $depth = 512
    ) {
        $flags |= JSON_THROW_ON_ERROR;

        if (is_null($object)) {
            return "";
        }

        if (!$as_json) {
            return $object;
        }

        if (is_array($object)) {
            return json_encode($object, $flags, $depth);
        }

        if ($this->isCallable($object, "getArrayCopy")) {
            $value = $object->getArrayCopy();
            return json_encode($value);
        }

        throw new Exception("Object must be an array or at least has getArrayCopy function");
    }

    public function toJson(int $flags = 0, int $depth = 512) : string
    {
        $flags |= JSON_THROW_ON_ERROR;

        return json_encode($this->getArrayCopy(), $flags, $depth);
    }

    public function isCallable($object, ?string $function) : bool
    {
        if (is_null($function)) {
            return false;
        }

        return (method_exists($object, $function) and is_callable([$object, $function]));
    }

    /**
     * Retrieve and cache all child Model property
     * Only non static public and non static protected count as model attribute
     */
    private function getModelProperties() : array
    {
        if (!count($this->modelProperties)) {
            $reflection = new ReflectionClass($this);

            foreach($reflection->getProperties() as $property) {
                if(!($property->isPublic() or $property->isProtected())) {
                    continue;
                }

                if ($property->isStatic()) {
                    continue;
                }

                $this->modelProperties[$property->name] = ($property->isPublic() ? "public" : "protected");
            }
        }
        return $this->modelProperties;
    }

    private function getPropertyClassMethod(string $type = "get", string $property, bool $throw_error = true) : ?string
    {
        $method = sprintf("%s%s", $type, implode('', array_map('ucfirst', explode('_', $property))));
        if ($this->isCallable($this, $method)) {
            return $method;
        }
        return null;
    }
}
