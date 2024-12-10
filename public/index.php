<?php

// Display all errors

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . '/../src/Services/Init.php';

$router = new AltoRouter();

// Include the API Routes
require_once __DIR__ . '/../src/Routes/api.php';

// Include the Web Routes
require_once __DIR__ . '/../src/Routes/web.php';


/* Match the current request 
| Dont' remove this code 
| This code match the request and show the specific file or Controller that match 
| Want to add new routes go to the /src/Routes/ Folder for APIs use api.php and for common routes use web.php
*/

$match = $router->match();

if ($match) {
    if (is_callable($match['target'])) {
        // If target is a closure, call it directly
        call_user_func_array($match['target'], $match['params']);
    } else {
        list($controller, $method) = explode('@', $match['target']);

        // Dynamically call the controller and method based on the route target
        $controller = 'App\\Controllers\\' . $controller;
        if(class_exists($controller) && method_exists($controller, $method)){
            $object = new $controller();

             // Convert associative array to indexed array to avoid named parameter issue
             $params = array_values($match['params']);
            call_user_func_array([$object, $method], $params);
        } else{
            // Handle error - controller or method not found
            header("HTTP/1.0 404 Not Found");
            echo "404 - Controller or method not found";
        }
    }

} else {
    // No route was matched
    header("HTTP/1.0 404 Not Found");
    echo $twig->render('404.twig');
}
