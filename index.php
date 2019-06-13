<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once 'vendor/autoload.php';
require_once 'Task.php';

use Relay\Relay;
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
    'driver'    => 'mysql',
    'host'      => 'localhost',
    'database'  => 'todo',
    'username'  => 'root',
    'password'  => '',
    'charset'   => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix'    => '',
]);

// // Set the event dispatcher used by Eloquent models... (optional)
// use Illuminate\Events\Dispatcher;
// use Illuminate\Container\Container;
// $capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();


$request = Zend\Diactoros\ServerRequestFactory::fromGlobals(
    $_SERVER,
    $_GET,
    $_POST,
    $_COOKIE,
    $_FILES
);

$loader = new Twig_Loader_Filesystem('.');
$twig = new \Twig_Environment($loader, array(
    'debug' => true,
    'cache' => false,
));

$router = new Aura\Router\RouterContainer();
$map = $router->getMap();
$Uri = parse_url($_SERVER["REQUEST_URI"], PHP_URL_PATH);

$map->get('todo.list', $Uri, function ($request) use ($twig) {
    // $tasks = [
    //     [
    //         'id' => 1,
    //         'description' => 'Aprender inglés',
    //         'done' => false
    //     ],
    //     [
    //         'id' => 1,
    //         'description' => 'Hacer la tarea',
    //         'done' => true
    //     ],
    //     [
    //         'id' => 1,
    //         'description' => 'Pasear al perro',
    //         'done' => false
    //     ],
    //     [
    //         'id' => 1,
    //         'description' => 'Ver el curso de introducción a PHP',
    //         'done' => false
    //     ]
    // ];

$tasks = Task::all();
$response = new Zend\Diactoros\Response\HtmlResponse($twig->render('template.twig', [
        'tasks' => $tasks
    ]));
    return $response;
});

$relay = new Relay([
    new Middlewares\AuraRouter($router),
    new Middlewares\RequestHandler()
]);

$response = $relay->handle($request);

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header(sprintf('%s: %s', $name, $value), false);
    }
}
echo $response->getBody();
