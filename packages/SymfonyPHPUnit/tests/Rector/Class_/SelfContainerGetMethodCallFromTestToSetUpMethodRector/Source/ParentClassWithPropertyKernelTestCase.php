<?php declare(strict_types=1);

namespace Rector\SymfonyPHPUnit\Tests\Rector\Class_\SelfContainerGetMethodCallFromTestToSetUpMethodRector\Source;

abstract class ParentClassWithPropertyKernelTestCase extends DummyKernelTestCase
{
    protected $itemRepository;
}
