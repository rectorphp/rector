<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsService\Fixture;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Mapping as ORM;

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

class User
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsService\Fixture;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

final class SomeServiceEntityRepository
{
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(\Doctrine\ORM\EntityManager $entityManager)
    {
        $this->repository = $entityManager->getRepository(\Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsService\Fixture\Some::class);
    }
}

/**
 * @ORM\Entity()
 */
class Some
{
}

?>
