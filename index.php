<?php

// Load dependencies and environment variables
require_once __DIR__ . '/vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
$dotenv->load();

// Import controllers
use Controllers\AuthController;
use Controllers\ProjectController;
use Controllers\FileController;

// Set global CORS headers
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// Parse the request URI and HTTP method
$request_method = $_SERVER["REQUEST_METHOD"];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = explode('/', trim($uri, '/'));

$controller = null;
$action = null;
$param = null;

// Route dispatcher: decide which controller and method to call
if (isset($uri[0])) {
    switch ($uri[0]) {

        // User authentication endpoints
        case 'register':
            $controller = new AuthController();
            $action = 'register';
            break;

        case 'login':
            $controller = new AuthController();
            $action = 'login';
            break;

        // Project CRUD endpoints
        case 'projects':
            $controller = new ProjectController();
            if (isset($uri[1])) {
                $action = 'readOne'; // GET /projects/{id}
                $param = $uri[1];
            } else {
                $action = 'read'; // GET /projects
            }
            break;

        case 'project':
            $controller = new ProjectController();
            if ($request_method === 'POST') {
                $action = 'create'; // POST /project
            } elseif ($request_method === 'PUT') {
                $action = 'update'; // PUT /project
            } elseif ($request_method === 'DELETE' && isset($uri[1])) {
                $action = 'delete'; // DELETE /project/{id}
                $param = $uri[1];
            }
            break;

        // File endpoints: upload, list, download, delete
        case 'files':
            $controller = new FileController();
            if (isset($uri[1])) {
                $action = 'read'; // GET /files/{project_id}
                $param = $uri[1];
            }
            break;

        case 'file':
            $controller = new FileController();
            if ($request_method === 'POST' && isset($uri[1])) {
                $action = 'upload'; // POST /file/{project_id}
                $param = $uri[1];
            } elseif ($request_method === 'GET' && isset($uri[1])) {
                $action = 'download'; // GET /file/{file_id}
                $param = $uri[1];
            } elseif ($request_method === 'DELETE' && isset($uri[1])) {
                $action = 'delete'; // DELETE /file/{file_id}
                $param = $uri[1];
            }
            break;
    }
}

// Invoke the controller method, passing parameter if needed
if ($controller && $action) {
    if ($param !== null) {
        $controller->$action($param);
    } else {
        $controller->$action();
    }
} else {
    // Return error if no valid route is matched
    header('Content-Type: application/json');
    echo json_encode(["message" => "Invalid route"]);
}
