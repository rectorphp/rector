<?php

namespace Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\JoinColumn;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\DoctrineCodeQuality\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class SkipUnique
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class)
     * @ORM\JoinColumn(name="training", unique=true)
     */
    private $training;
}

?>
