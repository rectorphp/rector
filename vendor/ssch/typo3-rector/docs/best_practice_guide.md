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

# Best practice guide

## What to use for

You can use typo3-rector in various ways:
- checking existing code if there are left out segments of the last upgrade
- evaluate upgrades and see what parts of your custom setup will be updated automatically
- partial execution of full core upgrades


## Guide for a good upgrade


### TLDR;

- apply older or current version rulesets first (if you're going from v8 to v10, apply v7/v8 sets first )
- composer package versions update via SetList (see [composer section below](#composer-packages-update))
- add ClassAliasMap in case you're upgrading 2 versions to provide old classes to migrate (see [ClassAliasMap](#classaliasmap))
- apply rulesets stepwise by version; first TCA only, then full set or combined
- apply rulesets stepwise to your packages or multiple packages at once
- don't use class aliases for Nimut Testing Framework (see [Migrating Testing Framework](#migrating-testing-framework))

### Starting
Starting with an upgrade should start with installing typo3-rector and checking for the rector rules/sets of your current version, not the one you're targeting.
Often there are things that were missed out in previous upgrades while rector adds rulesets for those.


So if you're on TYPO3 v8 you should start with applying the rulesets for v7 first and v8 afterwards to make sure you're current code base.

Examples for often missed out upgrade steps:
- ext:lang key changes OR the full ext:lang replacement
- TCA renderType addition on type="single"
- TCA fieldwizard OR overrideChildTCA rewrite


### Ongoing upgrade

After making sure your current code base is properly upgraded you go on with the actual upgrade process.
This requires manual action like allowing the core versions in your composer.json and ext_emconf.php files depending on your individual setup.

#### Composer packages update
We got your back with the version constraints of your packages though! For core packages but also extension packages we got a SetList that can be used to be processed and apply changes to your composer.json.

```php
use Ssch\TYPO3Rector\Set\Typo3SetList;
...
$rectorConfig->import(Typo3SetList::COMPOSER_PACKAGES_95_CORE);
$rectorConfig->import(Typo3SetList::COMPOSER_PACKAGES_95_EXTENSIONS);
```

This will add the fitting constraints for each package available on packagist - **Be aware:** The list is based on extensions that got a dependency against *typo3/cms-core*

Also: core packages that are not supported anymore (e.g. css_styled_content) will not be updated of course. This helps you to clean up a little!

#### Applying rulesets

Depending on the amount of version steps you should add the ClassAliasMap as mentioned above for e.g. v8 if you're going from v8 to v10 directly.

Once again you add you're wanted/needed rulesets that should be separated by version.
It also comes in handy to divide between TCA and TYPO3 changes AND/OR you're packages.

**The TYPO3 sets always include the TCA sets!**

TCA changes are often not that big in their impact but necessary. Also custom packages do not necesserily provide that much own TCA.
Both of that is reason to gather multiple packages for a combined TCA run with the following config:

```php
    $rectorConfig->import(Typo3SetList::TCA_95);

    $parameters->set(Option::PATHS, [
        __DIR__ . '/packages/package_numberone',
        __DIR__ . '/packages/package_thesecond',
        __DIR__ . '/packages/package_thirdone',
    ]);
```

### ClassAliasMap

The ClassAliasMap is a TYPO3 specific feature.
It is used to allow migration of no longer existing class names to new class names.
Rector is not able to load necessary ClassAliasMap on demand.
Those need to be provided via `extra` section inside `composer.json` of the project:

```json
{
    "extra": {
        "typo3/class-alias-loader": {
            "class-alias-maps": [
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/8.7/typo3/sysext/extbase/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/8.7/typo3/sysext/fluid/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/8.7/typo3/sysext/version/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/adminpanel/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/core/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/extbase/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/fluid/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/info/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/lowlevel/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/recordlist/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/reports/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/t3editor/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/9.5/typo3/sysext/workspaces/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/10.4/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/10.4/typo3/sysext/core/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/12.0/typo3/sysext/backend/Migrations/Code/ClassAliasMap.php",
                "vendor/rector/rector/vendor/ssch/typo3-rector/Migrations/TYPO3/12.0/typo3/sysext/frontend/Migrations/Code/ClassAliasMap.php"
            ]
        }
    }
}
```

Provide ClassAliasMap files of all necessary extensions for all necessary versions.

### Migrating Testing Framework

Do not use any class alias like

```php
use Nimut\TestingFramework\TestCase\FunctionalTestCase as TestCase;

abstract class AbstractContentElement extends TestCase
```

This will only work partially, resolve them beforehand:

```php
use Nimut\TestingFramework\TestCase\FunctionalTestCase;

abstract class AbstractContentElement extends FunctionalTestCase
```

---
**Be aware!**
There are limitation to the TCA detection.

Typo3-rector can only detect TCA if there's a return statement along with a 'ctrl' and 'columns' key, just like here:

```php
    return [
        'ctrl' => [],
        'columns' => [],
    ];
```

Depending on your TCA, which can include overrides with array_replace_recursive() you have to manually adapt the TCA for the time of the typo3-rector process.
So all other migrations are done for you via ruleset.

---


The non-TCA rules are often a little more specific and should be applied in a separate step with the according set, e.g. `Typo3SetList::TYPO3_95`.

Those rules bring immense value as you don't have to find the replacement of classes and the actual changelog as it is provided for you already on execution.
With `--dry-run` you can process the ruleset without applying the changes giving you a perfect overview **before** changing your code.

You can focus on testing and possibly learning the new implementation of previous functions.

## Special cases

There are changes that typo3-rector knows of but cannot fully complete.
That's why rector provides a message to inform you about changes made and necessary steps to fully migrate.

An example for this would be the TCA change of replacing `'internal_type' => 'file'` with FAL.

The TCA is changed easy, but the whole DB record type changes as previously the whole name of the file was saved into the DB column, while now with FAL, it would only create an index-count - the reference to the new files that are saved in sys_file and connected via sys_file_reference.
(see [internal_type deprecation changelog](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86406-TCATypeGroupInternal_typeFileAndFile_reference.html))

