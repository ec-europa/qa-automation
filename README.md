# QA-Automation

Contains a ruleset for QualityAssurance on Drupal 8 projects.

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
