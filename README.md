# QA-Automation
Holds all quality assurance automation tools. It currently consists of 2
parts. The phpcs sniffs that contain standards regarding the
FPFIS platform. And a symfony console implementation for running QA
analysis and/or reviews on subsite projects.

## Installation
Add the composer package to the require-dev section of your composer project.
After this run composer install to fetch the package and it's dependencies.

```json
"ec-europa/qa-automation": "~3.0.0"
```

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
or the starterkit you can manually add the config to your phpcs.xml file:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<ruleset name="NextEuropa_default">
  <config name="installed_paths" value="../../ec-europa/qa-automation/phpcs/SubStandards" />
  <rule ref="Subsite"/>
</ruleset>
```
