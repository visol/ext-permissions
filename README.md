ext-permissions
===============

Allows (recursive) setting of the usergroup for pages by non-admins.

This extension uses the SimpleDataHandlerController (aka tce_db.php API) which internally uses DataHandler (formerly TCEmain) to perform the changes.

DataHandler only allows the change the field "perms_groupid" (usergroup of a page) for admins and the owner of a page. **Therefore the user setting the permission needs to be either the owner of the pages or has to be an admin user**.

## Compatibility and Maintenance

This package is currently maintained for the following versions:

| TYPO3 Version         | Package Version | Branch  | Maintained    |
|-----------------------|-----------------|---------|---------------|
| TYPO3 11.5.x          | 3.x             | master  | Yes           |
| TYPO3 8.7.x           | 2.x             | -       | No            |
| TYPO3 6.2.x           | 1.x             | -       | No            |
