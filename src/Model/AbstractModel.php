<?php

namespace Itseasy\Model;

use Laminas\Stdlib\ArraySerializableInterface;
use ReflectionClass;
use Exception;

class AbstractModel
{
    public function __get(string $name)
    {
        $method = $this->getPropertyClassMethod("get", $name);
        if (is_null($method)) {
            return $this->{$name};
        }
        return $this->{$method}();
    }

    public function __set(string $name, $value)
    {
        throw new Exception("Cannot add new property \$$name to instance of " . __CLASS__);
    }

    public function populate(array $data)
    {
        foreach ($data as $k => $v) {
            if (!property_exists($this, $k)) {
                continue;
            }

            $method = $this->getPropertyClassMethod("set", $k);
            if (is_null($method)) {
                $this->{$k} = $v;
            } else {
                $this->{$method}($v);
            }
        }
    }

    public function getArrayCopy() : array
    {
        $result = [];
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            if ($this->{$property->name} instanceof AbstractModel) {
                $result[$property->name] = $this->{$property->name}->getArrayCopy();
            } else {
                $method = $this->getPropertyClassMethod("get", $property->name);
                if (is_null($method)) {
                    $result[$property->name] = $this->{$property->name};
                } else {
                    $result[$property->name] = $this->{$method}();
                }
            }
        }
        return $result;
    }

    private function getPropertyClassMethod($type = "get", $property, $throw_error = true) : ?string
    {
        $method = sprintf("%s%s", $type, implode('', array_map('ucfirst', explode('_', $property))));
        if (method_exists($this, $method)) {
            return $method;
        }
        return null;
    }
}
