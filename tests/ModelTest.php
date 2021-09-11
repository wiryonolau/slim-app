<?php
namespace Itseasy\Test;

use PHPUnit\Framework\TestCase;

final class ModelTest extends TestCase {
    public function testBaseModel() {
        $base = new Model\TestModel();
        $this->assertEquals(is_array($base->getArrayCopy()), true);
    }

    public function testRecordModel() {
        $record = new Model\TestRecordModel(true);
        $array = $record->getArrayCopy();

        $this->assertEquals($array["tech_creation_date"], $record->getTechCreationDate());
        $this->assertEquals($array["tech_modification_date"], $record->getTechModificationDate());
    }

    public function testCollectionModel() {
        $collection = new Model\TestCollectionModel();
        $collection->setObject(Model\TestModel::class);

        $collection->append(new Model\TestModel());
        $collection->append(new Model\TestModel());
        $this->assertEquals($collection->count(), 2);
        foreach ($collection as $data) {
            $this->assertEquals(($data instanceof Model\TestModel), true);
        }
    }

    public function testComplexModel() {
        $complex = new Model\TestComplexModel();
        $complex->addData(new Model\TestModel());

        $this->assertEquals($complex->data->count(), 1);
        $array = $complex->getArrayCopy();
        $this->assertEquals($array["tech_creation_date"], $complex->getTechCreationDate());
        $this->assertEquals($array["tech_modification_date"], $complex->getTechModificationDate());
    }
}
