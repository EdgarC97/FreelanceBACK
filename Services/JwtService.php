<?php

namespace Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService {
    private $config;

    public function __construct() {
        $this->config = [
            "secret_key" => $_ENV['JWT_SECRET'],
            "issuer" => $_ENV['JWT_ISSUER'],
            "audience" => $_ENV['JWT_AUDIENCE'],
            "expiration" => time() + intval($_ENV['JWT_EXPIRES_IN'])
        ];
    }

    /**
     * Generates a JWT token based on user data
     *
     * @param array $user
     * @return string
     */
    public function createToken(array $user): string {
        $payload = [
            "iss" => $this->config['issuer'],
            "aud" => $this->config['audience'],
            "iat" => time(),
            "exp" => $this->config['expiration'],
            "data" => [
                "id" => $user['id'],
                "name" => $user['name'],
                "email" => $user['email']
            ]
        ];

        return JWT::encode($payload, $this->config['secret_key'], 'HS256');
    }

    /**
     * Decodes a JWT token
     *
     * @param string $token
     * @return object
     */
    public function decodeToken(string $token) {
        return JWT::decode($token, new Key($this->config['secret_key'], 'HS256'));
    }

    /**
     * Extracts and validates the user ID from the Authorization header
     *
     * @return int
     */
    public function extractUserId(): int {
        $headers = apache_request_headers();

        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["message" => "Token not provided"]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);

        try {
            $decoded = $this->decodeToken($token);
            return $decoded->data->id;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid token"]);
            exit;
        }
    }
}
