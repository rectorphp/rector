<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\Fixture;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\Fixture;

/**
 * @ORM\Entity
 */
class SomeClass
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];
    public function __construct()
    {
        $this->marketingEvents = new \Doctrine\Common\Collections\ArrayCollection();
    }
}

?>
