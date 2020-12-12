<?php

namespace Rector\Restoration\Tests\Rector\Class_\RemoveFinalFromEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
final class Fixture
{
}

?>
-----
<?php

namespace Rector\Restoration\Tests\Rector\Class_\RemoveFinalFromEntityRector\Fixture;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class Fixture
{
}

?>
