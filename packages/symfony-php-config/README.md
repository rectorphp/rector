# Symfony PHP Config

Tools that easy work with Symfony PHP Configs.

Read [How to Inline Value Object in Symfony PHP Config](https://getrector.org/blog/2020/09/07/how-to-inline-value-object-in-symfony-php-config) to learn more.

## 1. Install

```bash
composer require rector/symfony-php-config
```

## 2. Usage

Do you want to use value objects in Symfony configs?

Use `Rector\SymfonyPhpConfig\inline_value_objects` function:

```php
<?php

declare(strict_types=1);

use function Rector\SymfonyPhpConfig\inline_value_objects;
use Rector\Generic\Rector\FuncCall\FuncCallToStaticCallRector;
use Rector\Transform\ValueObject\FuncCallToStaticCall;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(FuncCallToStaticCallRector::class)
        ->call('configure', [[
            FuncCallToStaticCallRector::FUNC_CALLS_TO_STATIC_CALLS => inline_value_objects([
                new FuncCallToStaticCall('dump', 'Tracy\Debugger', 'dump'),
                // it handles multiple items without duplicated call
                new FuncCallToStaticCall('d', 'Tracy\Debugger', 'dump'),
                new FuncCallToStaticCall('dd', 'Tracy\Debugger', 'dump'),
        ])
    ]]);
};
```
