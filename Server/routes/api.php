<?php
$router->post('/api/login', 'AuthController@login');
$router->post('/api/register', 'AuthController@register');
$router->post('/api/logout', 'AuthController@logout');
$router->post('/api/tasks/create', 'TasksController@create');
$router->post('/api/tasks/show', 'TasksController@readSingle');
$router->post('/api/tasks/update', 'TasksController@update');
$router->post('/api/tasks/delete', 'TasksController@delete');
$router->get('/api/logout', 'AuthController@logout');
$router->get('/api/profile', 'UsersController@profile');
$router->get('/api/session-check', 'UsersController@sessionCheck');
$router->get('/api/tasks', 'TasksController@read');
