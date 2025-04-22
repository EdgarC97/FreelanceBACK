<?php

require_once __DIR__ . '/vendor/autoload.php';

use Controllers\AuthController;
use Controllers\ProjectController;
use Controllers\FileController;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$request_method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

$controller = null;
$action = null;
$param = null;

if (isset($uri[0])) {
    switch ($uri[0]) {
        case 'register':
            $controller = new AuthController();
            $action = 'register';
            break;

        case 'login':
            $controller = new AuthController();
            $action = 'login';
            break;

        case 'projects':
            $controller = new ProjectController();
            if (isset($uri[1])) {
                $action = 'readOne';
                $param = $uri[1];
            } else {
                $action = 'read';
            }
            break;

        case 'project':
            $controller = new ProjectController();
            if ($request_method === 'POST') {
                $action = 'create';
            } elseif ($request_method === 'PUT') {
                $action = 'update';
            } elseif ($request_method === 'DELETE' && isset($uri[1])) {
                $action = 'delete';
                $param = $uri[1];
            }
            break;

        case 'files':
            $controller = new FileController();
            if (isset($uri[1])) {
                $action = 'read';
                $param = $uri[1]; // project_id
            }
            break;

        case 'file':
            $controller = new FileController();
            if ($request_method === 'POST' && isset($uri[1])) {
                $action = 'upload';
                $param = $uri[1]; // project_id
            } elseif ($request_method === 'GET' && isset($uri[1])) {
                $action = 'download';
                $param = $uri[1]; // file_id
            } elseif ($request_method === 'DELETE' && isset($uri[1])) {
                $action = 'delete';
                $param = $uri[1]; // file_id
            }
            break;
    }
}

if ($controller && $action) {
    if ($param !== null) {
        $controller->$action($param);
    } else {
        $controller->$action();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid route"]);
}
