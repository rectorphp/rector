<?php

declare(strict_types=1);

namespace Rector\Tests\Symfony4\Rector\MethodCall\ContainerGetToConstructorInjectionRector\Source;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareParentClass
{
    public function getContainer(): ContainerInterface
    {
    }
}
