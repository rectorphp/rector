<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\LoggableBehaviorRector\Fixture;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @Gedmo\Loggable
 */
class SomeClass
{
    /**
     * @Gedmo\Versioned
     * @ORM\Column(name="title", type="string", length=8)
     */
    private $title;
}

?>
-----
<?php

namespace Rector\DoctrineGedmoToKnplabs\Tests\Rector\Class_\LoggableBehaviorRector\Fixture;

use Gedmo\Mapping\Annotation as Gedmo;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass implements \Knp\DoctrineBehaviors\Contract\Entity\LoggableInterface
{
    use \Knp\DoctrineBehaviors\Model\Loggable\LoggableTrait;
    /**
     * @ORM\Column(name="title", type="string", length=8)
     */
    private $title;
}

?>
