<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Doctrine\Common\Persistence\ManagerRegistry;

class DoNotRemoveRegistryOnNonGetRepoCall
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
        $manager = $this->managerRegistry->getManager();
        $item = $manager->getItem();

        $this->managerRegistry->kickThat();
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Doctrine\Common\Persistence\ManagerRegistry;

class DoNotRemoveRegistryOnNonGetRepoCall
{
    /**
     * @var ManagerRegistry
     */
    private $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry, private \Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function run()
    {
        $item = $this->entityManager->getItem();

        $this->managerRegistry->kickThat();
    }
}

?>
