<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class ColumnNullableAttribute
{
    #[ORM\Column(type: 'string', nullable: true)]
    private $surname;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class ColumnNullableAttribute
{
    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $surname = null;
}

?>
