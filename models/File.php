<?php

namespace App;

class File {
    private $connection;

    public function __construct($db) {
        $this->connection = $db;
    }

    public function save($name, $path, $mime, $project_id) {
        $query = "INSERT INTO files (file_name, file_path, mime_type, project_id)
                  VALUES (:name, :path, :mime, :project_id)";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":name", $name);
        $stmt->bindParam(":path", $path);
        $stmt->bindParam(":mime", $mime);
        $stmt->bindParam(":project_id", $project_id);
        return $stmt->execute();
    }

    public function getAllByProject($project_id) {
        $query = "SELECT * FROM files WHERE project_id = :project_id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":project_id", $project_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function getById($id) {
        $query = "SELECT * FROM files WHERE id = :id LIMIT 1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function delete($id) {
        $query = "DELETE FROM files WHERE id = :id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":id", $id);
        return $stmt->execute();
    }
}
