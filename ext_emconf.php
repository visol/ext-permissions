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
    'version' => '2.0.1',
    'constraints' =>
        [
            'depends' =>
                [
                    'typo3' => '8.7.0-8.7.999',
                    'func' => '8.7.0-8.7.999',
                ],
            'conflicts' =>
                [],
            'suggests' =>
                [],
        ],
];
