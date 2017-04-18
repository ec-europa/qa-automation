# Database updates
One of the most important things is the integrity of the database. Therefore the QA
team always carefully inspects your hook_update_N's. We require the developers to
have only one update hook per feature or module for one deployment.

The only exception to this rule is when you are using the &$sandbox argument to
create a multipass update. Then it is preferable to make separate hooks so your
single actions do not take part in the loop.

The hook_update_N doc block should have the short description describing what it
does. Further description should go in the long description. A reference to a ticket
number also never hurts.

```php
/**
 * Disable and uninstall the dashboard module.
 *
 * MULTISITE-XXXXX: This module gets uninstalled because of performance issues.
 */
function mymodule_update_7001() {
  if (module_exists('dashboard')) {
    module_disable(array('dashboard'));
    drupal_uninstall_modules(array('dashboard'));
  }
}
```