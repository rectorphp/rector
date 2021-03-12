<?php

namespace Rector\DeadCode\Tests\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class DoctrineEntityProperty
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
class DoctrineEntityProperty
{
    /**
     * @ORM\Column
     */
    private $unusedAnnotatedProperty;
}

?>
