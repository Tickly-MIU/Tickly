<?php
$router->post('/api/login', 'AuthController@login');
$router->post('/api/register', 'AuthController@register');
$router->post('/api/logout', 'AuthController@logout');
$router->post('/api/tasks/create', 'TasksController@create');
$router->post('/api/tasks/show', 'TasksController@readSingle');
$router->post('/api/tasks/update', 'TasksController@update');
$router->post('/api/tasks/delete', 'TasksController@delete');
$router->post('/api/category/create', 'CategoryController@create');
$router->post('/api/category/read', 'CategoryController@read');
$router->post('/api/category/update', 'CategoryController@update');
$router->post('/api/category/delete', 'CategoryController@delete');
$router->get('/api/logout', 'AuthController@logout');
$router->get('/api/profile', 'UsersController@profile');
$router->get('/api/session-check', 'UsersController@sessionCheck');
$router->get('/api/tasks', 'TasksController@read');