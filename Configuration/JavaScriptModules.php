<?php

return [
    'dependencies' => ['core', 'backend'],
    'tags' => [
        'backend.contextmenu',
    ],
    'imports' => [
        '@visol/Permissions/' => 'EXT:permissions/Resources/Public/JavaScript/',
    ],
];
