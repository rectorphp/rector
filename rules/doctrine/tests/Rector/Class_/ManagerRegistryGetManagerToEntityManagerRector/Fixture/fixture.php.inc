<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Doctrine\Common\Persistence\ManagerRegistry;

class CustomRepository
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
        $entityManager = $this->managerRegistry->getManager();
        $someRepository = $entityManager->getRepository('Some');
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\ManagerRegistryGetManagerToEntityManagerRector\Fixture;

use Doctrine\Common\Persistence\ManagerRegistry;

class CustomRepository
{
    public function __construct(private \Doctrine\ORM\EntityManagerInterface $entityManager)
    {
    }

    public function run()
    {
        $someRepository = $this->entityManager->getRepository('Some');
    }
}

?>
