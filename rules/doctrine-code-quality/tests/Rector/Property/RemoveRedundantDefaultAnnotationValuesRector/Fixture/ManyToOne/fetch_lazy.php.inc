<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\ManyToOne;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class FetchLazy
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class, fetch="LAZY")
     */
    private $trainings = [];
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\ManyToOne;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class FetchLazy
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     */
    private $trainings = [];
}

?>
