<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class NotNullableAttribute
{
    #[ORM\Column(type: 'string', nullable: false)]
    private $surname;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class NotNullableAttribute
{
    #[ORM\Column(type: 'string', nullable: false)]
    private string $surname;
}

?>
