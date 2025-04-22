<?php

namespace Controllers;

use App\File;
use Database\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class FileController {
    private $db;
    private $file;
    private $jwt_config;

    public function __construct() {
        // Initialize DB connection and file model
        $database = new Database();
        $this->db = $database->getConnection();
        $this->file = new File($this->db);

        // Load JWT configuration from .env
        $this->jwt_config = [
            "secret_key" => $_ENV['JWT_SECRET'],
            "issuer" => $_ENV['JWT_ISSUER'],
            "audience" => $_ENV['JWT_AUDIENCE'],
            "issued_at" => time(),
            "expiration_time" => time() + intval($_ENV['JWT_EXPIRES_IN'])
        ];
    }

    private function verifyToken() {
        // Extract and validate JWT token from headers
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["message" => "Token not provided"]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);

        try {
            $decoded = JWT::decode($token, new Key($this->jwt_config['secret_key'], 'HS256'));
            return $decoded->data->id;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Invalid token"]);
            exit;
        }
    }

    public function upload($project_id) {
        $this->verifyToken();

        // Ensure file is provided
        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(["message" => "No file uploaded"]);
            return;
        }

        $file = $_FILES['file'];

        // Allowed MIME types
        $allowed_types = [
            'application/pdf', 'image/jpeg', 'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        // Validate MIME type
        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid file type"]);
            return;
        }

        // Ensure upload directory exists
        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir);

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;

        // Move uploaded file
        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->file->save($file['name'], $filename, $file['type'], $project_id);
            echo json_encode(["message" => "File uploaded successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload file"]);
        }
    }

    public function read($project_id) {
        $this->verifyToken();
        // Get all files for a project
        $files = $this->file->getAllByProject($project_id);
        echo json_encode($files);
    }

    public function download($file_id) {
        $this->verifyToken();

        // Get file by ID
        $file = $this->file->getById($file_id);

        if (!$file) {
            http_response_code(404);
            echo json_encode(["message" => "File not found"]);
            return;
        }

        $path = __DIR__ . '/../uploads/' . $file['file_path'];

        // Ensure file exists on disk
        if (!file_exists($path)) {
            http_response_code(404);
            echo json_encode(["message" => "Physical file not found"]);
            return;
        }

        // Return file for download
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
        readfile($path);
        exit;
    }

    public function delete($file_id) {
        $this->verifyToken();

        // Get file by ID
        $file = $this->file->getById($file_id);

        if (!$file) {
            http_response_code(404);
            echo json_encode(["message" => "File not found"]);
            return;
        }

        $path = __DIR__ . '/../uploads/' . $file['file_path'];

        // Remove file from disk if exists
        if (file_exists($path)) unlink($path);

        // Delete record from DB
        if ($this->file->delete($file_id)) {
            echo json_encode(["message" => "File deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete file"]);
        }
    }
}
