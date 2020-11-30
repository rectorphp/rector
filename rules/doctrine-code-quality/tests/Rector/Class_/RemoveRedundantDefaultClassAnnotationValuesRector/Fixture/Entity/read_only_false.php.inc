<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=false)
 */
class ReadOnlyFalse
{
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Class_\RemoveRedundantDefaultClassAnnotationValuesRector\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity()
 */
class ReadOnlyFalse
{
}

?>
