<?php

class User {
    private $connection;

    public $id;
    public $name;
    public $email;
    public $password;

    public function __construct($db) {
        $this->connection = $db;
    }

    public function register() {
    }

    public function login() {
    }
}