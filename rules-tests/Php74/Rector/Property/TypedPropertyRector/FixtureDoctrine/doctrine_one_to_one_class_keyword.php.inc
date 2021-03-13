<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineOneToManyClassKeywordClass
{
    /**
     * @ORM\OneToOne(targetEntity=Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private $package;
}
?>
-----
<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

class DoctrineOneToManyClassKeywordClass
{
    /**
     * @ORM\OneToOne(targetEntity=Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent::class)
     * @ORM\JoinColumn(nullable=false)
     */
    private \Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\SomeParent $package;
}
?>
