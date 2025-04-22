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
        // Initialize DB connection and project model
        $database = new Database();
        $this->db = $database->getConnection();
        $this->project = new Project($this->db);

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

    public function create() {
        $user_id = $this->verifyToken();
        $data = json_decode(file_get_contents("php://input"));

        // Assign project data
        $this->project->title = $data->title;
        $this->project->description = $data->description;
        $this->project->start_date = $data->start_date;
        $this->project->delivery_date = $data->delivery_date;
        $this->project->status = $data->status;
        $this->project->user_id = $user_id;

        // Create project in DB
        if ($this->project->create()) {
            echo json_encode(["message" => "Project created"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create project"]);
        }
    }

    public function read() {
        $user_id = $this->verifyToken();
        // Fetch all projects by user
        $projects = $this->project->read($user_id);
        echo json_encode($projects);
    }

    public function readOne($id) {
        $user_id = $this->verifyToken();
        // Fetch one project by ID and user
        $project = $this->project->readOne($id, $user_id);

        if ($project) {
            echo json_encode($project);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Project not found"]);
        }
    }

    public function update() {
        $user_id = $this->verifyToken();
        $data = json_decode(file_get_contents("php://input"));

        // Assign updated values
        $this->project->id = $data->id;
        $this->project->title = $data->title;
        $this->project->description = $data->description;
        $this->project->start_date = $data->start_date;
        $this->project->delivery_date = $data->delivery_date;
        $this->project->status = $data->status;
        $this->project->user_id = $user_id;

        // Update project
        if ($this->project->update()) {
            echo json_encode(["message" => "Project updated"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update project"]);
        }
    }

    public function delete($id) {
        $user_id = $this->verifyToken();

        // Delete project
        if ($this->project->delete($id, $user_id)) {
            echo json_encode(["message" => "Project deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete project"]);
        }
    }
}
