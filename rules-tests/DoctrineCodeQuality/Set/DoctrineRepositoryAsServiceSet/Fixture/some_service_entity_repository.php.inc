<?php

declare(strict_types=1);

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\User;

final class SomeServiceEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    public function save(User $user): void
    {
        $this->_em->persist($user);
        $this->_em->flush();
        $this->_em->clear();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\User;

final class SomeServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\User>
     */
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(private \Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(User::class);
    }

    public function save(User $user): void
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        $this->entityManager->clear();
    }
}

?>
