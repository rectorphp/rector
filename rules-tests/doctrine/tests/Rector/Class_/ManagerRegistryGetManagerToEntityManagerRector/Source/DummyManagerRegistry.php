<?php

declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Source;

final class DummyManagerRegistry
{
    public function getManager(): DummyObjectManager
    {
        return new DummyObjectManager();
    }
}
