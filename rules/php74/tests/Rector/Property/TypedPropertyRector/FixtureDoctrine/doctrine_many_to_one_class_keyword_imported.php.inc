<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

class DoctrineManyToOneClassKeywordClassImported
{
    /**
     * @ORM\ManyToOne(targetEntity=SomeParent::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $package;
}

?>
-----
<?php

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent;

class DoctrineManyToOneClassKeywordClassImported
{
    /**
     * @ORM\ManyToOne(targetEntity=SomeParent::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private \Rector\Php74\Tests\Rector\Property\TypedPropertyRector\Source\SomeParent $package;
}

?>
