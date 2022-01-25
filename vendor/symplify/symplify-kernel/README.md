# Symplify Kernel

[![Downloads total](https://img.shields.io/packagist/dt/symplify/symplify-kernel.svg?style=flat-square)](https://packagist.org/packages/symplify/symplify-kernel/stats)

Do you use Symfony Kernel, but not for PHP projects?

Use Symfony Kernel for:

* light [Symfony Console Apps](https://tomasvotruba.com/blog/introducing-light-kernel-for-symfony-console-apps/) without Http
* faster and easy-to-setup tests
* merging of array parameters in 2 configs

## Install

```bash
composer require symplify/symplify-kernel --dev
```

## Usage

### 1. Light Kernel for Symfony CLI Apps

```php
use Psr\Container\ContainerInterface;
use Symplify\SymplifyKernel\ContainerBuilderFactory;

final class MonorepoBuilderKernel
{
    /**
     * @param string[] $configFiles
     */
    public function createFromConfigs(array $configFiles): ContainerInterface
    {
        // local config here
        $configFiles[] = __DIR__ . '/../../config/config.php';

        $containerBuilderFactory = new ContainerBuilderFactory();
        $containerBuilder = $containerBuilderFactory->create($configFiles, [], []);

        // build the container
        $containerBuilder->compile();

        return $containerBuilder;
    }
}
```

Then use in your `bin/app.php` file:

```php
$easyCIKernel = new MonorepoBuilderKernel();
$easyCIKernel->createFromConfigs([__DIR__ . '/config/config.php']);

$container = $easyCIKernel->getContainer();

/** @var Application $application */
$application = $container->get(Application::class);
exit($application->run());
```

That's it!

<br>

## Report Issues

In case you are experiencing a bug or want to request a new feature head over to the [Symplify monorepo issue tracker](https://github.com/symplify/symplify/issues)

## Contribute

The sources of this package are contained in the Symplify monorepo. We welcome contributions for this package on [symplify/symplify](https://github.com/symplify/symplify).
