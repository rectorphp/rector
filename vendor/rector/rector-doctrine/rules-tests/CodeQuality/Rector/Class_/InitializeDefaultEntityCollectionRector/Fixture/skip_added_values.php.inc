<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\Fixture;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * @ORM\Entity
 */
class SkipAddedValues
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];

    public function __construct(array $marketingEvents)
    {
        $this->marketingEvents = new ArrayCollection([$marketingEvents]);
        $value = 5;
    }
}
