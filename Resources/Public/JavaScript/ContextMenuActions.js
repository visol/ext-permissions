/**
 * Module: @visol/Permissions/ContextMenuActions
 */
class ContextMenuActions {
  /**
   * @param {string} table
   * @param {int} uid of the page
   * @param {array} dataAttributes
   */

  pagePermissions (table, uid, dataAttributes) {
    if (table === 'pages') {
      top.TYPO3.Backend.ContentContainer.setUrl(dataAttributes.pagesNewMultipleUrl);
    }
  }
};

export default new ContextMenuActions();

