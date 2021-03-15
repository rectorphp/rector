<?php

namespace Rector\Tests\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\Tests\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector\Source\Project;

final class ProjectRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Project::class);
    }
}

?>
-----
<?php

namespace Rector\Tests\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\Tests\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector\Source\Project;

final class ProjectRepository extends ServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\Tests\Doctrine\Rector\ClassMethod\ServiceEntityRepositoryParentCallToDIRector\Source\Project>
     */
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Project::class);
    }
}

?>
