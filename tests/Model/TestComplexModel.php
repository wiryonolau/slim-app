<?php

namespace Itseasy\Test\Model;

use Itseasy\Model\RecordModel;
use Itseasy\Model\CollectionModel;

class TestComplexModel extends RecordModel {
    protected $id;
    protected $name;
    protected $data;

    public function __construct() {
        $this->initTechDate();
        $this->data = new CollectionModel();
    }

    public function addData($data) {
        $this->data->append($data);
    }

}
