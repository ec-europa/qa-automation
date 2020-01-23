# QA automation tools change log

### 3.0.14
  * MULTISITE-22834: Toolkit - PHPcs compatibility with php 7.3

### 3.0.13
  * MULTISITE-22031: Fix path in PHP Compatibility for ultimate_cron

### 3.0.12
  * MULTISITE-21816: PHPCS False Positive PHP in Views
  * MULTISITE-21950: Provide PHP Compatiblity Substandard ruleset for false positives

### 3.0.11
  * MULTISITE-21295: Replace wimg with phpcompatibility package
  * MULTISITE-21251: Remove dependency into master branch of Platform Dev
  * MULTISITE-21436: Add sniff to detect curly brackets
  * MULTISITE-21207: Add sniff for detecting whitespaces on tpl files
  * MULTISITE-21458: Create pipeline for QA automation check

### 3.0.10
  * MULTISITE-19378 - Added sniff for detecting broken handlers in exported views
  * MULTISITE-20745 - Added sniff for detecting PHP code in exported views
  * MULTISITE-21105 - Added a variable to configure the theme path

### 2.6
  * MULTISITE-15705 - Added sniff for empty (pathauto) strongarm settings
  * MULTISITE-15680 - Added theme checks

### 2.5
  * MULTISITE-15888 - Make qa-automation compatible with platform-dev

### 2.4
  * MULTISITE-15888 - Fixed compatibility for platform integration
  * MULTISITE-16111 - Added sniff to detect curl_init() implementations
  * MULTISITE-15711 - Added sniff to detect custom modules placed in the features folder
  * MULTISITE-15552 - Added sniff to detect internal hardcoded paths
  * MULTISITE-15576 - Enabled check:ssk by default on the review:full command
  * MULTISITE-15555 - All sniffs are covered by phpunit testing again
  * MULTISITE-16586 - Added hook_update_last_removed() to allowed install file functions
  * MULTISITE-16318 - Added hook_field_schema() to allowed install file functions
  * MULTISITE-16041 - Fixed feature files sniffs that chocked on certain arrays
