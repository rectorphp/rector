<?php

namespace Rector\Tests\Restoration\Rector\Class_\RemoveFinalFromEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class SomeClass
{
}

?>
-----
<?php

namespace Rector\Tests\Restoration\Rector\Class_\RemoveFinalFromEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class SomeClass
{
}

?>
