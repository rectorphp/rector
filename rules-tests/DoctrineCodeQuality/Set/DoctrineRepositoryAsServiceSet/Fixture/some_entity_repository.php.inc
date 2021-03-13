<?php

declare(strict_types=1);

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

final class SomeEntityRepository extends EntityRepository
{
}

/**
 * @ORM\Entity(repositoryClass="Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture\SomeEntityRepository")
 */
class Some
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping as ORM;

final class SomeEntityRepository
{
    private \Doctrine\ORM\EntityRepository $repository;
    public function __construct(\Doctrine\ORM\EntityManagerInterface $entityManager)
    {
        $this->repository = $entityManager->getRepository(\Rector\Tests\DoctrineCodeQuality\Set\DoctrineRepositoryAsServiceSet\Fixture\Some::class);
    }
}

/**
 * @ORM\Entity()
 */
class Some
{
}

?>
