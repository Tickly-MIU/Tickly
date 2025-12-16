<?php
$router->post('/api/login', 'AuthController@login');
$router->post('/api/register', 'AuthController@register');
$router->post('/api/logout', 'UsersController@logout');
$router->get('/api/profile', 'UsersController@profile');

$router->post('/api/tasks/create', 'TasksController@create');
$router->get('/api/tasks', 'TasksController@read');
$router->post('/api/tasks/show', 'TasksController@readSingle');
$router->post('/api/tasks/update', 'TasksController@update');
$router->post('/api/tasks/delete', 'TasksController@delete');
$router->get('/test', function () {
    echo 'OK';
});

?>