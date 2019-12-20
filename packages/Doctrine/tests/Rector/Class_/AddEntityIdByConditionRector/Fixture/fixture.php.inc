<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Fixture;

use Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;

class SomeClass
{
    use SomeTrait;
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Fixture;

use Rector\Doctrine\Tests\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;

class SomeClass
{
    use SomeTrait;
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}

?>
