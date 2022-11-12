<?php

use Visol\Permissions\Controller\ManagePermissionsController;

return [
    'pages_permissions' => [
        'path' => '/pages/permissions',
        'target' => ManagePermissionsController::class . '::mainAction',
        'redirect' => [
            'enable' => true,
            'parameters' => [
                'id' => true,
            ],
        ],
    ],
];
