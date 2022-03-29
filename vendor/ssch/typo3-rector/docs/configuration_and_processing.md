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

# Configuration and Processing

This library ships already with a bunch of configuration files organized by TYPO3 version.
To get you started quickly run the following command inside the root directory of your project:

```bash
./vendor/bin/rector init --template-type=typo3
```

The command generates a basic configuration skeleton which you can adapt to your needs.
The file is full of comments, so you can follow along what is going on.

Also have a look at the class [Typo3SetList](https://github.com/sabbelasichon/typo3-rector/blob/master/src/Set/Typo3SetList.php).
There you can find all the available sets you can configure in the configuration file.

To mitigate one of the most boring but also most tedious tasks, the TCA configuration, we offer dedicated sets for it.
Let´s say you want to migrate the TCA from a TYPO3 7 project to the latest TYPO3 version 11 add the following sets to your configuration file:

```php
<?php

// rector.php
declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Rector\Core\ValueObject\PhpVersion;
use Rector\PostRector\Rector\NameImportingPostRector;
use Ssch\TYPO3Rector\FileProcessor\Composer\Rector\ExtensionComposerRector;
use Ssch\TYPO3Rector\Rector\General\ConvertImplicitVariablesToExplicitGlobalsRector;
use Ssch\TYPO3Rector\Rector\General\ExtEmConfRector;
use Ssch\TYPO3Rector\Set\Typo3LevelSetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $containerConfigurator->import(Typo3LevelSetList::UP_TO_TYPO3_11);

    // FQN classes are not imported by default. If you don't do it manually after every Rector run, enable it by:
    $parameters->set(Option::AUTO_IMPORT_NAMES, true);

    // this will not import root namespace classes, like \DateTime or \Exception
    $parameters->set(Option::IMPORT_SHORT_CLASSES, false);

    // this prevents infinite loop issues due to symlinks in e.g. ".Build/" folders within single extensions
    $parameters->set(Option::FOLLOW_SYMLINKS, false);

    // this will not import classes used in PHP DocBlocks, like in /** @var \Some\Class */
    $parameters->set(Option::IMPORT_DOC_BLOCKS, false);

    // Define your target version which you want to support
    $parameters->set(Option::PHP_VERSION_FEATURES, PhpVersion::PHP_74);

    // If you only want to process one/some TYPO3 extension(s), you can specify its path(s) here.
    // If you use the option --config change __DIR__ to getcwd()
    // $parameters->set(Option::PATHS, [
    //    __DIR__ . '/packages/acme_demo/',
    // ]);

    // When you use rector there are rules that require some more actions like creating UpgradeWizards for outdated TCA types.
    // To fully support you we added some warnings. So watch out for them.

    // If you set option Option::AUTO_IMPORT_NAMES to true, you should consider excluding some TYPO3 files.
    $parameters->set(Option::SKIP, [
        NameImportingPostRector::class => [
            'ClassAliasMap.php',
            'ext_localconf.php',
            'ext_emconf.php',
            'ext_tables.php',
            __DIR__ . '/**/Configuration/TCA/*',
            __DIR__ . '/**/Configuration/RequestMiddlewares.php',
            __DIR__ . '/**/Configuration/Commands.php',
            __DIR__ . '/**/Configuration/AjaxRoutes.php',
            __DIR__ . '/**/Configuration/Extbase/Persistence/Classes.php',
        ],

        // We skip those directories on purpose as there might be node_modules or similar
        // that include typescript which would result in false positive processing
        __DIR__ . '/**/Resources/**/node_modules/*',
        __DIR__ . '/**/Resources/**/NodeModules/*',
        __DIR__ . '/**/Resources/**/BowerComponents/*',
        __DIR__ . '/**/Resources/**/bower_components/*',
        __DIR__ . '/**/Resources/**/build/*',
        __DIR__ . '/vendor/*',
        __DIR__ . '/Build/*',
        __DIR__ . '/public/*',
        __DIR__ . '/.github/*',
        __DIR__ . '/.Build/*',
    ]);

    // If you have trouble that rector cannot run because some TYPO3 constants are not defined add an additional constants file
    // Have a look at https://github.com/sabbelasichon/typo3-rector/blob/master/typo3.constants.php
    // $parameters->set(Option::AUTOLOAD_PATHS, [
    //    __DIR__ . '/typo3.constants.php'
    // ]);

    // get services (needed for register a single rule)
    $services = $containerConfigurator->services();

    // register a single rule
    // $services->set(InjectAnnotationRector::class);

    /**
     * Useful rule from RectorPHP itself to transform i.e. GeneralUtility::makeInstance('TYPO3\CMS\Core\Log\LogManager')
     * to GeneralUtility::makeInstance(\TYPO3\CMS\Core\Log\LogManager::class) calls.
     * But be warned, sometimes it produces false positives (edge cases), so watch out
     */
    // $services->set(StringClassNameToClassConstantRector::class);

    // Optional non-php file functionalities:
    // @see https://github.com/sabbelasichon/typo3-rector/blob/main/docs/beyond_php_file_processors.md

    // Adapt your composer.json dependencies to the latest available version for the defined SetList
    // $containerConfigurator->import(Typo3SetList::COMPOSER_PACKAGES_104_CORE);
    // $containerConfigurator->import(Typo3SetList::COMPOSER_PACKAGES_104_EXTENSIONS);

    // Rewrite your extbase persistence class mapping from typoscript into php according to official docs.
    // This processor will create a summarized file with all of the typoscript rewrites combined into a single file.
    // The filename can be passed as argument, "Configuration_Extbase_Persistence_Classes.php" is default.
    // $services->set(ExtbasePersistenceTypoScriptRector::class);
    // Add some general TYPO3 rules
    $services->set(ConvertImplicitVariablesToExplicitGlobalsRector::class);
    $services->set(ExtEmConfRector::class);
    $services->set(ExtensionComposerRector::class);

    // Do you want to modernize your TypoScript include statements for files and move from <INCLUDE /> to @import use the FileIncludeToImportStatementVisitor
    // $services->set(FileIncludeToImportStatementVisitor::class);
};
```

For more configuration options see [Rector README](https://github.com/rectorphp/rector#configuration).

After your adopt the configuration to your needs, run typo3-rector to simulate (hence the option `--dry-run`) the future code fixes:

```bash
./vendor/bin/rector process packages/my_custom_extension --dry-run
```

Check if everything makes sense and run the process command without the `--dry-run` option to apply the changes.

---

## Use with the --config option
If the Rector configuration is not in the main directory (e.g. /var/www/html/), the CLI option --config must be added.
If the CLI option `--config` is used, the paths in the Rector configuration file must be adapted, as this is based on the path of the rector.php file in the standard configuration.

Instead of `__DIR__` the PHP method `getcwd()` must be used. This takes the starting point for the execution of Rector.

### Example with the option --config and custom rector.php location
The file `rector.php` is located in the directory` /var/www/Build/Apps/` and it is executed
via` cd /var/www/html/ && ./vendor/bin/rector process --config ../Build/Apps/rector.php --dry-run`.
The starting point with the PHP method `getcwd()` is then `/var/www/html/` instead of `/var/www/html/Build/Apps/` with `__DIR__`.
```php
$parameters->set(
    Option::SKIP,
    [
        NameImportingPostRector::class => [
            'ClassAliasMap.php',
            'ext_localconf.php',
            'ext_emconf.php',
            'ext_tables.php',
            getcwd() . '/**/Configuration/TCA/*',
            getcwd() . '/**/Configuration/RequestMiddlewares.php',
            getcwd() . '/**/Configuration/Commands.php',
            getcwd() . '/**/Configuration/AjaxRoutes.php',
            getcwd() . '/**/Configuration/Extbase/Persistence/Classes.php'
        ]
    ]
);
```

### Example with the option --config and predefined paths in a custom rector.php location
In order to process the source files of only one TYPO3 extension it's recommended to defined said extension's path via the `Option::PATHS` parameter within the config file:
```php
// paths to refactor; solid alternative to CLI arguments
$parameters->set(
    Option::PATHS,
    [
        getcwd() . '/**/acme_demo/'
    ]
);
```
