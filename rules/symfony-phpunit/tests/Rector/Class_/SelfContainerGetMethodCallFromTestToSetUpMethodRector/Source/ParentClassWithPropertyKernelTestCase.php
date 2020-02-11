<?php

declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

abstract class ParentClassWithPropertyKernelTestCase extends KernelTestCase
{
    protected $itemRepository;
}
