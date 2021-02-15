<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Source\Car;

final class CreateQueryServiceEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    public function findOneById(int $id): ?object
    {
        return $this->find($id);
    }

    public function findByPasswordRequestToken(): ?array
    {
        $query = $this->_em->createQuery('SELECT u FROM App\Person\User\Domain\Model\User u WHERE u.passwordRequestToken != ?1');
        $query->setParameter(1, '');
        return $query->getResult();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Source\Car;

final class CreateQueryServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Source\Car>
     */
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(private \Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Car::class);
    }

    public function findOneById(int $id): ?object
    {
        return $this->repository->find($id);
    }

    public function findByPasswordRequestToken(): ?array
    {
        $query = $this->entityManager->createQuery('SELECT u FROM App\Person\User\Domain\Model\User u WHERE u.passwordRequestToken != ?1');
        $query->setParameter(1, '');
        return $query->getResult();
    }
}

?>
