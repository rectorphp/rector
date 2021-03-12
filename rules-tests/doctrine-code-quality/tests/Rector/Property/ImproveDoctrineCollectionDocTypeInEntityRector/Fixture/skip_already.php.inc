<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Rector\DoctrineCodeQuality\Tests\Rector\Property\ImproveDoctrineCollectionDocTypeInEntityRector\Source\Training;

/**
 * @ORM\Entity
 */
class SkipAlready
{
    /**
     * @ORM\OneToMany(targetEntity=Training::class, mappedBy="trainer")
     * @var Collection<int, Training>|Training[]
     */
    private $trainings = [];
}
