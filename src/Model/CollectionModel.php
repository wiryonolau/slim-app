<?php

namespace Itseasy\Model;

use ArrayIterator;
use ArrayObject;
use Exception;
use Laminas\Stdlib\ArraySerializableInterface;

class CollectionModel extends ArrayObject implements ArraySerializableInterface
{
    private $collectionObjectPrototype = null;

    public function __construct(
        $collectionObjectPrototype = null,
        array $data = [],
        int $flags = 0,
        string $iteratorClass = ArrayIterator::class
    ) {
        parent::__construct([], $flags, $iteratorClass);

        if (!is_null($object)) {
            $this->setCollectionObject($object);
        }

        foreach ($data as $d) {
            $this->append($d);
        }
    }

    public function setCollectionObjectPrototype($object)
    {
        if (is_string($object) and class_exists($object)) {
            $object = new $object;
        } else {
            throw new Exception("Class not exist");
        }

        $this->collectionObjectPrototype = $object;
    }

    public function getCollectionObjectPrototype() : string
    {
        return $this->collectionObjectPrototype;
    }


    public function append($item) : void
    {
        if (is_null($this->collectionObjectPrototype)) {
            parent::append($item);
        } else {
            if (is_array($item)) {
                $obj = clone $this->collectionObjectPrototype;
                $obj->populate($item);
                parent::append($obj);
            } elseif ($item instanceof get_class($this->collectionObjectPrototype))) {
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
