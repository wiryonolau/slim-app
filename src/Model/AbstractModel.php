<?php

namespace Itseasy\Model;

use Exception;
use Laminas\Stdlib\ArraySerializableInterface;
use ReflectionClass;

abstract class AbstractModel implements ArraySerializableInterface
{
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
        throw new Exception("Cannot add new property \$$name to instance of " . __CLASS__);
    }

    public function populate(array $data) : void
    {
        foreach ($data as $k => $v) {
            if (!property_exists($this, $k)) {
                continue;
            }

            $method = $this->getPropertyClassMethod("set", $k);
            if (!is_null($method)) {
                $this->{$method}($v);
            } elseif ($this->{$k} instanceof ArraySerializableInterface) {
                $this->{$k}->populate($v);
            } elseif ($this->isCallable($this->{$k}, "populate")) {
                $this->{$k}->populate($v);
            } else {
                $this->{$k} = $v;
            }
        }
    }

    public function exchangeArray(array $data) : void
    {
        $this->populate($data);
    }

    public function getArrayCopy() : array
    {
        $result = [];
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            $method = $this->getPropertyClassMethod("get", $property->name);
            if (!is_null($method)) {
                $result[$property->name] = $this->{$method}();
            } elseif ($this->{$property->name} instanceof ArraySerializableInterface) {
                $result[$property->name] = $this->{$property->name}->getArrayCopy();
            } elseif ($this->isCallable($this->{$property->name}, "getArrayCopy")) {
                $result[$property->name] = $this->{$property->name}->getArrayCopy();
            } else {
                $result[$property->name] = $this->{$property->name};
            }
        }
        return $result;
    }

    protected function isCallable($object, ?string $function) : bool
    {
        if (is_null($function)) {
            return false;
        }

        return (method_exists($object, $function) and is_callable([$object, $function]));
    }

    private function getPropertyClassMethod($type = "get", $property, $throw_error = true) : ?string
    {
        $method = sprintf("%s%s", $type, implode('', array_map('ucfirst', explode('_', $property))));
        if ($this->isCallable($this, $method)) {
            return $method;
        }
        return null;
    }
}
