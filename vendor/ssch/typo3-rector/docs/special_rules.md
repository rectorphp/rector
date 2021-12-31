## Table of Contents
1. [Examples in action](./examples_in_action.md)
1. [Overview of all rules](./all_rectors_overview.md)
1. [Installation](./installation.md)
1. [Configuration and Processing](./configuration_and_processing.md)
1. [Best practice guide](./best_practice_guide.md)
1. [Special rules](./special_rules.md)
1. [Beyond PHP - Entering the realm of FileProcessors](./beyond_php_file_processors.md)
1. [Limitations](./limitations.md)
1. [Contribution](./contribution.md)

# Special rules

Some of our rules require more than just executing them.

This means some further steps are necessary to fully migrate the code. We can only partly prepare the full migration, but the finishing checks have to be done by each use case.

All special rules can be included via config file (rector.php) in the following way:

```php
...
$services = $containerConfigurator->services();
$services->set(CLASSNAME);
```


## Affected rules

See each listed class with its changelog restfile and processing output for further todo's.

- \Ssch\TYPO3Rector\Rector\v11\v3\RemoveBackendUtilityViewOnClickUsageRector
