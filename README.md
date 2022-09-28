# QA-Automation

[![Build Status](https://drone.fpfis.eu/api/badges/ec-europa/qa-automation/status.svg)](https://drone.fpfis.eu/ec-europa/qa-automation) 

Holds all quality assurance automation tools. It currently consists of 2
parts. The PHP CodeSniffer sniffs that contain standards regarding the
FPFIS platform. And a symfony console implementation for running QA
analysis and/or reviews on Drupal projects.

## 1. Installation

### 1.1 Install through composer.json

```json
{
  "require-dev": {
    "ec-europa/qa-automation": "^9.0"
  }
}
```

### 1.2 Install with composer command
```
composer require --dev ec-europa/qa-automation:^9.0
```

## 2. Sniff list

```
The QualityAssurance standard contains 13 sniffs

QualityAssurance (13 sniffs)
----------------------------
  QualityAssurance.Functions.DrupalDeprecated
  QualityAssurance.Functions.DrupalForbiddenHooks
  QualityAssurance.Functions.DrupalHttpRequest
  QualityAssurance.Functions.DrupalWrappers
  QualityAssurance.Generic.Credentials
  QualityAssurance.Generic.DeprecatedConstants
  QualityAssurance.Generic.HardcodedPath
  QualityAssurance.InfoFiles.Forbidden
  QualityAssurance.InfoFiles.Required
  QualityAssurance.InstallFiles.FunctionDeclarations
  QualityAssurance.InstallFiles.HookUpdate0
  QualityAssurance.InstallFiles.HookUpdateN
  QualityAssurance.InstallFiles.InstallUpdateCallbacks
```

To update this list run:
```shell
./vendor/bin/phpcs -e --standard=QualityAssurance
```
