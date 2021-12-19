<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="table_name", uniqueConstraints={
 *     @ORM\UniqueConstraint(name="fist"),
 *     @ORM\UniqueConstraint(name="second")
 * })
 */
class MultiNestedIndexes
{
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Doctrine;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'table_name')]
#[ORM\UniqueConstraint(name: 'fist')]
#[ORM\UniqueConstraint(name: 'second')]
class MultiNestedIndexes
{
}

?>
