<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Source\DummyManagerRegistry;

class DoNotRemoveRegistryOnNonGetRepoCall
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
        $manager = $this->managerRegistry->getManager();
        $item = $manager->getItem();

        $this->managerRegistry->kickThat();
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Source\DummyManagerRegistry;

class DoNotRemoveRegistryOnNonGetRepoCall
{
    /**
     * @var DummyManagerRegistry
     */
    private $managerRegistry;
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;

    public function __construct(DummyManagerRegistry $managerRegistry, \Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->managerRegistry = $managerRegistry;
        $this->entityManager = $entityManager;
    }

    public function run()
    {
        $item = $this->entityManager->getItem();
        $this->managerRegistry->kickThat();
    }
}

?>
