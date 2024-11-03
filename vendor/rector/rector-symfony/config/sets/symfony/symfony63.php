<?php

declare (strict_types=1);
namespace RectorPrefix202411;

use Rector\Config\RectorConfig;
use Rector\Renaming\Rector\Name\RenameClassRector;
use Rector\Symfony\Symfony63\Rector\Class_\ParamAndEnvAttributeRector;
use Rector\Symfony\Symfony63\Rector\Class_\SignalableCommandInterfaceReturnTypeRector;
// @see https://github.com/symfony/symfony/blob/6.3/UPGRADE-6.3.md
// @see \Rector\Symfony\Tests\Set\Symfony63\Symfony63Test
return static function (RectorConfig $rectorConfig) : void {
    $rectorConfig->ruleWithConfiguration(RenameClassRector::class, [
        // @see https://github.com/symfony/symfony/commit/b653adf426aedc66d16c5fc1cf71e261f20b9638
        'Symfony\\Component\\DependencyInjection\\Attribute\\MapDecorated' => 'Symfony\\Component\\DependencyInjection\\Attribute\\AutowireDecorated',
        // @see https://github.com/symfony/symfony/commit/20ab567385e3812ef661dae01a1fdc5d1bde2666
        'Http\\Client\\HttpClient' => 'Psr\\Http\\Client\\ClientInterface',
        // @see https://github.com/symfony/symfony/commit/9415b438b75204c72ff66b838307b73646393cbf
        'Symfony\\Component\\Messenger\\EventListener\\StopWorkerOnSigtermSignalListener' => 'Symfony\\Component\\Messenger\\EventListener\\StopWorkerOnSignalsListener',
        // @see https://github.com/symfony/symfony/commit/a7926b2d83f35fe53c41a28d8055490cc1955928
        'Symfony\\Component\\Messenger\\Transport\\InMemoryTransport' => 'Symfony\\Component\\Messenger\\Transport\\InMemory\\InMemoryTransport',
        'Symfony\\Component\\Messenger\\Transport\\InMemoryTransportFactory' => 'Symfony\\Component\\Messenger\\Transport\\InMemory\\InMemoryTransportFactory',
    ]);
    $rectorConfig->rules([
        // @see https://github.com/symfony/symfony/commit/1650e3861b5fcd931e5d3eb1dd84bad764020d8e
        SignalableCommandInterfaceReturnTypeRector::class,
        // @see https://symfony.com/blog/new-in-symfony-6-3-dependency-injection-improvements#new-options-for-autowire-attribute
        ParamAndEnvAttributeRector::class,
    ]);
};
