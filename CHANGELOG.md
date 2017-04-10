# QA automation tools change log

## Version 2.4

### New features:
  * MULTISITE-15888 - Fixed compatibility for platform integration
  * MULTISITE-16111 - Added sniff to detect curl_init() implementations
  * MULTISITE-15711 - Added sniff to detect custom modules placed in the features folder
  * MULTISITE-15552 - Added sniff to detect internal hardcoded paths

### Improvements:
  * MULTISITE-15576 - Enabled check:ssk by default on the review:full command
  * MULTISITE-15555 - All sniffs are covered by phpunit testing again

### Bug fixes:
  * MULTISITE-16586 - Added hook_update_last_removed() to allowed install file functions
  * MULTISITE-16318 - Added hook_field_schema() to allowed install file functions
  * MULTISITE-16041 - Fixed feature files sniffs that chocked on certain arrays

## Version 2.5

### Bug fixes:
  * MULTISITE-15888 - Make qa-automation compatible with platform-dev

## Version 2.6

### New features:
  * MULTISITE-15705 - Added sniff for empty (pathauto) strongarm settings
  * MULTISITE-15680 - Added theme checks
