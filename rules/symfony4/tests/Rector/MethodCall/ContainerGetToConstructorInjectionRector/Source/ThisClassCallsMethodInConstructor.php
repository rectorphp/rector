<?php

declare(strict_types=1);

namespace Rector\Symfony4\Tests\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source;

use Symfony\Component\DependencyInjection\ContainerInterface;

abstract class ThisClassCallsMethodInConstructor
{
    public function __construct()
    {
        $this->prepareEverything();
    }

    public function getContainer(): ContainerInterface
    {
    }

    private function prepareEverything()
    {
    }
}
