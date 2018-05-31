<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Tests\PerNodeTypeResolver\MethodCallTypeResolver\MethodCallSource;

final class OnSelfCall extends \Rector\NodeTypeResolver\Tests\Source\AnotherClass
{
    public function createContainer()
    {
        return $this->createContainer();
    }

    public function createAnotherContainer(): self
    {
        return $this->createAnotherContainer();
    }
}
