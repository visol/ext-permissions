<?php

namespace Visol\Permissions\Controller;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use TYPO3\CMS\Backend\Routing\UriBuilder;
use TYPO3\CMS\Backend\Template\ModuleTemplate;
use TYPO3\CMS\Backend\Template\ModuleTemplateFactory;
use TYPO3\CMS\Backend\Tree\View\PageTreeView;
use TYPO3\CMS\Backend\Utility\BackendUtility;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\DataHandling\DataHandler;
use TYPO3\CMS\Core\Http\HtmlResponse;
use TYPO3\CMS\Core\Imaging\IconFactory;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;
use TYPO3\CMS\Fluid\View\StandaloneView;

class ManagePermissionsController
{
    const PERMISSION_EDIT_PAGE = 2;

    /**
     * @var ModuleTemplate
     */
    protected $moduleTemplate;

    protected IconFactory $iconFactory;
    protected ModuleTemplateFactory $moduleTemplateFactory;

    public function __construct()
    {
        $this->iconFactory = GeneralUtility::makeInstance(IconFactory::class);
        $this->moduleTemplateFactory = GeneralUtility::makeInstance(ModuleTemplateFactory::class);
    }

    public function mainAction(ServerRequestInterface $request): ResponseInterface
    {
        $this->moduleTemplate = $this->moduleTemplateFactory->create($request);

        $id = (int)$request->getQueryParams()['id'];
        $depth = $request->getQueryParams()['depth'] ? (int)$request->getQueryParams()['depth'] : 2;
        $userGroup = (string)$request->getParsedBody()['usergroup'];

        if ($userGroup) {
            // change usergroup
            $uids = $this->getRecursivePageUids($id, $depth);
            $this->setUserGroupValue($uids, $userGroup);
        }

        $view = GeneralUtility::makeInstance(StandaloneView::class);
        $view->setTemplatePathAndFilename(GeneralUtility::getFileAbsFileName(
            'EXT:permissions/Resources/Private/Templates/Index.html'
        ));

        $view->assign('depth', $depth);

        $depthBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('pages_permissions', [
            'id' => $id,
            'depth' => '__DEPTH__',
        ]);
        $view->assign('depthBaseUrl', $depthBaseUrl);

        $idBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('pages_permissions', [
            'depth' => $depth,
        ]);
        $view->assign('idBaseUrl', $idBaseUrl);

        $cmdBaseUrl = GeneralUtility::makeInstance(UriBuilder::class)->buildUriFromRoute('pages_permissions', [
            'id' => $id,
            'depth' => $depth,
        ]);
        $view->assign('cmdBaseUrl', $cmdBaseUrl);

        $depthOptions = [];
        foreach ([1, 2, 3, 4, 10] as $depthLevel) {
            $levelLabel = $depthLevel === 1 ? 'level' : 'levels';
            $depthOptions[$depthLevel] =
                $depthLevel . ' ' .
                LocalizationUtility::translate(
                    'LLL:EXT:beuser/Resources/Private/Language/locallang_mod_permission.xlf:' . $levelLabel,
                    'beuser'
                );
        }
        $view->assign('depthOptions', $depthOptions);


        $view->assign('LLPrefix', 'LLL:EXT:permissions/Resources/Private/Language/locallang.xlf:');

        $tree = $this->getPageTree($id, $depth);
        $view->assign('viewTree', $tree->tree);

        $view->assign('usergroups', $this->getUserGroups());
        $view->assign('beusers', $this->getBackendUsers());

        $this->moduleTemplate->setContent($view->render());
        return new HtmlResponse($this->moduleTemplate->renderContent());
    }

    /**
     * Return an array of page id's where the user have access to
     *
     * @param $id
     * @param $depth
     */
    protected function getRecursivePageUids($id, $depth): array
    {
        $tree = $this->getPageTree($id, $depth);
        $rows = $this->getRowsFromPageTree($tree);

        $uidList = [];

        if ($this->checkPermissionsForRow($rows[$id])) {
            $uidList[] = $id;
        }

        if ($this->getBackendUser()->user['uid'] && count($tree->ids_hierarchy)) {
            reset($tree->ids_hierarchy);
            for ($a = $depth; $a > 0; $a--) {
                if (is_array($tree->ids_hierarchy[$a])) {
                    reset($tree->ids_hierarchy[$a]);
                    foreach ($tree->ids_hierarchy[$a] as $theId) {
                        if ($this->checkPermissionsForRow($rows[$theId])) {
                            $uidList[] = $theId;
                        }
                    }
                }
            }
        }

        return $uidList;
    }

    protected function getPageTree($id, $depth): PageTreeView
    {
        /** @var PageTreeView $tree */
        $tree = GeneralUtility::makeInstance(PageTreeView::class);
        $tree->init(' AND ' . $this->getBackendUser()->getPagePermsClause(Permission::PAGE_SHOW));
        $tree->setRecs = 1;
        $tree->makeHTML = true;
        $tree->thisScript = 'index.php';
        $tree->addField('perms_userid');
        $tree->addField('perms_user');
        $tree->addField('perms_groupid');
        $tree->addField('perms_group');
        $tree->addField('perms_everybody');

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


    /**
     * Get rows from PageTreeView. Use uid as key
     *
     * @throws \Exception
     */
    protected function getRowsFromPageTree(PageTreeView $tree): array
    {
        $rows = [];

        foreach ($tree->tree as $treeItem) {
            $uid = $treeItem['row']['uid'];
            $row = $treeItem['row'];

            if (!is_int($uid)) {
                throw new \Exception ('Could not determine uid for treeItem', 1522933282);
            }

            if (!is_array($row)) {
                throw new \Exception ('Could not find row data for treeItem', 1522933282);
            }

            $rows[$uid] = $row;
        }

        return $rows;

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

    protected function getBackendUser(): BackendUserAuthentication
    {
        return $GLOBALS['BE_USER'];
    }

    protected function setUserGroupValue(array $uids, string $value)
    {
        $data = [];
        foreach ($uids as $uid) {
            $data['pages'][$uid]['perms_groupid'] = $value;
        }

        /** @var DataHandler $dataHandler */
        $dataHandler = GeneralUtility::makeInstance(DataHandler::class);
        $dataHandler->start($data, []);
        $dataHandler->process_datamap();
    }

    /**
     * Get a select option for each user group
     */
    public function getUserGroups(): array
    {
        $tableName = 'be_groups';
        $q = $this->getQueryBuilder($tableName);
        $rows = $q->select('*')
            ->from($tableName)
            ->orderBy('title', 'ASC')
            ->execute()
            ->fetchAllAssociative();

        $userGroupSelectorOptions = [];
        foreach ($rows as $row) {
            $userGroupSelectorOptions[$row['uid']] = $row['title'];
        }

        return $userGroupSelectorOptions;
    }

    /**
     * Get a select option for each user group
     */
    public function getBackendUsers(): array
    {
        $tableName = 'be_users';
        $q = $this->getQueryBuilder($tableName);
        $rows = $q->select('*')
            ->from($tableName)
            ->orderBy('username', 'ASC')
            ->execute()
            ->fetchAllAssociative();

        $beUsers = [];
        foreach ($rows as $row) {
            $beUsers[$row['uid']] = $row['username'];
        }

        return $beUsers;
    }

    protected function getQueryBuilder(string $tableName): QueryBuilder
    {
        /** @var ConnectionPool $connectionPool */
        $connectionPool = GeneralUtility::makeInstance(ConnectionPool::class);
        return $connectionPool->getQueryBuilderForTable($tableName);
    }
}
