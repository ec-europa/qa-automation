# QA-Automation

Holds all quality assurance automation tools. It currently consists of 2
parts. The PHP CodeSniffer sniffs that contain standards regarding the
FPFIS platform. And a symfony console implementation for running QA
analysis and/or reviews on Drupal projects.

## 1. Installation

### 1.1 Install with composer command
```json
composer require ec-europa/qa-automation:~4.0
```

### 1.2 Create a phpcs.xml file
```xml
<?xml version="1.0"?>
<ruleset name="Custom">
    <config name="installed_paths" value="vendor/ec-europa/qa-automation/phpcs" />
    <rule ref="QualityAssurance" />
</ruleset>
```
