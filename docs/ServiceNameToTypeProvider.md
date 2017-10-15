# Service Name to Type Provider

Some Rectors like:

```yaml
rectors:
    Rector\Rector\Contrib\Symfony\Console\CommandToConstructorInjectionRector: ~
    Rector\Rector\Contrib\Symfony\HttpKernel\GetterToPropertyRector: ~
    Rector\Rector\Contrib\Nette\Environment\GetServiceToConstructorInjectionRector: ~
```

requires `Rector\Contract\Bridge\ServiceNameToTypeProviderInterface` service that provides **service type for certain service name**.


**Why Interface?**

This operation could be automated to some level, but Kernel and Container API vary over time, so the implementation is left up to you and your specific case. This allows any framework and container to use these rectors and gives you freedom to provide own types.


## How to Add it?

1. Implement `Rector\Contract\Bridge\ServiceNameToTypeProviderInterface`
2. And add *name* to *type* map.

    **Static implementation** would look like this:

    ```yaml
    <?php declare(strict_types=1);

    use Rector\Contract\Bridge\ServiceNameToTypeProviderInterface;

    final class StaticProvidero implements ServiceNameToTypeProviderInterface
    {
        /**
         * @return string[]
         */
        public function provide(): array
        {
            return [
                'eventDispatcher' => 'Symfony\Component\EventDispatcher\EventDispatcherInterface',
            ];
        }
    }
    ```

3. Register is as a service to `rector.yml

    ```yaml
    services:
        StaticProvider: ~

        # this allows autowiring via interface
        Rector\Contract\Bridge\ServiceNameToTypeProviderInterface:
            alias: StaticProvider
    ```

That's it!



## Symfony Kernel Version

Of couse we have some prepared examples for Kernel, don't you worry.


```php
use Psr\Container\ContainerInterface;
use Rector\Contract\Bridge\ServiceNameToTypeProviderInterface;
use Symfony\Component\HttpKernel\Kernel;

final class AppKernelProvider implements ServiceNameToTypeProviderInterface
{
    // @todo: modify API so it matches

    // https://github.com/RectorPHP/Rector/pull/86/commits/0e375e713b3fea3a990762d5f117a019d317e67e#diff-4f9e06675af869311a8729c450e01d2eL26

    public function getTypeForName(string $name)
    {

    }

    /**
     * @return string[]
     */
    public function provide(): array
    {
    }

    private function getContainer(): ContainerInterface
    {
        // @todo: cache it

        /** @var Kernel $kernel */
        $kernel = new $kernelClass('rector_dev', true);
        $kernel->boot();

        return $kernel->getContainer();
    }
}
```



## Nette Environment

@tba