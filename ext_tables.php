<?php

use Visol\Permissions\ContextMenu\ManagePermissionsItemProvider;
if (!defined('TYPO3')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['BE']['ContextMenu']['ItemProviders'][1668693255] = ManagePermissionsItemProvider::class;
