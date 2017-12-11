<?php

if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}
if (TYPO3_MODE === 'BE') {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::insertModuleFunction(
        'web_func',
        \Visol\Permissions\Controller\ManagePermissionsModuleFunctionController::class,
        null,
        "LLL:EXT:permissions/Resources/Private/Language/locallang.xlf:title"
    );
}
