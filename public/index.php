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

$app->get('/', function ($request, $response) {
    return $response->write('<a href="\users">Users</a>');
});

$app->get('/users', function ($request, $response) use ($users) {
    $term = $request->getQueryParam('term');
    $result = collect($users)->sortBy('Name')->filter(function ($user) use ($term) {
        return s($user['Name'])->startsWith($term, false);
    });
    $params = [
        'users' => $result,
        'term' => $term
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
})setName('new');

$app->post('/users', function ($request, $response) use ($repo) {
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
