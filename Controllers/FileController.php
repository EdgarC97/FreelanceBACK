<?php

namespace Controllers;

use App\File;
use Database\Database;
use Services\JwtService;

class FileController {
    private $db;
    private $file;
    private $jwtService;

    public function __construct() {
        // Initialize DB connection and file model
        $database = new Database();
        $this->db = $database->getConnection();
        $this->file = new File($this->db);
        $this->jwtService = new JwtService();
    }

    /**
     * Uploads a file to a given project
     */
    public function upload($project_id) {
        $this->jwtService->extractUserId(); // Validate token

        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(["message" => "No file uploaded"]);
            return;
        }

        $file = $_FILES['file'];

        // Allowed file types
        $allowed_types = [
            'application/pdf', 'image/jpeg', 'image/png',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document'
        ];

        // Validate file type
        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(["message" => "Invalid file type"]);
            return;
        }

        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir);

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->file->save($file['name'], $filename, $file['type'], $project_id);
            echo json_encode(["message" => "File uploaded successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload file"]);
        }
    }

    /**
     * Reads all files related to a project
     */
    public function read($project_id) {
        $this->jwtService->extractUserId(); // Validate token
        $files = $this->file->getAllByProject($project_id);
        echo json_encode($files);
    }

    /**
     * Downloads a file by its ID
     */
    public function download($file_id) {
        $this->jwtService->extractUserId(); // Validate token

        $file = $this->file->getById($file_id);

        if (!$file) {
            http_response_code(404);
            echo json_encode(["message" => "File not found"]);
            return;
        }

        $path = __DIR__ . '/../uploads/' . $file['file_path'];

        if (!file_exists($path)) {
            http_response_code(404);
            echo json_encode(["message" => "Physical file not found"]);
            return;
        }

        // Stream file to browser
        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
        readfile($path);
        exit;
    }

    /**
     * Deletes a file by its ID
     */
    public function delete($file_id) {
        $this->jwtService->extractUserId(); // Validate token

        $file = $this->file->getById($file_id);

        if (!$file) {
            http_response_code(404);
            echo json_encode(["message" => "File not found"]);
            return;
        }

        $path = __DIR__ . '/../uploads/' . $file['file_path'];

        if (file_exists($path)) unlink($path);

        if ($this->file->delete($file_id)) {
            echo json_encode(["message" => "File deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete file"]);
        }
    }
}
