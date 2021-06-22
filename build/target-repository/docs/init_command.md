# How to generate a configuration file

To start quickly you can run the init command

```bash
vendor/bin/rector init
```

This will create a `rector.php` if it doesn't already exist in your root directory with some sensitive defaults to start with.

```php
// rector.php
use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // here we can define, what sets of rules will be applied
    $containerConfigurator->import(SetList::CODE_QUALITY);

    // register single rule
    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
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
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Rector\PostRector\Rector\NameImportingPostRector;
use Rector\Core\Configuration\Option;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(Typo3SetList::TYPO3_76);
    $containerConfigurator->import(Typo3SetList::TYPO3_87);
    $containerConfigurator->import(Typo3SetList::TYPO3_95);
    $containerConfigurator->import(Typo3SetList::TYPO3_104);
    $containerConfigurator->import(Typo3SetList::TYPO3_11);

    // get parameters
    $parameters = $containerConfigurator->parameters();
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
