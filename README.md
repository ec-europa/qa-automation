# QA-Automation
Holds all quality assurance automation tools. It currently consists of 2
parts. The PHP CodeSniffer sniffs that contain standards regarding the
FPFIS platform. And a symfony console implementation for running QA
analysis and/or reviews on subsite projects.

## 1. Installation
Add the composer package to the require-dev section of your composer project.
After this run composer install to fetch the package and it's dependencies.
Example at:

<big><pre>
["ec-europa/qa-automation": "~3.0.0"](https://github.com/ec-europa/ssk/blob/master/includes/composer/composer.json#L22)
</pre></big>

## 2. Usage

### 2.1 This package provides four different sets of standards.

<big><details>
    <summary>Two internal and two external:</summary>

|Type|Provided by package|Location in package|Provided Standards|
|:---|:---|:---|:---|
|Main|[ec-europa/qa-automation](https://github.com/ec-europa/qa-automation)|[/phpcs/Standards/*](https://github.com/ec-europa/qa-automation/tree/release/3.0/phpcs/Standards)|DrupalSecure and QualityAssurance|
|Sub|[ec-europa/qa-automation](https://github.com/ec-europa/qa-automation)|[/phpcs/SubStandards/*](https://github.com/ec-europa/qa-automation/tree/release/3.0/phpcs/SubStandards)|Platform, Subsite and QA|
|Main|[drupal/coder](https://github.com/klausi/coder)|[/coder_sniffer/*](https://github.com/klausi/coder/tree/master/coder_sniffer)|Drupal and DrupalPractice|
|Main|[squizlabs/php_codesniffer](https://github.com/squizlabs/PHP_CodeSniffer)|[/src/Standards/*](https://github.com/squizlabs/PHP_CodeSniffer/tree/master/src/Standards)|PHPCS, Zend, PSR2, PSR1, MySource, PEAR and Squiz|

* Each set is either a main or sub standard:
  * Main standards contain actual sniffs and possibly ruleset.
  * Sub standards are compilations of main standards and only contain a ruleset.
</details></big>

#### 2.1.1 Full manual usage example:

<big><details>
    <summary>add installed_paths to <code>CodeSniffer.conf</code></summary>
    <p>

```php
<?php
// Put paths into array for readability.
// Using relative paths in regard to the location of this file:
// vendor/squizlabs/php_codesniffer/CodeSniffer.conf
$installedPaths = array(
   '../../drupal/coder/coder_sniffer',
   '../../ec-europa/qa-automation/phpcs/Standards',
   '../../ec-europa/qa-automation/phpcs/SubStandards',
 );
// Add the paths comma seperated to the installed_paths setting.
$phpCodeSnifferConfig = array(
  'installed_paths' => implode(',', $installedPaths),
);
```
</p></details>
<details>
    <summary>execute <code>./bin/phpcs -i</code></summary>
    <p>

```bash
The installed coding standards are PHPCS, Zend, PSR2, PSR1, MySource, PEAR, Squiz,
DrupalPractice, Drupal, QualityAssurance, DrupalSecure, QA, Platform and Subsite
```
</p></details>
<details>
    <summary>execute <code>./bin/phpcs --standard=Subsite lib/</code></summary>
    <p>

```bash
FILE: /var/www/html/lib/modules/example_module/example_module.info
----------------------------------------------------------------------
FOUND 2 ERRORS AFFECTING 1 LINE
----------------------------------------------------------------------
 1 | ERROR | "php" property is missing in the info file
 1 | ERROR | "multisite_version" property is missing in the info file
----------------------------------------------------------------------
Time: 206ms; Memory: 10Mb
```
</p></details></big>

#### 2.1.2 Configured usage example:
<big><details>
    <summary>add default_standard to <code>CodeSniffer.conf</code></summary><p>

```php
<?php
$phpCodeSnifferConfig = array(
  'default_standard' => '/var/www/html/phpcs.xml',
);
```
</p></details>
<details>
    <summary>create <code>phpcs.xml</code> file in project root</summary><p>

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ruleset name="NextEuropa_default">
  <config name="installed_paths" value="../../ec-europa/qa-automation/phpcs/SubStandards" />
  <rule ref="Subsite"/>
  <file>/var/www/html/lib</file>
</ruleset>
```
</p></details>
<details>
    <summary>execute <code>./bin/phpcs</code></summary>
    <p>

```bash
FILE: /var/www/html/lib/modules/example_module/example_module.info
----------------------------------------------------------------------
FOUND 2 ERRORS AFFECTING 1 LINE
----------------------------------------------------------------------
 1 | ERROR | "php" property is missing in the info file
 1 | ERROR | "multisite_version" property is missing in the info file
----------------------------------------------------------------------
Time: 206ms; Memory: 10Mb
```
</p></details></big>

#### 2.1.3 NextEuropa Platform usage example:
<big><details>
    <summary>execute <code>composer install</code> in your project</summary><p>

```bash

```
</p></details>
<details>
    <summary>execute <code>./bin/phing setup-php-codesniffer</code></summary><p>

```bash
Buildfile: /platform-dev/build.xml
 [property] Loading /platform-dev/build.properties.local
 [property] Loading /platform-dev/build.properties
 [property] Unable to find property file: /platform-dev/build.properties... skipped
 [property] Loading /platform-dev/build.properties.dist
     [echo] Loading Drush task.
     [echo] Loading Behat tasks.
     [echo] Loading PHP Codesniffer Configuration task.

NextEuropa > setup-php-codesniffer:

     [echo] Deleting existing PHP Codesniffer default configuration file.
   [delete] Deleting: /platform-dev/phpcs.xml
     [echo] Deleting existing PHP Codesniffer global configuration file.
   [delete] Deleting: /platform-dev/vendor/squizlabs/php_codesniffer/CodeSniffer.conf

BUILD FINISHED

Total time: 0.1581 seconds

```
</p></details>
<details>
    <summary>execute <code>./bin/phpcs</code></summary>
    <p>

```bash
.EEEEEE.E..E.E.E.WE.E..EEEEE.EEEEEEEEEEE.E.EE.EE.EEEE.WEE.EE   60 / 1224 (5%)
EE..EE.EEEEEWEEEEEE.E.EE.EEEE.EEEEE.EEEEEEE.EEEEEEEEEEE.EEE.  120 / 1224 (10%)
EEEE.E.E..EEEEEEEE..E.EEEEEEE.EEEEE.E.EE.E.EEE.EEEEEEEEEE.EE  180 / 1224 (15%)
E.EE..EE.EEEE.EEEE...EEEE..EEEE.EE.EEWEEE.E.E.EEEEEEEE.EEEE.  240 / 1224 (20%)
.E.EE.E.E.EEEEE.EEEEEEE...EEEEEEE.EEE..E.EEE.EEEEEEEEEE.EEE.  300 / 1224 (25%)
EEE....EEEEEEE......E.EE..EEEEEEEEEE.EEE..EE.EEEEEEEEEEEEEE.  360 / 1224 (29%)
EEEEE.E.EE.EEEEEE.WW.EWEEEEE..EEEEEEEE.E..E.EE.EEEEEEEEEEE.E  420 / 1224 (34%)
EEE.EE.E.EEEE.E.E.EE..E.EEE..EEEEEEEEEEEEEEE.EEEEEE.EEEEE.E.  480 / 1224 (39%)
.EEEE.EEEEEEEEE...EE.E.EEEE.E.EEEEEEEEE.EE.EE..EEEEE.EEEEEEE  540 / 1224 (44%)
EEE..EE.EEEE...E..E.EEEEEEEEEE.EEEE.WEEE.E.EE.EEE.....EEE.EE  600 / 1224 (49%)
.EEEE..EEEEEEEE.E.EEE..EEEE.EEEEEEEEEEEEEEE.EE.EEEEEEEEE.EEE  660 / 1224 (54%)
E.EE.E.E.EEE....E..EE......E.EE.EEEEEE.EEEEEW..E..EE..EEEE..  720 / 1224 (59%)
EEEEEEEEEE.E.EEEEEEEEEW.EW..E..E.EE.EE.EE.EE.E.EE....E.E.E.E  780 / 1224 (64%)
E.EEEEW.E.EEEEEEE.E.E...E.EEEEEEEEEEE.EE.EEEE.EEE.E.EEEEEEEE  840 / 1224 (69%)
EEEEEE.EEE.EE.EEEEE..EE.E.EEE.E.EEE......EE.EEE.E...E.E.E...  900 / 1224 (74%)
EEEE.EEE.E.E.EEE.EEWEE.EE.WEEEEEEEEEEEEEEEE.EEEEE.EE.EE...E.  960 / 1224 (78%)
.EEEEEE.E...E..E.W.EWEE.E.EEEEEEEE.EEEEEEEEEEEEEEEEEEEEEEEEE 1020 / 1224 (83%)
EEEEEE.WEEEE.EE......E..E.E.EE..EEEE..E..E.EE..E.EEE.E...EE. 1080 / 1224 (88%)
EEEEEEW.EEE.EEEEEEEEE......E.E.......E.E...E.E.E.E..EEE.E... 1140 / 1224 (93%)
EEEEEE.E...EEEEEEEEEEEEEEEEEEEEEEEEEEEEE..EEEE.E.EE.E..EWE.. 1200 / 1224 (98%)
EEEEEEEEEEEEEEEEEEEEEEEE

----------------------------------------------------------------------
A TOTAL OF 1890 ERRORS AND 203 WARNINGS WERE FOUND IN 877 FILES
----------------------------------------------------------------------
PHPCBF CAN FIX 1818 OF THESE SNIFF VIOLATIONS AUTOMATICALLY
----------------------------------------------------------------------

Time: 3 mins, 0.65 secs; Memory: 53.5Mb
```
</p></details></big>
