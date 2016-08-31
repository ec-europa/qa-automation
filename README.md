# qa-automation
Holds all quality assurance automation tools. It currently consists of 3 parts. One
class that executes the quality assurance tests. And 2 folders that contain their
respective PHPCS sniffs. One for security sniffs and one for quality assurance
sniffs.

## QualityAssuranceTasks.php

This class will be run with the mjolnir target from the quality-assurance target.
When the build property qa.autoselect is set to 0 you have the option to run the
quality assurance tests on your selected modules only. If it is enabled it will be
run on all the modules.

Currently it provides only checks through the PHPCS it will run. The other functions
inside of the class will be used for the QA team solely as a reporting tool. But if
there are other checks that can't be added with PHPCS these will be added to the
class.

## FPFISQualityAssurance

This folder contains the code sniffs developed by the FPFIS QA team. Current included
checks are:
- check required properties in info file
- check forbidden properties in info file
- check if features api is set to version 2
- check for forbidden permissions
- check for unlocked fields
- check for date fields that are not datestamp
- check for functions that need to be drupal wrapped

## DrupalSecure

This folder contains the code sniffs of an old [drupal sandbox] (https://www.drupal.org/sandbox/coltrane/1921926).
These should be thouroughly checked because they might be out of date. We can select
usefull sniffs and move them over to the FPFISQualityAssurance sniffs. After that we
should maintain these ourselves because the sandbox is not being maintained.
