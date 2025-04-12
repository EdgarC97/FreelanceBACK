<?php
class AuthController {
    private $db;
    private $user;

    public function __construct() {
        
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
    }

    public function register() {
    }

    public function login() {
    }
}