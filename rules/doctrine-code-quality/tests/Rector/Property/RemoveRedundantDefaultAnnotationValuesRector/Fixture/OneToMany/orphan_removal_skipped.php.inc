<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\OneToMany;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class OrphanRemovalSkipped
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, orphanRemoval=true)
     */
    private $trainings = [];
}

?>

