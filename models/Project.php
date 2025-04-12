<?php

class Project {
    private $connection;

    public $id;
    public $title;
    public $description;
    public $start_date;
    public $delivery_date;
    public $status;
    public $user_id;

    public function __construct($db) {
        $this->connection = $db;
    }

    public function create() {
    }

    public function read($user_id) {
    }

    public function readOne($id, $user_id) {
    }

    public function update() {
    }

    public function delete($id, $user_id) {
    }
}