<?php

namespace Rector\Tests\Doctrine\Rector\Class_\AddEntityIdByConditionRector\Fixture;

use Rector\Tests\Doctrine\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;

class SomeClass
{
    use SomeTrait;
}

?>
-----
<?php

namespace Rector\Tests\Doctrine\Rector\Class_\AddEntityIdByConditionRector\Fixture;

use Rector\Tests\Doctrine\Rector\Class_\AddEntityIdByConditionRector\Source\SomeTrait;

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
