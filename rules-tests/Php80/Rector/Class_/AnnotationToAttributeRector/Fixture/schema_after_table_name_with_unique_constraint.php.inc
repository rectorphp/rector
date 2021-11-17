<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="address", schema="inpulsar", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="some_index", columns={"column_1", "column_2"}),
 * })
 */
class Address
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

#[\Doctrine\ORM\Mapping\Table(name: 'address', schema: 'inpulsar')]
#[\Doctrine\ORM\Mapping\UniqueConstraint(name: 'some_index', columns: ['column_1', 'column_2'])]
class Address
{
}

?>
