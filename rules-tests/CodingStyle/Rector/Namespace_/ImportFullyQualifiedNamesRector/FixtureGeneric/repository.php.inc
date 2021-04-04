<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

class Repository
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $entityManager;
}

?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureGeneric;

use Doctrine\ORM\EntityManagerInterface;
class Repository
{
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
}

?>
