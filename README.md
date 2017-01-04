# QA-Automation
Holds all quality assurance automation tools. It currently consists of 2
parts. The custom phpcs sniffs that contain standards regarding the
FPFIS platform. And a symfony console implementation for running QA
analysis and/or reviews on subsite projects.

## 1 ./phpcs/Standards
This folder contains 2 types of phpcs sniffs. One for security and the 
other for FPFIS project specific standards.

### 1.1 ./phpcs/Standards/DrupalSecure/

This folder contains the code sniffs of an old [drupal sandbox] (https://www.drupal.org/sandbox/coltrane/1921926).
These should be thouroughly checked because they might be out of date. We can select
usefull sniffs and move them over to the QualityAssurance sniffs. After that we
should maintain these ourselves because the sandbox is not being maintained.

### 1.2 ./phpcs/Standards/QualityAssurance/

This folder contains the code sniffs developed by the FPFIS QA team. Current included
checks are:
- check required properties in info file
- check forbidden properties in info file
- check if features api is set to version 2
- check for forbidden permissions
- check for unlocked fields
- check for date fields that are not datestamp
- check for functions that need to be drupal wrapped
- check for hardcoded image tags
- check for hardcoded link tags

## 2 ./bin/qa
This is the console application which you can use to perform analysis
and review of your code. It contains various commands:
- [phpcs:xml](src/Console/Command/CheckCodingStandardsCommand.php):
Perform a phpcs run with provided phpcs.xml standard.
- [check:ssk](src/Console/Command/CheckStarterkitCommand.php): Check if
the starterkit is up to date.
- [diff:make](src/Console/Command/DiffMakeFilesCommand.php): Check make
file for changes.
- [diff:updb](src/Console/Command/DiffUpdateHooksCommand.php) : Check
make file for changes.
- [review:full](src/Console/Command/ReviewFullCommand.php): Performs all
required QA checks on the codebase.
- [review:select](src/Console/Command/ReviewSelectCommand.php): Performs
a selection of QA checks on the codebase.
- [scan:csi](src/Console/Command/ScanCodingStandardsIgnoreCommand.php): 
Scan for codingStandardsIgnore tags.
- [scan:coco](src/Console/Command/ScanCommentedCodeCommand.php): Scan
for possible commented code.
- [scan:cron](src/Console/Command/ScanCronCommand.php): Scan for cron
implementations.
- [scan:mkpd](src/Console/Command/ScanPlatformProvidedCommand.php):
Scan for platform provided modules.
- [scan:todo](src/Console/Command/ScanTodosCommand.php): Scan for
pending refractoring tasks.


1. [Cronjobs](docs/cron.md): Check codebase for cronjob and verify it is running at
requested interval.
2. [Database updates](docs/updb.md): Scanning pull request for hook_update_N's.
3. [codingStandardsIgnore](docs/codingstandardsignore.md): Skip coding standards
checks with permission of the QA team.
4. [Todo's](docs/todo.md): Request postponement of code refractoring untill the next
release.



