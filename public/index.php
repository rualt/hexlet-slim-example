<?php

use Slim\Factory\AppFactory;
use Slim\Middleware\MethodOverrideMiddleware;

use DI\Container;
use function Stringy\create as s;

require __DIR__ . '/../vendor/autoload.php';
const FILE = __DIR__ . '/../data/characters.json';

session_start();

$container = new Container();
$container->set('renderer', function () {
    return new \Slim\Views\PhpRenderer(__DIR__ . '/../templates');
});
$container->set('flash', function () {
    return new \Slim\Flash\Messages();
});

AppFactory::setContainer($container);
$app = AppFactory::create();
$app->addErrorMiddleware(true, true, true);
$app->add(MethodOverrideMiddleware::class);

$router = $app->getRouteCollector()->getRouteParser();
$repo = new App\Repository();
$users = $repo->getData(FILE);

$app->get('/', function ($request, $response) use ($router) {
    $urlUsers = $router->urlFor('users');
    $urlTest = $router->urlFor('test');
    $params = [
        'urlUsers' => $urlUsers,
        'urlTest' => $urlTest
    ];
    return $this->get('renderer')->render($response, 'index.phtml', $params);
})->setName('home');

$app->get('/users', function ($request, $response) use ($router, $users) {
    $messages = $this->get('flash')->getMessages();

    $urlUsers = $router->urlFor('users');
    $urlNewUser = $router->urlFor('new user');

    $term = $request->getQueryParam('term');

    $page = $request->getQueryParam('page', 1);
    $per = $request->getQueryParam('per', 10);
    $offset = ($page - 1) * $per;
    $lastPage = ceil(count($users) / $per);
    $usersPerPage = collect($users)->sortBy('name')->filter(function ($user) use ($term) {
        return s($user['name'])->startsWith($term, false);
    })->slice($offset, $per);
    $params = [
        'users' => $usersPerPage,
        'term' => $term,
        'page' => $page,
        'lastPage' => $lastPage,
        'urlUsers' => $urlUsers,
        'urlNewUser' => $urlNewUser,
        'messages' => $messages
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

$app->get('/users/{id}', function ($request, $response, $args) use ($users, $router) {
    $id = $args['id'];
    $user = collect($users)->firstWhere('id', $id);
    $urlUserEdit = $router->urlFor('editUser', ['id' => $user['id']]);
    $messages = $this->get('flash')->getMessages();
    $params = [
        'user' => $user,
        'id' => $id,
        'urlUserEdit' => $urlUserEdit,
        'messages' => $messages
    ];
    $response = isset($user) ? $response : $response->withStatus(404)->withHeader('Location', '/404');
    return $this->get('renderer')->render($response, 'users/show.phtml', $params);
})->setName('user');

$app->get('/users/{id}/edit', function ($request, $response, $args) use ($router, $users) {
    $id = $args['id'];
    $user = collect($users)->firstWhere('id', $id);
    $params = [
        'user' => $user,
        'errors' => []
    ];
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
})->setName('editUser');
    

$app->post('/users', function ($request, $response) use ($repo, $router) {
    $validator = new App\Validator();
    $user = $request->getParsedBodyParam('user');
    $errors = $validator->validate($user);
    if (count($errors) === 0) {
        $urlUsers = $router->urlFor('users');
        $repo->saveData($user, FILE);
        $this->get('flash')->addMessage('success', 'User is added!');
        return $response->withRedirect($urlUsers);
    }

    $params = [
        'user' => $user,
        'errors' => $errors
    ];
    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, "users/new.phtml", $params);
});

$app->patch('/users/{id}', function ($request, $response, array $args) use ($users, $repo, $router) {
    $id = $args['id'];
    $user = collect($users)->firstWhere('id', $id);
    $data = $request->getParsedBodyParam('user');

    $validator = new App\Validator();
    $errors = $validator->validate($data);

    if ($user['name'] == $data['name']) {
        $errors['name'] = "You didn't change the name";
    } elseif (count($errors) === 0) {
        // Ручное копирование данных из формы в нашу сущность
        $user['name'] = $data['name'];
        $this->get('flash')->addMessage('success', 'User has been updated');
        $repo->saveData($user, FILE);
        $url = $router->urlFor('user', ['id' => $user['id']]);
        return $response->withRedirect($url);
    }

    $params = [
        'userData' => $data,
        'user' => $user,
        'errors' => $errors
    ];

    $response = $response->withStatus(422);
    return $this->get('renderer')->render($response, 'users/edit.phtml', $params);
});


$app->get('/404', function ($request, $response) use ($router) {
    $urlHome = $router->urlFor('home');
    $response->write("Page not foung<br>");
    $response->write("<a href='{$urlHome}'>⌂ Main page</a><br>");
    return $response;
})->setName('not found');

$app->get('/test', function ($request, $response) use ($router) {
    foreach ($request->getHeaders() as $name => $values) {
        echo $name . ': ' . implode(', ', $values) . '<br>';
    }
    return $response;
})->setName('test');

$app->run();
