<?php

use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use Visol\Permissions\Controller\ManagePermissionsModuleFunctionController;
if (!defined('TYPO3')) {
    die('Access denied.');
}
ExtensionManagementUtility::insertModuleFunction(
    'web_func',
    ManagePermissionsModuleFunctionController::class,
    null,
    "LLL:EXT:permissions/Resources/Private/Language/locallang.xlf:title"
);
