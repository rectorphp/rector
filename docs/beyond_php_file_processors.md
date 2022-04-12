# Beyond PHP - FileProcessors

You think Rector is all about PHP? You might be wrong.
Sure, the vast majority of the rules included in Rector is for PHP-Code. That´s true.

But since version 0.11.x Rector introduced the concept of so called FileProcessors.
When you are running Rector with the process command all collected files from your configured paths
are iterated through all registered FileProcessors.

Each FileProcessor must implement the [FileProcessorInterface](https://github.com/rectorphp/rector-src/blob/main/src/Contract/Processor/FileProcessorInterface.php) and must decide if it is able to handle a given file by
the [supports](https://github.com/rectorphp/rector-src/blob/main/src/Contract/Processor/FileProcessorInterface.php#L11) method or not.

Rector itself already ships with three FileProcessors. Whereas the most important one, you guessed it, is the PhpFileProcessor.

But another nice one is the ComposerFileProcessor. The ComposerFileProcessor lets you manipulate composer.json files in your project.
Let´s say you want to define a custom configuration where you want to update the version constraint of some packages.
All you have to do is using the ChangePackageVersionComposerRector:

```php
use Rector\Composer\Rector\ChangePackageVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->ruleWithConfiguration(ChangePackageVersionComposerRector::class, [
        new PackageAndVersion('symfony/yaml', '^5.0'),
    ]);
};
```

There are some more rules related to manipulate your composer.json files. Let´s see them in action:

```php
use Rector\Composer\Rector\AddPackageToRequireComposerRector;
use Rector\Composer\Rector\AddPackageToRequireDevComposerRector;
use Rector\Composer\Rector\RemovePackageComposerRector;
use Rector\Composer\Rector\ReplacePackageAndVersionComposerRector;
use Rector\Composer\ValueObject\PackageAndVersion;
use Rector\Composer\ValueObject\ReplacePackageAndVersion;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // Add a package to the require section of your composer.json
    $rectorConfig->ruleWithConfiguration(AddPackageToRequireComposerRector::class, [
        new PackageAndVersion('symfony/yaml', '^5.0'),
    ]);

    // Add a package to the require dev section of your composer.json
    $rectorConfig->ruleWithConfiguration(AddPackageToRequireDevComposerRector::class, [
        new PackageAndVersion('phpunit/phpunit', '^9.0'),
    ]);

    // Remove a package from composer.json
    $rectorConfig->ruleWithConfiguration(RemovePackageComposerRector::class, [
        'symfony/console'
    ]);

    // Replace a package in the composer.json
    $rectorConfig->ruleWithConfiguration(ReplacePackageAndVersionComposerRector::class, [
        new ReplacePackageAndVersion('vendor1/package2', 'vendor2/package1', '^3.0'),
    ]);
};
```

Behind every FileProcessor are one or multiple rules which are in turn implementing a dedicated Interface extending the [RectorInterface](https://github.com/rectorphp/rector-src/blob/main/src/Contract/Rector/RectorInterface.php).
In the case of the ComposerFileProcessor all rules are implementing the [ComposerRectorInterface](https://github.com/rectorphp/rector-src/blob/main/rules/Composer/Contract/Rector/ComposerRectorInterface.php)

Are you eager to create your own one? Dive in and have a look at [How to create a custom FileProcessor](how_to_create_custom_fileprocessor.md)


