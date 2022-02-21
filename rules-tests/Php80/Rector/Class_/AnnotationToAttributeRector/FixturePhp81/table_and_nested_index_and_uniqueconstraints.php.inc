<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\FixturePhp81;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(indexes={@ORM\Index(name="search_idx", columns={"name", "c"})}, uniqueConstraints={@ORM\UniqueConstraint(name="name_idx", columns={"name"})}]
 */
class TableAndNestedIndex
{
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\FixturePhp81;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(indexes: [new ORM\Index(name: 'search_idx', columns: ['name', 'c'])], uniqueConstraints: [new ORM\UniqueConstraint(name: 'name_idx', columns: ['name'])])]
class TableAndNestedIndex
{
}

?>
