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
$router->post('/api/forgot-password', 'PasswordResetController@requestReset');
$router->post('/api/reset-password', 'PasswordResetController@resetPassword');

// Admin Routes
$router->get('/api/admin/users', 'AdminController@getAllUsers');
$router->get('/api/admin/statistics', 'AdminController@getUserStatistics');
$router->get('/api/admin/activity-logs', 'AdminController@getActivityLogs');
$router->get('/api/admin/overview', 'AdminController@getSystemOverview');
$router->post('/api/admin/user/delete', 'AdminController@deleteUser');
$router->post('/api/admin/user/role', 'AdminController@updateUserRole');
$router->post('/api/admin/add-admin', 'AdminController@addNewAdmin');

// Reminders Routes
$router->post('/api/reminders/create', 'RemindersController@create');
$router->get('/api/reminders', 'RemindersController@getMyReminders');
$router->post('/api/reminders/task', 'RemindersController@getByTask');
$router->post('/api/reminders/update', 'RemindersController@update');
$router->post('/api/reminders/delete', 'RemindersController@delete');
$router->get('/api/reminders/debug', 'RemindersController@debugReminders');
$router->post('/api/reminders/send-notifications', 'RemindersController@sendNotifications');
$router->post('/api/reminders/send-deadline-notifications', 'RemindersController@sendDeadlineNotifications');