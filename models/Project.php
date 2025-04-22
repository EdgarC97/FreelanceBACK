<?php

namespace App;

class Project {
    private $connection;

    public $id;
    public $title;
    public $description;
    public $start_date;
    public $delivery_date;
    public $status;
    public $user_id;

    public function __construct($db) {
        $this->connection = $db;
    }

    public function create() {
        $query = "INSERT INTO projects (title, description, start_date, delivery_date, status, user_id)
                  VALUES (:title, :description, :start_date, :delivery_date, :status, :user_id)";
        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":delivery_date", $this->delivery_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    public function read($user_id) {
        $query = "SELECT * FROM projects WHERE user_id = :user_id ORDER BY start_date DESC";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public function readOne($id, $user_id) {
        $query = "SELECT * FROM projects WHERE id = :id AND user_id = :user_id LIMIT 1";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);
        $stmt->execute();
        return $stmt->fetch();
    }

    public function update() {
        $query = "UPDATE projects SET title = :title, description = :description, start_date = :start_date,
                  delivery_date = :delivery_date, status = :status WHERE id = :id AND user_id = :user_id";

        $stmt = $this->connection->prepare($query);

        $stmt->bindParam(":title", $this->title);
        $stmt->bindParam(":description", $this->description);
        $stmt->bindParam(":start_date", $this->start_date);
        $stmt->bindParam(":delivery_date", $this->delivery_date);
        $stmt->bindParam(":status", $this->status);
        $stmt->bindParam(":id", $this->id);
        $stmt->bindParam(":user_id", $this->user_id);

        return $stmt->execute();
    }

    public function delete($id, $user_id) {
        $query = "DELETE FROM projects WHERE id = :id AND user_id = :user_id";
        $stmt = $this->connection->prepare($query);
        $stmt->bindParam(":id", $id);
        $stmt->bindParam(":user_id", $user_id);

        return $stmt->execute();
    }
}
