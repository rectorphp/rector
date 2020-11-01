<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Fixture\ManyToOne;

use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\RemoveRedundantDefaultAnnotationValuesRector\Source\Training;

/**
 * @ORM\Entity
 */
class FetchSkipped
{
    /**
     * @ORM\ManyToOne(targetEntity=Training::class, fetch="AUTO")
     */
    private $trainings = [];
}

?>

