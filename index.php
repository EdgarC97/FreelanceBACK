<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;

header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

require_once 'controllers/AuthController.php';
require_once 'controllers/ProjectController.php';
// require_once 'controllers/FileController.php';

$request_method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', $uri);

$controller = null;
$action = null;
$param = null;

if(isset($uri[1])) {
    switch($uri[1]) {
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
            if(isset($uri[2])) {
                $action = 'readOne';
                $param = $uri[2];
            } else {
                $action = 'read';
            }
            break;
        case 'project':
            $controller = new ProjectController();
            if($request_method == 'POST') {
                $action = 'create';
            } elseif($request_method == 'PUT') {
                $action = 'update';
            } elseif($request_method == 'DELETE' && isset($uri[2])) {
                $action = 'delete';
                $param = $uri[2];
            }
            break;
        // case 'files':
        //     $controller = new FileController();
        //     $action = 'read';
        //     $param = $uri[2] ?? null;
        //     break;
        // case 'file':
        //     $controller = new FileController();
        //     if($request_method == 'POST' && isset($uri[2])) {
        //         $action = 'upload';
        //         $param = $uri[2];
        //     } elseif($request_method == 'GET' && isset($uri[2])) {
        //         $action = 'download';
        //         $param = $uri[2];
        //     } elseif($request_method == 'DELETE' && isset($uri[2])) {
        //         $action = 'delete';
        //         $param = $uri[2];
        //     }
            break;
    }
}

if($controller && $action) {
    if($param) {
        $controller->$action($param);
    } else {
        $controller->$action();
    }
} else {
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid route"]);
}