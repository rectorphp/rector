<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

final class RepositoryGeneric
{
    /**
     * @var \Doctrine\ORM\EntityRepository<\Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Generic\SomeGeneral>
     */
    private $repository;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

use Doctrine\ORM\EntityRepository;
use Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Generic\SomeGeneral;
final class RepositoryGeneric
{
    /**
     * @var EntityRepository<SomeGeneral>
     */
    private $repository;
}

?>
