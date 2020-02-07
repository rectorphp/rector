<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TimestampableBehaviorRector\Fixture;

use Gedmo\Timestampable\Traits\TimestampableEntity;

class SomeClass
{
    use TimestampableEntity;
}

?>
-----
<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\TimestampableBehaviorRector\Fixture;

use Gedmo\Timestampable\Traits\TimestampableEntity;

class SomeClass implements \Knp\DoctrineBehaviors\Contract\Entity\TimestampableInterface
{
    use \Knp\DoctrineBehaviors\Model\Timestampable\TimestampableTrait;
}

?>
