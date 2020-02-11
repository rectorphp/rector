<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\Class_\InitializeDefaultEntityCollectionRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class SkipAdded
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];

    public function __construct()
    {
        $this->marketingEvents = new ArrayCollection();
        $value = 5;
    }
}
