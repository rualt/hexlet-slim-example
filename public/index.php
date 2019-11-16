<?php

use Slim\Factory\AppFactory;
use DI\Container;
use function Stringy\create as s;
use function HP\Parser\csvToArray;

require __DIR__ . '/../vendor/autoload.php';

$users = csvToArray(';', '"', __DIR__ . '/../data/characters.csv');

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

$app->get('/users', function ($request, $response) use ($router, $users) {
    $urlUsers = $router->urlFor('users');
    $term = $request->getQueryParam('term');
    $result = collect($users)->sortBy('Name')->filter(function ($user) use ($term) {
        return s($user['Name'])->startsWith($term, false);
    });
    $params = [
        'users' => $result,
        'term' => $term,
        'urlUsers' => $urlUsers
    ];
    return $this->get('renderer')->render($response, 'users/index.phtml', $params);
})->setName('users');

$app->get('/users/{id}', function ($request, $response, $args) use ($users) {
    $id = $args['id'];
    $user = collect($users)->firstWhere('Id', $id);
    $params = [
        'user' => $user,
        'id' => $id
    ];
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

/* $app->get('/users/new', function ($request, $response) {
    $params = [
        'user' => ['name' => '', 'email' => '', 'password' => '', 'passwordConfirmation' => '', 'city' => ''],
        'errors' => []
    ];
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
 })->setName('new');

/* S$app->post('/users', function ($request, $response) use ($repo) {
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
