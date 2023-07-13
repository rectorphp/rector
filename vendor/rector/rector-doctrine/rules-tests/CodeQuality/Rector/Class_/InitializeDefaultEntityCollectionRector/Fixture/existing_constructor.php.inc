<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\Fixture;

/**
 * @ORM\Entity
 */
class ExistingConstructor
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];

    public function __construct()
    {
        $value = 5;
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\CodeQuality\Rector\Class_\InitializeDefaultEntityCollectionRector\Fixture;

/**
 * @ORM\Entity
 */
class ExistingConstructor
{
    /**
     * @ORM\OneToMany(targetEntity="MarketingEvent")
     */
    private $marketingEvents = [];

    public function __construct()
    {
        $this->marketingEvents = new \Doctrine\Common\Collections\ArrayCollection();
        $value = 5;
    }
}

?>
