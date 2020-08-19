<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

class Repository
{
    private \Doctrine\ORM\EntityManagerInterface $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository<\Luzanky\Security\Entity\UsersNastaveni>
     */
    private \Doctrine\ORM\EntityRepository $repository;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
class Repository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<\Luzanky\Security\Entity\UsersNastaveni>
     */
    private EntityRepository $repository;
}

?>
