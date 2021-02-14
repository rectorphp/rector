<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Source\Car;

final class SetPasswordServiceEntityRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Car::class);
    }

    /** @param User $user */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        $user->setPassword($newEncodedPassword);
        $this->_em->flush();
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

final class SetPasswordServiceEntityRepository
{
    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsServiceSet\Source\Car>
     */
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(private \Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(Car::class);
    }

    /** @param User $user */
    public function upgradePassword(UserInterface $user, string $newEncodedPassword): void
    {
        $user->setPassword($newEncodedPassword);
        $this->entityManager->flush();
    }
}

?>
