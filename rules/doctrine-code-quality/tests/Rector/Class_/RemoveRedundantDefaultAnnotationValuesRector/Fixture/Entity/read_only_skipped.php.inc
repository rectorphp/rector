<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Class_\RemoveRedundantDefaultAnnotationValuesRector\Fixture\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(readOnly=true)
 */
class ReadOnlySkipped
{
}

?>
