# QA-Automation
Holds all quality assurance automation tools. It currently consists of 2
parts. The PHP CodeSniffer sniffs that contain standards regarding the
FPFIS platform. And a symfony console implementation for running QA
analysis and/or reviews on subsite projects.

## Installation
Add the composer package to the require-dev section of your composer project.
After this run composer install to fetch the package and it's dependencies.
Example at:

<big><pre>
["ec-europa/qa-automation": "~3.0.0"](https://github.com/ec-europa/ssk/blob/master/includes/composer/composer.json#L22)
</pre></big>

Both the platform and starterkit provide a phing task to generate a phpcs.xml
file that contains the necessary configurations to run the standards provided
by the qa-automation package. References from 

<big><pre>
https://github.com/ec-europa/ssk
https://github.com/ec-europa/ssk/blob/master/includes/build/build.test.xml#L78-L110
https://github.com/ec-europa/ssk/blob/master/src/Phing/PhpCodeSnifferConfigurationTask.php#L109-L129
https://github.com/ec-europa/ssk/blob/master/build.properties.dist#L269-L311)
</pre></big>

If you wish to use the qa-automation provided standards outside of the platform
or the starterkit you can either manually add the installed_paths configuration to:

<big><details>
    <summary>the <code>phpcs.xml</code> file located in the <code>root/</code> folder of your project.</summary>
    <p>

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ruleset name="NextEuropa_default">
  <config name="installed_paths" value="../../ec-europa/qa-automation/phpcs/SubStandards" />
  <rule ref="Subsite"/>
</ruleset>
```
</p></details>
<details>
    <summary>the <code>CodeSniffer.conf</code>file located in the <code>vendor/squizlabs/php_codesniffer/</code> folder.</summary>
    <p>

```php
<?php
 $phpCodeSnifferConfig = array (
  'default_standard' => '/var/www/html/phpcs.xml',
  'installed_paths' => '../../ec-europa/qa-automation/phpcs/SubStandards'
  'ignore_warnings_on_exit' => '0',
);
```
</p></details></big>


## Coding Standards
This package provides 4 different sets of standards.

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


## Usage

For full manual usage perform the following steps:

<big><details>
    <summary>add the installed_paths to <code>CodeSniffer.conf</code></summary>
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
