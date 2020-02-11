<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Doctrine\Common\Persistence\ManagerRegistry;

class KeepDifferntMethods
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function run()
    {
        $this->managerRegistry->resetThis();
        $this->managerRegistry->kickThat();
    }
}
