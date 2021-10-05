<?php

namespace Itseasy\Model;

use Laminas\Stdlib\ArraySerializableInterface;
use ReflectionClass;

class AbstractModel implements ArraySerializableInterface
{
    public function __get(string $name)
    {
        $method = $this->getPropertyClassMethod("get", $name);
        if (is_null($method)) {
            return $this->{$name};
        }
        return $this->{$method}();
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

    public function exchangeArray(array $data)
    {
        $this->populate($data);
    }

    public function getArrayCopy() : array
    {
        $result = [];
        $reflection = new ReflectionClass($this);
        foreach ($reflection->getProperties() as $property) {
            if ($this->{$property->name} instanceof ArraySerializableInterface) {
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
