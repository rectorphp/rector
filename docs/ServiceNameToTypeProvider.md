# Service Name to Type Provider

Some Rectors like:

```yaml
rectors:
    Rector\Rector\Contrib\Symfony\Console\CommandToConstructorInjectionRector: ~
    Rector\Rector\Contrib\Symfony\HttpKernel\GetterToPropertyRector: ~
    Rector\Rector\Contrib\Nette\Environment\GetServiceToConstructorInjectionRector: ~
```

requires `Rector\Contract\Bridge\ServiceTypeForNameProviderInterface` service that provides **service type for certain service name**.

**Why Should You Implement the Interface?**

This operation could be automated on some level, but Kernel and Container API vary too much over frameworks and their versions. The implementation is left up to you and your specific case. **This allows any framework and container to use these rectors and gives you freedom to provide own desired types if needed**.

## How to Add it?

1. Implement `Rector\Contract\Bridge\ServiceTypeForNameProviderInterface`
2. And add *name* to *type* map.

    **Static implementation** would look like this:

    ```php
    <?php declare(strict_types=1);

    use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;

    final class StaticProvidero implements ServiceTypeForNameProviderInterface
    {
        /**
         * @var string[]
         */
        private $nameToTypeMap = [
            'eventDispatcher' => 'Symfony\Component\EventDispatcher\EventDispatcherInterface',
        ];

        public function provideTypeForName(string $name): ?string
        {
            return $this->nameToTypeMap[$name] ?? null;
        }
    }
    ```

3. Register is as a service to `rector.yml

    ```yaml
    services:
        StaticProvider: ~

        # this allows autowiring via interface
        Rector\Contract\Bridge\ServiceTypeForNameProviderInterface:
            alias: StaticProvider
    ```

That's it!

## Symfony Kernel Version

Of couse we have some prepared examples for Kernel, don't you worry.

```php
use Psr\Container\ContainerInterface;
use Rector\Contract\Bridge\ServiceTypeForNameProviderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernelProvider implements ServiceTypeForNameProviderInterface
{
    /**
     * @var ContainerInterface
     */
    private $container;

    public function provideTypeForName(string $name): ?string
    {
        if (! $this->container->has($name)) {
            return null;
        }

        return $this->container->get($name);
    }

    private function getContainer(): ContainerInterface
    {
        if ($this->container) {
            return $this->container;
        }

        /** @var Kernel $kernel */
        $kernel = new $kernelClass('rector_dev', true);
        $kernel->boot();

        return $this->container = $kernel->getContainer();
    }
}
```

## Nette Environment

@tba
