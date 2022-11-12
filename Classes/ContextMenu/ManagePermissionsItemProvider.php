<?php

namespace Visol\Permissions\ContextMenu;

use TYPO3\CMS\Backend\ContextMenu\ItemProviders\RecordProvider;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ManagePermissionsItemProvider extends RecordProvider
{
    protected $itemsConfiguration = [
        'pages_permissions' => [
            'type' => 'item',
            'label' => 'LLL:EXT:permissions/Resources/Private/Language/locallang.xlf:title',
            'iconIdentifier' => 'actions-lock',
            'callbackAction' => 'pagePermissions'
        ]
    ];

    public function canHandle(): bool
    {
        return true;
    }

    public function getPriority(): int
    {
        return 60;
    }

    protected function getAdditionalAttributes(string $itemName): array
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        return [
            'data-callback-module' => 'TYPO3/CMS/Permissions/ContextMenuActions',
            'data-pages-new-multiple-url' => (string)$uriBuilder->buildUriFromRoute('pages_permissions', ['id' => $this->record['uid'] ?? 0]),
        ];
    }

    public function addItems(array $items): array
    {
        $this->initialize(); // load this->record
        $this->initDisabledItems();
        // renders an item based on the configuration from $this->itemsConfiguration
        $localItems = $this->prepareItems($this->itemsConfiguration);

        if (isset($items['more'])) {
            $items['more']['childItems'] +=  $localItems; // we merge the item at the end
        } else {
            $items = $items + $localItems;
        }
        //passes array of items to the next item provider
        return $items;
    }

    protected function canRender(string $itemName, string $type): bool
    {
        // checking if item is disabled through TSConfig
        if (in_array($itemName, $this->disabledItems, true)) {
            return false;
        }
        $canRender = false;
        switch ($itemName) {
            case 'pages_permissions':
                $canRender = $this->canShow();
                break;
        }
        return $canRender;
    }

    protected function canShow(): bool
    {
        return true;
    }
}
