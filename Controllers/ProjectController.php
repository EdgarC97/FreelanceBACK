<?php

namespace Controllers;

use App\Project;
use Database\Database;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ProjectController {
    private $db;
    private $project;
    private $jwt_config;

    public function __construct() {
        $database = new Database();
        $this->db = $database->getConnection();
        $this->project = new Project($this->db);
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

    public function create() {
        $user_id = $this->verifyToken();
        $data = json_decode(file_get_contents("php://input"));

        $this->project->title = $data->title;
        $this->project->description = $data->description;
        $this->project->start_date = $data->start_date;
        $this->project->delivery_date = $data->delivery_date;
        $this->project->status = $data->status;
        $this->project->user_id = $user_id;

        if ($this->project->create()) {
            echo json_encode(["message" => "Proyecto creado"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al crear proyecto"]);
        }
    }

    public function read() {
        $user_id = $this->verifyToken();
        $projects = $this->project->read($user_id);
        echo json_encode($projects);
    }

    public function readOne($id) {
        $user_id = $this->verifyToken();
        $project = $this->project->readOne($id, $user_id);

        if ($project) {
            echo json_encode($project);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Proyecto no encontrado"]);
        }
    }

    public function update() {
        $user_id = $this->verifyToken();
        $data = json_decode(file_get_contents("php://input"));

        $this->project->id = $data->id;
        $this->project->title = $data->title;
        $this->project->description = $data->description;
        $this->project->start_date = $data->start_date;
        $this->project->delivery_date = $data->delivery_date;
        $this->project->status = $data->status;
        $this->project->user_id = $user_id;

        if ($this->project->update()) {
            echo json_encode(["message" => "Proyecto actualizado"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al actualizar"]);
        }
    }

    public function delete($id) {
        $user_id = $this->verifyToken();

        if ($this->project->delete($id, $user_id)) {
            echo json_encode(["message" => "Proyecto eliminado"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Error al eliminar"]);
        }
    }
}
