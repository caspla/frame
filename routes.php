<?php
return [
    '/' => [
        'controller' => 'DefaultController',
        'action' => 'indexAction',
    ],
    '/users' => [
        'controller' => 'UsersController',
        'action' => 'listAction'
    ],
    '/users/create' => [
        'controller' => 'UsersController',
        'action' => 'createAction',
    ],
    '/users/edit/{id}' => [
        'controller' => 'UsersController',
        'action' => 'editAction',
    ],
    '/users/save' => [
        'controller' => 'UsersController',
        'action' => 'saveAction'
    ],
];