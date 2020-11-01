<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\ManyToMany;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class OrphanRemovalSkipped
{
    /**
     * @ORM\ManyToMany(targetEntity=Training::class, orphanRemoval=true)
     */
    private $trainings = [];
}

?>

