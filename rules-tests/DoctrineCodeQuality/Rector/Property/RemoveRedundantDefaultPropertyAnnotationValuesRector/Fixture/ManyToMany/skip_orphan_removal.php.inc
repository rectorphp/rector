<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\ManyToMany;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class SkipOrphanRemoval
{
    /**
     * @ORM\ManyToMany(targetEntity=Training::class, orphanRemoval=true)
     */
    private $trainings = [];
}

?>

