<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source;

final class ManyImports
{
    public function filter(Query $query)
    {
        /** @var Source\Stock\Query $query */
        $query = 5;

        if (!($query instanceof Source\Stock\Querying\Query)) {
            throw new \InvalidArgumentException('Wrong query provided');
        }

        /** @var Source\Stock\Querying\Query $query */
        $builder = $this->createBuilder();
        $query->build($builder);
    }

    public function multiDoc()
    {
        /** @var \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Connection $connection */
        $connection = $this->registry->getConnection();

        /** @var \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Connection $connection */
        $connection = $this->registry->getConnection();
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

use InvalidArgumentException;
use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\Connection;
use Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source;

final class ManyImports
{
    public function filter(Query $query)
    {
        /** @var Source\Stock\Query $query */
        $query = 5;

        if (!($query instanceof Source\Stock\Querying\Query)) {
            throw new InvalidArgumentException('Wrong query provided');
        }

        /** @var Source\Stock\Querying\Query $query */
        $builder = $this->createBuilder();
        $query->build($builder);
    }

    public function multiDoc()
    {
        /** @var Connection $connection */
        $connection = $this->registry->getConnection();

        /** @var Connection $connection */
        $connection = $this->registry->getConnection();
    }
}

?>
