ext-permissions
===============

Allows (recursive) setting of the usergroup for pages by non-admins.

This extension uses the SimpleDataHandlerController (aka tce_db.php API) which internally uses DataHandler (formerly TCEmain) to perform the changes.

DataHandler only allows the change the field "perms_groupid" (usergroup of a page) for admins and the owner of a page. **Therefore the user setting the permission needs to be either the owner of the pages or has to be an admin user**.
