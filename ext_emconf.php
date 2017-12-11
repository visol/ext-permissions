<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Web>Func, Wizards, Manage permissions',
    'description' => 'Manage user group permissions for pages.',
    'category' => 'module',
    'shy' => '0',
    'dependencies' => '',
    'conflicts' => '',
    'priority' => '',
    'loadOrder' => '',
    'module' => '',
    'state' => 'beta',
    'uploadfolder' => '0',
    'createDirs' => '',
    'modify_tables' => '',
    'clearCacheOnLoad' => '0',
    'lockType' => '',
    'author' => 'Lorenz Ulrich',
    'author_email' => 'lorenz.ulrich@visol.ch',
    'author_company' => 'visol digitale Dienstleistungen GmbH',
    'version' => '1.0.0',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '6.2.0-6.2.999',
                    'func' => '6.2.0-6.2.999',
                ],
            'conflicts' =>
                [],
            'suggests' =>
                [],
        ],
];
