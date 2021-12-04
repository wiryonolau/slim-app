<?php

namespace Itseasy\Test\Model;

use Itseasy\Model\RecordModel;
use Itseasy\Model\CollectionModel;

class TestComplexModel extends RecordModel {
    protected $id;
    protected $name;
    protected $data;
    protected $attrs;

    public function __construct() {
        $this->data = new CollectionModel();
        $this->attrs = new CollectionModel(AttrModel::class);
    }

    public function addData($data) {
        $this->data->append($data);
    }
}
