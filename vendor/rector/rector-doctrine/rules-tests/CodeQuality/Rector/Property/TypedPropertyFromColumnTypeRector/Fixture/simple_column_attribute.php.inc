<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class SimpleColumnAttribute
{
    #[ORM\Column(type: 'string')]
    private $name;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Property\TypedPropertyFromColumnTypeRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class SimpleColumnAttribute
{
    #[ORM\Column(type: 'string')]
    private ?string $name = null;
}

?>
