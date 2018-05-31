<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Contrib\Symfony\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source;

use Symfony\Component\DependencyInjection\ContainerInterface;

class ContainerAwareParentClass
{
    public function getContainer(): ContainerInterface
    {
    }
}
