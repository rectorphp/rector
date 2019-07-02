<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Source\DummyManagerRegistry;

class KeepDifferntMethods
{
    /**
     * @var DummyManagerRegistry
     */
    private $managerRegistry;

    public function __construct(DummyManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function run()
    {
        $this->managerRegistry->resetThis();
        $this->managerRegistry->kickThat();
    }
}
