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

# Beyond PHP Code - Entering the realm of FileProcessors

TYPO3 Rector and RectorPHP is all about PHP-Code. Well, yes and no.
Some time ago we introduced the concept of FileProcessors which can handle also non PHP files of your defined project paths.

In TYPO3 Rector specifically we have already five of them:

1. TypoScriptProcessor
1. FlexFormsProcessor
1. ComposerProcessor
1. IconsProcessor
1. FormYamlProcessor

## IconsProcessor
Let´s start with the simplest one the IconsProcessor:

The IconsProcessor takes the ext_icon.* in your extension directory and moves it under the Resources/Public/Icons/ directory with the name Extension.*

The IconsProcessor is part of the TYPO3_87 set.

## FlexFormsProcessor
The FlexFormsProcessor takes all xml files starting with the xml Node T3DataStructure and can do some modifications on it.
For now only the renderType is added in the config section if missing.

## ComposerProcessor
The ComposerProcessor takes all composer.json files of type typo3-cms-extension.
It adds an extension-key if it is missing. You can configure this Processor in your rector.php configuration file to add the typo3/cms-core dependency with the right version to your composer.json:

```php
# rector.php configuration file
use Ssch\TYPO3Rector\FileProcessor\Composer\Rector\ExtensionComposerRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/config/config.php');

    $rectorConfig->ruleWithConfiguration(ExtensionComposerRector::class, [
        ExtensionComposerRector::TYPO3_VERSION_CONSTRAINT => '^10.4'
    ]);
};
```

Also you can update all your core packages and third party packages (that are on packageist and got a dependency against typo3/cms-core) with the following SetLists:
```php
use Ssch\TYPO3Rector\Set\Typo3SetList;
...
$rectorConfig->import(Typo3SetList::COMPOSER_PACKAGES_104_CORE);
$rectorConfig->import(Typo3SetList::COMPOSER_PACKAGES_104_EXTENSIONS);
```

## FormYamlProcessor
The FormYamlProcessor only transforms the old single key value pair of the EmailFinisher to an array syntax and is part of the TYPO3_104 set.

## TypoScriptProcessor
I think this is the most powerful Processor at the moment and can transform your old conditions to the Symfony Expression Language based ones.
It takes all of your TypoScript files ending of 'typoscript', 'ts', 'txt', 'pagets', 'constantsts', 'setupts', 'tsconfig', 't3s', 't3c', 'typoscriptconstants' and typoscriptsetupts into account.
This is also configurable in your rector.php configuration file:

```php
# rector.php configuration file
use Rector\Config\RectorConfig;
use Ssch\TYPO3Rector\FileProcessor\TypoScript\TypoScriptFileProcessor;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->import(__DIR__ . '/config/config.php');

    $rectorConfig = $containerConfigurator->services();

    $rectorConfig->set(TypoScriptFileProcessor::class)
        ->call('configure', [[
            TypoScriptFileProcessor::ALLOWED_FILE_EXTENSIONS => [
                'special',
            ],
        ]]);
};
```
# Special Cases

Below we list functionalities that are based on special processors that top the typo3-rector rulesets by not only rewriting code but also creating new files etc.


## Extbase persistence class configuration rewrite

The original configuration of classes in the context of the Extbase persistence is no longer possible via typoscript.
To help you out typo3-rector adds a processor that rewrites the typoscript for you.

By adding the following configuration your code is automatically rewritten into the new PHP structure:

```php
use Ssch\TYPO3Rector\FileProcessor\TypoScript\Rector\ExtbasePersistenceTypoScriptRector;
...
$services = $containerConfigurator->services();
$services->set(ExtbasePersistenceTypoScriptRector::class);
```

Currently all of your packages are summarized into a single file.
This means you have to split the segments manually or you process the configs step by step for each package you got to create the file each time.
In this step you can also move the file and verify its functionality.

Typo3-rector **does not** create/change `Configuration/Extbase/Classes.php` to protect you from unwillingly changes on an existing file or unnoticed file creation.

---
Changelog entry: [Breaking: #87623 - Replace config.persistence.classes typoscript configuration](https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Breaking-87623-ReplaceConfigpersistenceclassesTyposcriptConfiguration.html)
