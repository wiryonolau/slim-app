<?php

namespace Itseasy\Model;

use ArrayIterator;
use ArrayObject;
use Exception;
use Laminas\Stdlib\ArraySerializableInterface;

class CollectionModel extends ArrayObject implements ArraySerializableInterface
{
    private $collectionObject = null;

    public function __construct(
        ?string $object = null,
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

    public function setCollectionObject(string $object)
    {
        if (!class_exists($object)) {
            throw new Exception("Class not exist");
        }
        $this->collectionObject = $object;
    }

    public function getObject() : string
    {
        return $this->collectionObject;
    }


    public function append($item) : void
    {
        if (is_array($item)) {
            array_map([$this, "appendFilter"], $item);
        } else {
            $this->appendFilter($item);
        }
    }

    public function populate(array $data) : void
    {
        $this->append($data);
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

    private function appendFilter($item) : void
    {
        if (is_null($this->collectionObject)) {
            parent::append($item);
        } else {
            if (is_array($item)) {
                $obj = new $this->collectionObject;
                $obj->populate($item);
                parent::append($item);
            } elseif (is_a($item, $this->collectionObject)) {
                parent::append($item);
            }
        }
    }
}
