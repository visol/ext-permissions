<?php
namespace Visol\Permissions\Service;


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
class PageTreeView extends \TYPO3\CMS\Backend\Tree\View\PageTreeView
{

    /**
     * Wrapping $title in a-tags.
     *
     * @param string $title
     * @param string $v
     *
     * @return string
     */
    public function wrapTitle($title, $v)
    {
        $aOnClick = 'return jumpToUrl(\'index.php?id=' . $v['uid'] . '\',this);';

        return '<a href="#" onclick="' . htmlspecialchars($aOnClick) . '">' . $title . '</a>';
    }

    /**
     * Creates title attribute content for pages.
     * Uses API function in BackendUtility which will retrieve lots of useful information for pages.
     *
     * @param array $row
     *
     * @return string
     */
    public function getTitleAttrib($row)
    {
        return $iconAltText = \TYPO3\CMS\Backend\Utility\BackendUtility::getRecordIconAltText($row, $this->table);
    }

    /**
     * Wrapping the image tag, $icon, for the row, $row (except for mount points)
     *
     * @param string $icon
     * @param array $row
     *
     * @return string
     */
    public function wrapIcon($icon, $row)
    {
        // Add title attribute to input icon tag
        $theIcon = $this->addTagAttributes($icon,
            ($this->titleAttrib ? $this->titleAttrib . '="' . $this->getTitleAttrib($row) . '"' : ''));

        return $theIcon;
    }
}

?>
