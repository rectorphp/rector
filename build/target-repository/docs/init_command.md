# How to generate a configuration file

To start quickly you can run the init command

```bash
vendor/bin/rector init
```

This will create a `rector.php` if it doesn't already exist in your root directory with some sensitive defaults to start with.

```php
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    // here we can define, what sets of rules will be applied
    $rectorConfig->sets([SetList::CODE_QUALITY]);

    // register single rule
    $rectorConfig->rule(TypedPropertyRector::class);
};
```

The init command takes an option called `--template-type`.
If some other Rector extension like [rector-nette](https://github.com/rectorphp/rector-nette) or [typo3-rector](https://github.com/sabbelasichon/typo3-rector) provides such a custom template type you can specify it here:

```bash
vendor/bin/rector init --template-type=typo3
```

The rector.php file for TYPO3 contains useful framework specific defaults to start from:

```php
use Ssch\TYPO3Rector\Set\Typo3SetList;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\Config\RectorConfig;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->sets([
        Typo3SetList::TYPO3_76,
        Typo3SetList::TYPO3_87,
        Typo3SetList::TYPO3_95,
        Typo3SetList::TYPO3_104,
        Typo3SetList::TYPO3_11,
    ]);

    $rectorConfig->skip([
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
    ]);
};
```

If you just want to use the default template provided by Rector you can omit the --template-type option.

# How to add a template type as a developer
In order to provide a new template type as a developer you should create a custom template class implementing the TemplateResolverInterface:

```php
use Rector\Core\Contract\Template\TemplateResolverInterface;

final class MyCustomTemplate implements TemplateResolverInterface
{
    /**
     * @var string
     */
    private const TYPE = 'custom';

    public function provide(): string
    {
        return __DIR__ . '/path/to/custom/template.php.dist';
    }
    public function supports(string $type): bool
    {
        return $type === self::TYPE;
    }

    public function __toString(): string
    {
        return self::TYPE;
    }
}
```
