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
        $database = new Database();
        $this->db = $database->getConnection();
        $this->file = new File($this->db);
        $this->jwt_config = require __DIR__ . '/../config/jwt.php';
    }

    private function verifyToken() {
        $headers = apache_request_headers();
        if (!isset($headers['Authorization'])) {
            http_response_code(401);
            echo json_encode(["message" => "Token no proporcionado"]);
            exit;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        try {
            $decoded = JWT::decode($token, new Key($this->jwt_config['secret_key'], 'HS256'));
            return $decoded->data->id;
        } catch (\Exception $e) {
            http_response_code(401);
            echo json_encode(["message" => "Token inválido"]);
            exit;
        }
    }

    public function upload($project_id) {
        $this->verifyToken();

        if (!isset($_FILES['file'])) {
            http_response_code(400);
            echo json_encode(["message" => "Archivo no enviado"]);
            return;
        }

        $file = $_FILES['file'];
        $allowed_types = ['application/pdf', 'image/jpeg', 'image/png', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];

        if (!in_array($file['type'], $allowed_types)) {
            http_response_code(400);
            echo json_encode(["message" => "Tipo de archivo no permitido"]);
            return;
        }

        $upload_dir = __DIR__ . '/../uploads/';
        if (!is_dir($upload_dir)) mkdir($upload_dir);

        $filename = uniqid() . '_' . basename($file['name']);
        $filepath = $upload_dir . $filename;

        if (move_uploaded_file($file['tmp_name'], $filepath)) {
            $this->file->save($file['name'], $filename, $file['type'], $project_id);
            echo json_encode(["message" => "Archivo subido correctamente"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al subir archivo"]);
        }
    }

    public function read($project_id) {
        $this->verifyToken();
        $files = $this->file->getAllByProject($project_id);
        echo json_encode($files);
    }

    public function download($file_id) {
        $this->verifyToken();
        $file = $this->file->getById($file_id);

        if (!$file) {
            http_response_code(404);
            echo json_encode(["message" => "Archivo no encontrado"]);
            return;
        }

        $path = __DIR__ . '/../uploads/' . $file['file_path'];
        if (!file_exists($path)) {
            http_response_code(404);
            echo json_encode(["message" => "Archivo físico no encontrado"]);
            return;
        }

        header('Content-Type: ' . $file['mime_type']);
        header('Content-Disposition: attachment; filename="' . $file['file_name'] . '"');
        readfile($path);
        exit;
    }

    public function delete($file_id) {
        $this->verifyToken();
        $file = $this->file->getById($file_id);

        if (!$file) {
            http_response_code(404);
            echo json_encode(["message" => "Archivo no encontrado"]);
            return;
        }

        $path = __DIR__ . '/../uploads/' . $file['file_path'];
        if (file_exists($path)) unlink($path);

        if ($this->file->delete($file_id)) {
            echo json_encode(["message" => "Archivo eliminado"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al eliminar"]);
        }
    }
}
