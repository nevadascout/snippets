<?php

// Require composer autoloader
require __DIR__ . "/../vendor/autoload.php";

// Read .env
try {
    $dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . "/../");
    $dotenv->load();
} catch(InvalidArgumentException $ex) {
    // Ignore if no dotenv
}

/**
 * Register a custom autoload function
 */
spl_autoload_register(function ($class) {
    $file = __DIR__ . "/../src/" . str_replace("\\", DIRECTORY_SEPARATOR, $class) . ".php";
    if (file_exists($file)) {
        require $file;
        return true;
    }

    error_log("Unable to autoload file '{$file}'");

    return false;
});

session_start();

// Create Router instance
$router = new \Bramus\Router\Router();

// Activate CORS
function sendCorsHeaders() {
    if (!empty($_ENV["CORS_ALLOWED_ORIGIN"])) {
        header("Access-Control-Allow-Origin: {$_ENV["CORS_ALLOWED_ORIGIN"]}");
        header("Access-Control-Allow-Credentials: true");
        header("Access-Control-Allow-Headers: content-type");
        header("Access-Control-Allow-Methods: GET,HEAD,PUT,POST,DELETE");
    }
}

$router->options("/.*", function() {
    sendCorsHeaders();
});

sendCorsHeaders();

// Custom 404 Handler
$router->set404(function() {
    $controller = new \App\Controllers\ErrorsController();
    $controller->notFound();
});

// Check authentication
$router->before("POST|PUT|DELETE", "/.*", function() {
    requireAuth();
});

// Define API routes
$router->mount("/oauth", function() use ($router) {
    $controller = new \App\Controllers\OAuthController();

    $router->get("/initiate", function() use ($controller) {
        $controller->goToGithub();
    });

    $router->get("/callback", function() use ($controller) {
        $controller->callback();
    });

    // $router->post("/logout", function() use ($controller) {
    //     $controller->logout();
    // });
});

// $router->mount("/snippets", function() use ($router) {
//     $controller = new \App\Controllers\SnippetController();

//     $router->get("/", function() use ($controller) {
//         requireAuth();
//         $controller->listOwnSnippets();
//     });

//     $router->post("/", function() use ($controller) {
//         $controller->createSnippet();
//     });
//     $router->get("/(.*)", function($snippetId) use ($controller) {
//         $controller->getSnippet($snippetId);
//     });
//     $router->put("/(.*)", function($snippetId) use ($controller) {
//         $controller->updateSnippet($snippetId);
//     });
//     $router->delete("/(.*)", function($snippetId) use ($controller) {
//         $controller->deleteSnippet($snippetId);
//     });

//     $router->post("/(.*)/comments", function($snippetId) use ($controller) {
//         $controller->addComment($snippetId);
//     });
//     $router->put("/(.*)/comments/(.*)", function($snippetId, $commentId) use ($controller) {
//         $controller->updateComment($snippetId, $commentId);
//     });
//     $router->delete("/(.*)/comments/(.*)", function($snippetId, $commentId) use ($controller) {
//         $controller->deleteComment($snippetId, $commentId);
//     });
// });


$router->run();

function requireAuth() {
    if (!isset($_SESSION["AUTHENTICATED"]) || $_SESSION["AUTHENTICATED"] == false) {
        http_response_code(401);
        header("Content-Type: application/json; charset=utf-8");
        echo json_encode(array("error" => "Not logged in"));
        exit();
    }
}

// function updateUserLastActiveTime() {
//     if (isset($_SESSION["USER"])) {
//         $usersDAO = new \App\DAO\UsersDAO();
//         $usersDAO->setLastActiveTime($_SESSION["USER"]["email"], time());
//     }
// }
