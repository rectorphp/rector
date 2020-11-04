<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Fixture\OneToOne;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultPropertyAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class FetchSkipped
{
    /**
     * @ORM\OneToOne(targetEntity=Training::class, fetch="AUTO")
     */
    private $trainings = [];
}

?>

