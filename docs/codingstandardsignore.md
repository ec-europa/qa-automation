# CodingStandardsIgnore

Because there are exceptions that can not be submitted to the phpcs-ruleset.xml we
allow on occasion the use of CodingStandardsIgnore comments.

The usage of these comments should be explicitly allowed by the QA team. If there is
usage of these comments without consulting the QA team this code will always be
checked. This may result in longer unneccesary QA.

When an exception is allowed you always have to mention the ticket OR the exception
overview in the wiki where we can find the reasoning for making the exception. Common
exceptions will be taken up in the wiki and if possible in the phpcs-ruleset.xml.

## 1. Examples of CodingStandardsIgnore comments:

### 1.1 @CodingStandardsIgnoreLine

This example shows valid usage of parse_url instead of drupal_parse_url because the
Drupal wrapped function only takes one argument and thus doesn't allow the type to be
set. To skip coding standard checks we use the single line comment tag.
```php
  // @CodingStandardsIgnoreLine: MULTISITE-XXXXX
  $js_paths[1] = parse_url(file_create_url($js_paths[0]), PHP_URL_PATH);
  if (substr($js_paths[1], 0, strlen($GLOBALS['base_path'])) == $GLOBALS['base_path']) {
    $js_paths[1] = substr($js_paths[1], strlen($GLOBALS['base_path']));
  }
```

### 1.2 @CodingStandardsIgnoreStart - @CodingStandardsIgnoreEnd

This example shows a piece of code where the QA team could allow for an exception.
This example is purely fictional and does not represent a valid exception.
```php
// @CodingStandardsIgnoreStart: MULTISITE-XXXXX
/**
 * Implements hook_uninstall().
 */
function empty_hook_uninstall() {
}
// @CodingStandardsIgnoreEnd
```

### 1.3 @CodingStandardsIgnoreFile
This example shows usage where we skip coding standards checks for the entire file.
This example is purely fictional and does not represent a valid exception.
```php
<?php
// @CodingStandardsIgnoreFile: MULTISITE-XXXXX
/**
 * @file
 * Default theme implementation to display the splash page content.
 *
 * Available variables:
 *
 * - $languages_list: The html for the list of languages.
 *
 * @see splash_screen_preprocess_splash()
 */
?>

<?php global $base_url;?>
<?php print $languages_list; ?>
<?php print $languages_list_; ?>
```


