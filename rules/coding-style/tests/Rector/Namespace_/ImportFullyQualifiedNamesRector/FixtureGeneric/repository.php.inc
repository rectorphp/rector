<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

class Repository
{
    private \Doctrine\ORM\EntityManagerInterface $entityManager;

    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Generic\SomeGeneral>
     */
    private \Doctrine\ORM\EntityRepository $repository;
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Generic\SomeGeneral;
class Repository
{
    private EntityManagerInterface $entityManager;

    /**
     * @var EntityRepository<SomeGeneral>
     */
    private EntityRepository $repository;
}

?>
