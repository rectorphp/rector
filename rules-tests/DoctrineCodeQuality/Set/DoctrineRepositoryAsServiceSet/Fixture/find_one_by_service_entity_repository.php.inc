<?php

declare(strict_types=1);

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\Car;

final class FindOneByServiceEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    public function findOneByEmail(string $email): ?object
    {
        return $this->findOneBy([
            'email' => $email
        ]);
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\Car;

final class FindOneByServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\Car>
     */
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(private \Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Source\Car::class);
    }

    public function findOneByEmail(string $email): ?object
    {
        return $this->repository->findOneBy([
            'email' => $email
        ]);
    }
}

?>
