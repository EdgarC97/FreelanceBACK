<?php

namespace Controllers;

use App\User;
use Database\Database;
use Firebase\JWT\JWT;

class AuthController {
    private $db;
    private $user;
    private $jwt_config;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->user = new User($this->db);
        $this->jwt_config = require __DIR__ . '/../config/jwt.php';
    }

    public function register() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->name, $data->email, $data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Faltan campos requeridos"]);
            return;
        }

        $existingUser = $this->user->findByEmail($data->email);
        if ($existingUser) {
            http_response_code(409);
            echo json_encode(["message" => "Email ya registrado"]);
            return;
        }

        $this->user->name = $data->name;
        $this->user->email = $data->email;
        $this->user->password = password_hash($data->password, PASSWORD_DEFAULT);

        if ($this->user->register()) {
            echo json_encode(["message" => "Usuario registrado correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al registrar usuario"]);
        }
    }

    public function login() {
        $data = json_decode(file_get_contents("php://input"));

        if (!isset($data->email, $data->password)) {
            http_response_code(400);
            echo json_encode(["message" => "Faltan campos"]);
            return;
        }

        $user = $this->user->findByEmail($data->email);

        if (!$user || !password_verify($data->password, $user['password'])) {
            http_response_code(401);
            echo json_encode(["message" => "Credenciales inválidas"]);
            return;
        }

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

        $jwt = JWT::encode($payload, $this->jwt_config['secret_key'], 'HS256');

        echo json_encode([
            "message" => "Login exitoso",
            "token" => $jwt,
            "user" => $payload['data']
        ]);
    }
}
