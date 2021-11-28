<?php

namespace Itseasy\Model;

use ArrayIterator;
use ArrayObject;
use Exception;
use Laminas\Stdlib\ArraySerializableInterface;

class CollectionModel extends ArrayObject implements ArraySerializableInterface
{
    private $objectPrototype = null;

    public function __construct(
        $objectPrototype = null,
        array $data = [],
        int $flags = 0,
        string $iteratorClass = ArrayIterator::class
    ) {
        parent::__construct([], $flags, $iteratorClass);

        if (!is_null($objectPrototype)) {
            $this->setObjectPrototype($objectPrototype);
        }

        foreach ($data as $d) {
            $this->append($d);
        }
    }

    public function setObjectPrototype($object)
    {
        if (is_string($object) and class_exists($object)) {
            $object = new $object;
        } else {
            throw new Exception("Class not exist");
        }

        $this->objectPrototype = $object;
    }

    /*
     * return object
     */
    public function getObjectPrototype()
    {
        return $this->objectPrototype;
    }


    public function append($item) : void
    {
        if (is_null($this->getObjectPrototype())) {
            parent::append($item);
        } else if (is_array($item)) {
            $obj = clone $this->getObjectPrototype();
            $obj->populate($item);
            parent::append($obj);
        } else {
            $instance = get_class($this->getObjectPrototype());
            if ($item instanceof $instance) {
                parent::append($item);
            }
        }
    }

    public function populate(array $data) : void
    {
        foreach ($data as $row) {
            $this->append($data);
        }
    }

    public function exchangeArray($data) : array
    {
        $old = $this->getArrayCopy();
        $this->populate($data);
        return $old;
    }

    public function getArrayCopy() : array
    {
        $result = [];
        foreach ($this as $data) {
            if ($data instanceof ArraySerializableInterface) {
                $result[] = $data->getArrayCopy();
            } elseif (method_exists($data, "getArrayCopy") and is_callable([$data, "getArrayCopy"])) {
                $result[] = $data->getArrayCopy();
            } else {
                $result[] = $data;
            }
        }
        return $result;
    }
}
