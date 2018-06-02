<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareParentClass
{
    public function getContainer(): ContainerInterface
    {
    }
}
