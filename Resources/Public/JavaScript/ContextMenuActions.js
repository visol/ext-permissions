/**
 * Module: TYPO3/CMS/Permissions/ContextMenuActions
 *
 * @exports TYPO3/CMS/Permissions/ContextMenuActions
 */
define(['jquery'], function ($) {
  'use strict';

  /**
   * @exports TYPO3/CMS/Permissions/ContextMenuActions
   */
  const ContextMenuActions = {};

  /**
   * @param {string} table
   * @param {int} uid of the page
   */
  ContextMenuActions.pagePermissions = function (table, uid) {
    if (table === 'pages') {
      const url = $(this).data('pages-new-multiple-url');
      top.TYPO3.Backend.ContentContainer.setUrl(url);
    }
  };

  return ContextMenuActions;
});
