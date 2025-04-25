<?php

namespace Controllers;

use App\User;
use Database\Database;
use Services\JwtService;

class AuthController {
    private $db;
    private $user;
    private $jwtService;

    public function __construct() {
        // Initialize DB connection and user model
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->jwtService = new JwtService();
    }

    /**
     * Handles user registration
     */
    public function register() {
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if (!isset($data->name, $data->email, $data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            return;
        }

        // Check for existing user
        $existingUser = $this->user->findByEmail($data->email);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(["message" => "Email already registered"]);
            return;
        }

        // Set and hash user password
        $this->user->name = $data->name;
        $this->user->email = $data->email;
        $this->user->password = password_hash($data->password, PASSWORD_DEFAULT);

        if ($this->user->register()) {
            echo json_encode(["message" => "User registered successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "User registration failed"]);
        }
    }

    /**
     * Handles user login and returns JWT
     */
    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if (!isset($data->email, $data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing email or password"]);
            return;
        }

        $user = $this->user->findByEmail($data->email);

        // Validate credentials
        if (!$user || !password_verify($data->password, $user['password'])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid credentials"]);
            return;
        }

        // Create JWT token
        $token = $this->jwtService->createToken($user);

        echo json_encode([
            "message" => "Login successful",
            "token" => $token,
            "user" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ]);
    }
}