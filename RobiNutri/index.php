<?php
session_start();

require_once 'config/database.php';
require_once 'app/models/Database.php';

$request = $_SERVER['REQUEST_URI'];
$base_path = '/robinutri'; 

if (strpos($request, '/index.php/') !== false) {
    $path = str_replace($base_path . '/index.php', '', $request);
} else {
    $path = str_replace($base_path, '', $request);
}

$path = parse_url($path, PHP_URL_PATH);
$path = trim($path, '/');

$routes = [
    '' => 'HomeController@index',
    'home' => 'HomeController@index',  
    'chat' => 'ChatController@index',
    'chat/send' => 'ChatController@sendMessage',
    'chat/loadMessages' => 'ChatController@loadMessages', 
    'chat/loadChats' => 'ChatController@loadChats', 
    'chat/chatsPorPerfil' => 'ChatController@verChatsPorPerfil',
    'chat/getChatsUsuario' => 'ChatController@getChatsUsuario',
    'chat/delete' => 'ChatController@deleteChat',
    'chat/create' => 'ChatController@createChat',
    'chat/rename' => 'ChatController@renameChat',
    'profiles' => 'ProfileController@index',
    'profiles/save' => 'ProfileController@save',
    'profiles/load' => 'ProfileController@load',
    'profiles/loadAll' => 'ProfileController@loadAll',
    'profiles/delete' => 'ProfileController@delete',
    'login' => 'LoginController@index',
    'login/registro' => 'LoginController@registro',
    'login/auth' => 'LoginController@auth',
    'logout' => 'LoginController@logout',
];

//Enrutador (Router)
if (array_key_exists($path, $routes)) {
    list($controllerName, $method) = explode('@', $routes[$path]);
    
    $controllerFile = "app/controllers/{$controllerName}.php";

    if (file_exists($controllerFile)) {
        require_once $controllerFile;
        
        if (class_exists($controllerName)) {
            $controller = new $controllerName();
            
            if (method_exists($controller, $method)) {
                $controller->$method();
            } else {
                http_response_code(404);
                echo "Error: El método '$method' no existe en el controlador.";
            }
        } else {
            http_response_code(500);
            echo "Error: La clase '$controllerName' no se encuentra.";
        }
    } else {
        http_response_code(500);
        echo "Error: El archivo '$controllerFile' no existe.";
    }

} else {
    http_response_code(404);
    
    // A futuro crear una vista bonita para mostrar el error en app/views/404.php y cargarla aquí
    // require_once 'app/views/404.php'; 
    
    echo "<div style='text-align:center; margin-top:50px; font-family:sans-serif;'>";
    echo "<h1>Error 404</h1>";
    echo "<p>La página <strong>/$path</strong> no existe en RobiNutri.</p>";
    echo "<a href='/robinutri'>Volver al Inicio</a>";
    echo "</div>";
}
?>