<?php

namespace Visol\Permissions\ContextMenu;

use TYPO3\CMS\Backend\ContextMenu\ItemProviders\PageProvider;
use TYPO3\CMS\Backend\ContextMenu\ItemProviders\ProviderInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ManagePermissionsItemProvider extends PageProvider implements ProviderInterface
{
    protected $itemsConfiguration = [
        'pagesPermissions' => [
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
        return 46;
    }

    protected function getAdditionalAttributes(string $itemName): array
    {
        $uriBuilder = GeneralUtility::makeInstance(UriBuilder::class);
        $url = (string) $uriBuilder->buildUriFromRoute(
            'pages_permissions',
            [
                'id' => $this->record['uid'] ?? 0,
            ]
        );
        return [
            'data-callback-module' => '@visol/Permissions/ContextMenuActions',
            'data-pages-new-multiple-url' => $url,
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
        if ($itemName === 'pagesPermissions') {
            return $this->canShow();
        }
        return false;
    }

    protected function canShow(): bool
    {
        return $this->context === 'tree';
    }
}
