<?php
class ProjectController {
    private $db;
    private $project;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->project = new Project($this->db);
    }

    private function verifyToken() {
    }

    public function create() {
    }

    public function read() {
    }

    public function readOne($id) {
    }

    public function update() {
    }

    public function delete($id) {
    }
}