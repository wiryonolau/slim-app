<?php

namespace Itseasy\Model;

use Laminas\Stdlib\ArraySerializableInterface;
use ArrayObject;
use Exception;

class CollectionModel extends ArrayObject implements ArraySerializableInterface
{
    protected $object = null;

    public function setObject(string $object)
    {
        if (!class_exists($object)) {
            throw Exception("Class not exist");
        }
        $this->object = $object;
    }

    public function append($item) : void
    {
        if (is_array($item)) {
            array_map([$this, "appendFilter"], $item);
        } else {
            $this->appendFilter($item);
        }
    }

    public function getArrayCopy() : array
    {
        $result = [];
        foreach ($this as $data) {
            if ($data instanceof ArraySerializableInterface) {
                $result[] = $data->getArrayCopy();
            } else {
                $result[] = $data;
            }
        }
        return $result;
    }

    private function appendFilter($item) : void
    {
        if ($this->isValid($item)) {
            parent::append($item);
        }
    }

    private function isValid($item) : bool
    {
        if (is_null($this->object)) {
            return true;
        }
        return is_a($item, $this->object);
    }
}
