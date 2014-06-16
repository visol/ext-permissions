<?php
namespace Visol\Permissions\Controller;
use TYPO3\CMS\Backend\Utility\BackendUtility;

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
class ManagePermissionsModuleFunctionController extends \TYPO3\CMS\Backend\Module\AbstractFunctionModule {

	var $tree;

	/**
	 *    Adds menu items
	 *
	 * @return    array
	 * @ignore
	 */
	public function modMenu() {
		$levelsLabel = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_perm.xlf:levels');

		return array(
			'tx_permissions_managepermissions_depth' => array(
				1 => '1 ' . $levelsLabel,
				2 => '2 ' . $levelsLabel,
				3 => '3 ' . $levelsLabel,
				4 => '4 ' . $levelsLabel,
				10 => '10 ' . $levelsLabel
			)
		);
	}

	/**
	 * Main function creating the content for the module.
	 *
	 * @return    string        HTML content for the module, actually a "section" made through the parent object in $this->pObj
	 */
	public function main() {
		$GLOBALS['LANG']->includeLLFile('EXT:permissions/Resources/Private/Language/locallang.xml');
		define('TYPO3_MOD_PATH', 'sysext/func/mod1/');

		$this->getPageTree();

		// title
		$theOutput = $this->pObj->doc->spacer(5);
		$theOutput .= $this->pObj->doc->section($GLOBALS['LANG']->getLL('title'), '', 0, 1);

		// depth menu
		$menu = $GLOBALS['LANG']->sL('LLL:EXT:lang/locallang_mod_web_perm.xlf:Depth') . ': ' .
			\TYPO3\CMS\Backend\Utility\BackendUtility::getFuncMenu($this->pObj->id,
				'SET[tx_permissions_managepermissions_depth]',
				$this->pObj->MOD_SETTINGS['tx_permissions_managepermissions_depth'],
				$this->pObj->MOD_MENU['tx_permissions_managepermissions_depth']
			);
		$theOutput .= $this->pObj->doc->spacer(5);
		$theOutput .= $this->pObj->doc->section('', $menu, 0, 1);

		// output page tree
		$theOutput .= $this->pObj->doc->spacer(10);
		$theOutput .= $this->pObj->doc->section('', $this->showPageTree(), 0, 1);

		// new form (close old)
		$theOutput .= '</form>';
		$theOutput .= $this->pObj->doc->spacer(10);

		$theOutput .= '<form action="' . $GLOBALS['BACK_PATH'] . 'tce_db.php" method="POST" name="editform">';
		$theOutput .= '<input type="hidden" name="id" value="' . $this->pObj->id . '">';
		$theOutput .= '<input type="hidden" name="redirect" value="' . \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('web_func') . '&id=' . $this->pObj->id . '">';

		$theOutput .= \TYPO3\CMS\Backend\Form\FormEngine::getHiddenTokenField('tceAction');

		$theOutput .= '<select name="data[pages][' . $this->pObj->id . '][perms_groupid]">';
		$theOutput .= $this->getUsergroupSelectorOptions();
		$theOutput .= '</select>';
		$theOutput .= '<input type="hidden" name="mirror[pages][' . $this->pObj->id . ']" value="' . htmlspecialchars(implode(',', $this->getRecursivePageIDArray())) . '">';

		// submit buttons
		$theOutput .= '<input type="submit" name="setGroup" value="' . $GLOBALS['LANG']->getLL('setGroup') . '" onclick="document.editform[\'data[pages][' . $this->pObj->id . '][no_search]\'].value=0;"> ';


		return $theOutput;
	}


	public function showPageTree() {
		$tableLayout = array(
			'table' => array('<table class="typo3-dblist" style="width:auto;"><tbody>', '</tbody></table>'),
			'0' => array(
				'tr' => array('<tr class="t3-row-header">', '</tr>'),
				'0' => array('<td nowrap="nowrap">', '</td>'),
				'1' => array('<td nowrap="nowrap">', '</td>'),
			),
			'defRow' => array(
				'tr' => array('<tr class="db_list_normal">', '</tr>'),
				'0' => array('<td nowrap="nowrap">', '</td>'),
				'1' => array('<td nowrap="nowrap">&nbsp;&nbsp;', '&nbsp;&nbsp;</td>'),
			)
		);

		$table = array();
		$tr = 0;
		$table[$tr][0] = '';
		$table[$tr][1] = '<strong>' . $GLOBALS['LANG']->getLL('group') . ':</strong>';
		$tr++;
		foreach ($this->tree->tree as $pageItem) {
			if (!($this->admin || $GLOBALS['BE_USER']->doesUserHaveAccess($pageItem['row'], $perms))) {
				$tableLayout[$tr]['tr'] = array('<tr class="bgColor4-20">', '</tr>');
			}

			$title = \TYPO3\CMS\Core\Utility\GeneralUtility::fixed_lgd_cs($this->tree->getTitleStr($pageItem['row']), $GLOBALS['BE_USER']->uc['titleLen']);
			$treeItem = $pageItem['HTML'] . $this->tree->wrapTitle($title, $pageItem['row']);

			$table[$tr][0] = $treeItem . '&nbsp;';

			$usergroupName = $this->getUsergroupNameForPage($pageItem['row']);
			$table[$tr++][1] = $usergroupName;
		}

		return $this->pObj->doc->table($table, $tableLayout);
	}


	/**
	 * Reads the page tree
	 *
	 * @return    void
	 */
	public function getPageTree() {
		$this->tree = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('Visol\Permissions\Service\PageTreeView');
		$this->tree->init(' AND ' . $this->pObj->perms_clause);
		$this->tree->setRecs = 1;
		$this->tree->makeHTML = TRUE;
		$this->tree->thisScript = 'index.php';
		$this->tree->addField('no_search');
		$this->tree->addField('perms_userid', 1);
		$this->tree->addField('perms_groupid', 1);
		$this->tree->addField('perms_user', 1);
		$this->tree->addField('perms_group', 1);
		$this->tree->addField('perms_everybody', 1);

		// set Root icon
		$HTML = '<img src="' . $GLOBALS['BACK_PATH'] . \TYPO3\CMS\Backend\Utility\IconUtility::getIcon('pages', $this->pObj->pageinfo) . '" title="' . \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordIconAltText($this->pObj->pageinfo, $this->tree->table) . '" width="18" height="16" align="top">';
		$this->tree->tree[] = array('row' => $this->pObj->pageinfo, 'HTML' => $HTML);

		$this->tree->getTree($this->pObj->id, $this->pObj->MOD_SETTINGS['tx_permissions_managepermissions_depth'], '');
	}


	/**
	 * Return an array of page id's where the user have access to
	 *
	 * @return    array    pages uid array
	 */
	public function getRecursivePageIDArray() {
		$theIdListArr = array();

		if ($GLOBALS['BE_USER']->user['uid'] && count($this->tree->ids_hierarchy)) {
			reset($this->tree->ids_hierarchy);
			$theIdListArr = array();
			for ($a = $this->pObj->MOD_SETTINGS['tx_permissions_managepermissions_depth']; $a > 0; $a--) {
				if (is_array($this->tree->ids_hierarchy[$a])) {
					reset($this->tree->ids_hierarchy[$a]);
					while (list(, $theId) = each($this->tree->ids_hierarchy[$a])) {
						if ($this->admin || $GLOBALS['BE_USER']->doesUserHaveAccess($this->tree->tree[$theId]['row'], $perms)) {
							$theIdListArr[] = $theId;
						}
					}
					$lKey = $getLevels - $a + 1;
				}
			}
		}

		return $theIdListArr;
	}

	/**
	 * Returns the title of a backend usergroup from a page row
	 *
	 * @param $page
	 * @return string
	 */
	public function getUsergroupNameForPage($page) {
		$usergroupUid = $page['perms_groupid'];
		$whereClause = 'uid=' . $usergroupUid . BackendUtility::deleteClause('be_groups') . BackendUtility::BEenableFields('be_groups');
		$row = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('title', 'be_groups', $whereClause);
		return $row['title'];
	}

	/**
	 * Get a select option for each user group
	 *
	 * @return string
	 */
	public function getUsergroupSelectorOptions() {
		/** @var \TYPO3\CMS\Core\Database\DatabaseConnection $databaseHandle */
		$databaseHandle = $GLOBALS['TYPO3_DB'];
		$whereClause = '1=1' . BackendUtility::deleteClause('be_groups') . BackendUtility::BEenableFields('be_groups');
		$rows = $databaseHandle->exec_SELECTgetRows('*', 'be_groups', $whereClause, '', 'title ASC');
		$usergroupSelector = array();
		foreach ($rows as $row) {
			$usergroupSelector[] = '<option value="' . $row['uid'] . '">' . $row['title'] . '</option>';
		}
		return implode($usergroupSelector);
	}
}

?>
