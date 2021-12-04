<?php

namespace Itseasy\Test\Model;

use Itseasy\Model\RecordModel;
use Itseasy\Model\CollectionModel;

class TestRecordModel extends RecordModel {
    protected $id;
    protected $data;
    protected $record;
    protected $attribute;

    public function setRecord($record)
    {
        $this->record = $this->dateToObject($record, $this->getTimezone(), "Y-m-d");
    }

    public function getRecord(bool $as_object = false, string $format = "Y-m-d", string $timezone="UTC")
    {
        return $this->formatDate($this->record, $as_object, $format, $timezone);
    }

    public function setAttribute($attr)
    {
        $this->attribute = $this->jsonToObject($attr, new CollectionModel(new TestModel()));
    }

    /**
     * getArrayCopy will call getAttribute with default argument
     * when call by getArrayCopy getAttribute must return the object as is
     * override getArrayCopy to return the object as array
     */
    public function getAttribute($as_json = false)
    {
        return $this->objectToJson($this->attribute, $as_json);
    }

    public function getArrayCopy(): array
    {
        $array = parent::getArrayCopy();
        $array["attribute"] = $this->attribute->getArrayCopy();
        return $array;
    }
}
