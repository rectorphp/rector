<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\JoinColumn;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class NullableTrue
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training", nullable=true)
     */
    private $training;
}

?>
-----
<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\JoinColumn;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class NullableTrue
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training")
     */
    private $training;
}

?>
