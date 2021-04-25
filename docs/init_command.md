# How to generate a configuration file

To start quickly you can run the init command

```bash
vendor/bin/rector init
```

This will create a `rector.php` if it doesn´t already exist in your root directory with some sensitive defaults.

```php
// rector.php
use Rector\Core\Configuration\Option;
use Rector\Php74\Rector\Property\TypedPropertyRector;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    // here we can define, what sets of rules will be applied
    $parameters = $containerConfigurator->parameters();
    $parameters->set(Option::SETS, [SetList::CODE_QUALITY]);

    // register single rule
    $services = $containerConfigurator->services();
    $services->set(TypedPropertyRector::class);
};
```

The init command takes an option called --template-type or in short form -t.
If some other Rector extension like [rector-nette](https://github.com/rectorphp/rector-nette) or [rector-doctrine](https://github.com/rectorphp/rector-doctrine) provides such a custom template type you can specify it here:

```bash
vendor/bin/rector init --template-type=nette
```

If you just want to use the default template provided by Rector you can omit the --template-type option.

# How to add a template type as a developer
In order to provide a new template type as a developer you should create a custom template class implementing the TemplateResolverInterface:

```
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
