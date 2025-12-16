<?php
$router->post('/api/login', 'AuthController@login');
$router->post('/api/register', 'AuthController@register');
$router->post('/api/tasks/create', 'TasksController@create');
$router->get('/api/tasks', 'TasksController@read');
$router->post('/api/tasks/show', 'TasksController@readSingle');
$router->post('/api/tasks/update', 'TasksController@update');
$router->post('/api/tasks/delete', 'TasksController@delete');

$router->post('/api/category/create', 'CategoryController@create');
$router->post('/api/category/read', 'CategoryController@read');
$router->post('/api/category/update', 'CategoryController@update');
$router->post('/api/category/delete', 'CategoryController@delete');
