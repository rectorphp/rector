<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SkipDoctrineEntityProperty
{
    private $unusedProperty;

    /**
     * @ORM\Column
     */
    private $unusedAnnotatedProperty;
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SkipDoctrineEntityProperty
{
    /**
     * @ORM\Column
     */
    private $unusedAnnotatedProperty;
}

?>
