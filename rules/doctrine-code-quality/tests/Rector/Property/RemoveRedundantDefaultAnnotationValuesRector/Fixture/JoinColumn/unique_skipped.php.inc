<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\JoinColumn;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class UniqueSkipped
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training", unique=true)
     */
    private $training;
}

?>
