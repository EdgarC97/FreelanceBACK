<?php

namespace Controllers;

use App\Project;
use Database\Database;
use Services\JwtService;

class ProjectController {
    private $db;
    private $project;
    private $jwtService;

    public function __construct() {
        // Initialize DB connection and project model
        $database = new Database();
        $this->db = $database->getConnection();
        $this->project = new Project($this->db);
        $this->jwtService = new JwtService();
    }

    public function create() {
        $user_id = $this->jwtService->extractUserId();
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
            $newId = $this->db->lastInsertId();
            echo json_encode([
                "id" => $newId,
                "title" => $this->project->title,
                "description" => $this->project->description,
                "start_date" => $this->project->start_date,
                "delivery_date" => $this->project->delivery_date,
                "status" => $this->project->status,
                "user_id" => $this->project->user_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to create project"]);
        }
    }

    public function read() {
        $user_id = $this->jwtService->extractUserId();
        $projects = $this->project->read($user_id);
        echo json_encode($projects);
    }

    public function readOne($id) {
        $user_id = $this->jwtService->extractUserId();
        $project = $this->project->readOne($id, $user_id);

        if ($project) {
            echo json_encode($project);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "Project not found"]);
        }
    }

    public function update() {
        $user_id = $this->jwtService->extractUserId();
        $data = json_decode(file_get_contents("php://input"));

        $this->project->id = $data->id;
        $this->project->title = $data->title;
        $this->project->description = $data->description;
        $this->project->start_date = $data->start_date;
        $this->project->delivery_date = $data->delivery_date;
        $this->project->status = $data->status;
        $this->project->user_id = $user_id;

        if ($this->project->update()) {
            echo json_encode([
                "message" => "Project updated",
                "id" => $this->project->id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to update project"]);
        }
    }

    public function delete($id) {
        $user_id = $this->jwtService->extractUserId();

        if ($this->project->delete($id, $user_id)) {
            echo json_encode(["message" => "Project deleted"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "Failed to delete project"]);
        }
    }
}
