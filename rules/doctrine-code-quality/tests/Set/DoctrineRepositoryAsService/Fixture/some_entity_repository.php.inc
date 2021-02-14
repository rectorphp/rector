<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsService\Fixture;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

final class SomeEntityRepository extends EntityRepository
{
}

/**
 * @ORM\Entity(repositoryClass="Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsService\Fixture\SomeEntityRepository")
 */
class Some
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Tests\Set\DoctrineRepositoryAsService\Fixture;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

final class SomeEntityRepository
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
