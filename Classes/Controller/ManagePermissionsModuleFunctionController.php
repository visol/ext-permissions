<?php
namespace Visol\Permissions\Controller;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Lorenz Ulrich <lorenz.ulrich@visol.ch>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *  A copy is found in the text file GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Database\DatabaseConnection;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ManagePermissionsModuleFunctionController extends \TYPO3\CMS\Backend\Module\AbstractFunctionModule
{

    const PERMISSION_EDIT_PAGE = 2;

    /**
     * @return string
     */
    public function main(): string
    {
        $id = $this->pObj->id;
        $depth = GeneralUtility::_GP('depth') ?: 2;
        $usergroup = GeneralUtility::_GP('usergroup');

        if ($usergroup) {
            // change usergroup
            $uids = $this->getRecursivePageUids($id, $depth);
            $this->setUsergroupValue($uids, $usergroup);
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:permissions/Resources/Private/Templates/Index.html'
        ));

        $view->assign('depth', $depth);

        $depthBaseUrl = BackendUtility::getModuleUrl(
            'web_func',
            [
                'SET' => [
                    'function' => self::class,
                ],
                'id' => $id,
                'depth' => '__DEPTH__',
            ]
        );
        $view->assign('depthBaseUrl', $depthBaseUrl);

        $idBaseUrl = BackendUtility::getModuleUrl(
            'web_func',
            [
                'SET' => [
                    'function' => self::class,
                ],
                'depth' => $depth,
            ]
        );
        $view->assign('idBaseUrl', $idBaseUrl);

        $cmdBaseUrl = BackendUtility::getModuleUrl(
            'web_func',
            [
                'SET' => [
                    'function' => self::class,
                ],
                'id' => $id,
                'depth' => $depth,
            ]
        );
        $view->assign('cmdBaseUrl', $cmdBaseUrl);

        $depthOptions = [];
        foreach ([1, 2, 3, 4, 10] as $depthLevel) {
            $levelLabel = $depthLevel === 1 ? 'level' : 'levels';
            $depthOptions[$depthLevel] = $depthLevel . ' ' . LocalizationUtility::translate('LLL:EXT:beuser/Resources/Private/Language/locallang_mod_permission.xlf:' . $levelLabel,
                    'beuser');
        }
        $view->assign('depthOptions', $depthOptions);

        $view->assign('LLPrefix', 'LLL:EXT:permissions/Resources/Private/Language/locallang.xlf:');

        $tree = $this->getPageTree($id, $depth);
        $view->assign('viewTree', $tree->tree);

        $view->assign('usergroups', $this->getUsergroups());

        return $view->render();
    }

    /**
     * Return an array of page id's where the user have access to
     *
     * @param $id
     * @param $depth
     *
     * @return array
     */
    protected function getRecursivePageUids($id, $depth): array
    {
        $tree = $this->getPageTree($id, $depth);

        $uidList = [];

        if ($this->checkPermissionsForRow($tree->tree[$id]['row'])) {
            $uidList[] = $id;
        }

        if ($this->getBackendUser()->user['uid'] && count($tree->ids_hierarchy)) {
            reset($tree->ids_hierarchy);

            for ($a = $depth; $a > 0; $a--) {
                if (is_array($tree->ids_hierarchy[$a])) {
                    reset($tree->ids_hierarchy[$a]);
                    while (list(, $theId) = each($tree->ids_hierarchy[$a])) {
                        if ($this->checkPermissionsForRow($tree->tree[$theId]['row'])) {
                            $uidList[] = $theId;
                        }
                    }
                }
            }
        }

        return $uidList;

    }

    /**
     * Reads the page tree
     *
     * @return PageTreeView
     */
    protected function getPageTree($id, $depth): PageTreeView
    {
        /** @var PageTreeView $tree */
        $tree = GeneralUtility::makeInstance(PageTreeView::class);
        $tree->init(' AND ' . $this->pObj->perms_clause);
        $tree->setRecs = 1;
        $tree->makeHTML = true;
        $tree->thisScript = 'index.php';
        $tree->addField('perms_groupid');

        if ($id) {
            $pageInfo = BackendUtility::readPageAccess($id, ' 1=1');
            $tree->tree[] = ['row' => $pageInfo, 'HTML' => $tree->getIcon($id)];
        } else {
            $pageInfo = ['title' => $GLOBALS['TYPO3_CONF_VARS']['SYS']['sitename'], 'uid' => 0, 'pid' => 0];
            $tree->tree[] = ['row' => $pageInfo, 'HTML' => $tree->getRootIcon($pageInfo)];
        }

        $tree->getTree($id, $depth, '');

        return $tree;
    }

    protected function checkPermissionsForRow($row): bool
    {
        if ($this->getBackendUser()->isAdmin()) {
            return true;
        }

        if ($this->getBackendUser()->doesUserHaveAccess($row, self::PERMISSION_EDIT_PAGE)) {
            return true;
        }

        return false;
    }

    /**
     * @return \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
     */
    protected function getBackendUser(): \TYPO3\CMS\Core\Authentication\BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    /**
     * @return DatabaseConnection
     */
    protected function getDatabaseConnection()
    {
        return $GLOBALS['TYPO3_DB'];
    }

    /**
     * @param array $uids
     * @param $value
     */
    protected function setUsergroupValue(array $uids, $value)
    {
        $data = [];
        foreach ($uids as $uid) {
            $data['pages'][$uid]['perms_groupid'] = $value;
        }

        /** @var DataHandler $dataHandler */
        $dataHandler = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
    }

    /**
     * Get a select option for each user group
     *
     * @return array
     */
    public function getUsergroups(): array
    {
        /** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseHandle */
        $databaseHandle = $GLOBALS['TYPO3_DB'];
        $whereClause = '1=1' . BackendUtility::deleteClause('be_groups') . BackendUtility::BEenableFields('be_groups');
        $rows = $databaseHandle->exec_SELECTgetRows('*', 'be_groups', $whereClause, '', 'title ASC');
        $usergroupSelectorOptions = [];
        foreach ($rows as $row) {
            $usergroupSelectorOptions[$row['uid']] = $row['title'];
        }

        return $usergroupSelectorOptions;
    }
}
