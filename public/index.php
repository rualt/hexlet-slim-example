<?php

use Slim\Factory\AppFactory;
use DI\Container;
use function Stringy\create as s;

require __DIR__ . '/../vendor/autoload.php';

$file = file_get_contents(__DIR__ . '/../data/characters.json');
$users = json_decode($file, true);

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);

$router = $app->getRouteCollector()->getRouteParser();

$app->get('/', function ($request, $response) use ($router) {
    $urlUsers = $router->urlFor('users');
    $urlTest = $router->urlFor('test');
    $response->write("Welcome to Slim!<br>");
    $response->write("<a href='{$urlUsers}'>List of all characters</a><br>");
    $response->write("<a href='{$urlTest}'>Test</a><br>");
    return $response;
})->setName('home');


$app->get('/test', function ($request, $response) use ($router) {
    foreach ($request->getHeaders() as $name => $values) {
        echo $name . ': ' . implode(', ', $values) . '<br>';
    }
    return $response;
})->setName('test');

$app->get('/404', function ($request, $response) use ($router) {
    $urlHome = $router->urlFor('home');
    $response->write("Page not foung<br>");
    $response->write("<a href='{$urlHome}'>⌂ Main page</a><br>");
    return $response;
})->setName('not found');

$app->get('/users', function ($request, $response) use ($router, $users) {
    $urlUsers = $router->urlFor('users');
    $urlNewUser = $router->urlFor('new user');
    $term = $request->getQueryParam('term');
    $result = collect($users)->sortBy('Name')->filter(function ($user) use ($term) {
        return s($user['Name'])->startsWith($term, false);
    });
    $params = [
        'users' => $result,
        'term' => $term,
        'urlUsers' => $urlUsers,
        'urlNewUser' => $urlNewUser
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

$app->get('/users/new', function ($request, $response) use ($router, $users) {
    $params = [
        'user' => ['name' => '', 'gender' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
})->setName('new user');

$app->get('/users/{id}', function ($request, $response, $args) use ($users) {
    $id = $args['id'];
    $user = collect($users)->firstWhere('Id', $id);
    $params = [
        'user' => $user,
        'id' => $id
    ];
    $newResponse = isset($user) ? $response : $response->withStatus(404)->withHeader('Location', '/404');
    return $this->get('renderer')->render($newResponse, 'users/show.phtml', $params);
})->setName('user');

/* $app->post('/users', function ($request, $response) use ($repo) {
    $validator = new Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        $repo->save($user);
        return $response->withHeader('Location', '/')
          ->withStatus(302);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
}); */

 $app->run();
