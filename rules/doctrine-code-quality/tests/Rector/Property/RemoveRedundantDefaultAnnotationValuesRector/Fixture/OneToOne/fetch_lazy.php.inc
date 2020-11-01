<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\OneToOne;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class FetchLazy
{
    /**
     * @ORM\OneToOne(targetEntity=Training::class, fetch="LAZY")
     */
    private $trainings = [];
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\OneToOne;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class FetchLazy
{
    /**
     * @ORM\OneToOne(targetEntity=Training::class)
     */
    private $trainings = [];
}

?>
