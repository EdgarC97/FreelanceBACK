<?php

namespace Controllers;

use App\User;
use Database\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthController {
    private $db;
    private $user;
    private $jwt_config;

    public function __construct() {
        // Initialize DB connection and user model
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);

        // Load JWT configuration from .env
        $this->jwt_config = [
            "secret_key" => $_ENV['JWT_SECRET'],
            "issuer" => $_ENV['JWT_ISSUER'],
            "audience" => $_ENV['JWT_AUDIENCE'],
            "issued_at" => time(),
            "expiration_time" => time() + intval($_ENV['JWT_EXPIRES_IN'])
        ];
    }

    public function register() {
        // Get JSON input
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if (!isset($data->name, $data->email, $data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing required fields"]);
            return;
        }

        // Check if user already exists
        $existingUser = $this->user->findByEmail($data->email);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(["message" => "Email already registered"]);
            return;
        }

        // Assign data and hash password
        $this->user->name = $data->name;
        $this->user->email = $data->email;
        $this->user->password = password_hash($data->password, PASSWORD_DEFAULT);

        // Attempt to register
        if ($this->user->register()) {
            echo json_encode(["message" => "User registered successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "User registration failed"]);
        }
    }

    public function login() {
        // Get JSON input
        $data = json_decode(file_get_contents("php://input"));

        // Validate required fields
        if (!isset($data->email, $data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Missing email or password"]);
            return;
        }

        // Find user by email
        $user = $this->user->findByEmail($data->email);

        // Check if user exists and password is correct
        if (!$user || !password_verify($data->password, $user['password'])) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid credentials"]);
            return;
        }

        // Prepare JWT payload
        $payload = [
            "iss" => $this->jwt_config['issuer'],
            "aud" => $this->jwt_config['audience'],
            "iat" => $this->jwt_config['issued_at'],
            "exp" => $this->jwt_config['expiration_time'],
            "data" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ];

        // Encode and return the token
        $jwt = JWT::encode($payload, $this->jwt_config['secret_key'], 'HS256');

        echo json_encode([
            "message" => "Login successful",
            "token" => $jwt,
            "user" => $payload['data']
        ]);
    }
}
