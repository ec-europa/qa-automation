# QA Automation change log

## Version 9.0.0
DQA-4440: Drop dependency on grumphp.

## Version 8.1.8
DQA-4479: [8.x] Add date() to the forbidden functions.

## Version 8.1.7
DQA-3678: [8.x] Sniff to check form_alter() usage.

## Version 8.1.6
Automate the detection of unauthorized hook_form_alter
- DO NOT USE THIS RELEASE!

## Version 8.1.5
Exclude config folder from verification

## Version 8.1.4
Fix issue with core and core_version_requirement detection as they cannot be used together.

## Version 8.1.3
Regular maintenance (modules dependencies and tests)
Remove check of PHP version in the .info.yml files;
Check the presence of required props: name, description, type, core and core_version_requirement;

## Version 8.1.2
DQA-3473: Remove composer update.

## Version 8.1.1
Set the minimum version of GRUMPHP to 1.5

## Version 8.1.0
DQA-2680: Update minimum grumphp version.

## Version 8.0.9
DQA-0: Test severity level

## Version 8.0.8
DQA-0: Revert some config.

## Version 8.0.7
DQA-2421: Fix syntax in *-conventions.compatible.yml.

## Version 8.0.6
DQA-0: Run phpunit for QA Automation.

## Version 8.0.5
DQA-0: Run phpunit for QA Automation.

## Version 8.0.4
DQA-0: Run phpunit for QA Automation.

## Version 8.0.3
DQA-0: Run phpunit for QA Automation.

## Version 8.0.2
DQA-0: Allow old version of GRUMPHP.

## Version 8.0.1
DQA-0: Lower  grumphp version

## Version 8.0.0
Composer 2 compatible
Update PHP squizlabs/php_codesniffer from 3.5.6 to 3.6.0
Update drupal/coder from 8.3.9 to 8.3.13
Update phpro/grumphp from v0.15.2 to v1.3.3
Update phpmd/phpmd from 2.9.1 to 2.10.1
Remove dependency on openeuropa/code-review
