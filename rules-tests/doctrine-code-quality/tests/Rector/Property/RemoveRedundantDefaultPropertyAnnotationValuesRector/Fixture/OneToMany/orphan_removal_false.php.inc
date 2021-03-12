<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\OneToMany;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class OrphanRemovalFalse
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, orphanRemoval=false)
     */
    private $trainings = [];
}

?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\OneToMany;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class OrphanRemovalFalse
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class)
     */
    private $trainings = [];
}

?>
