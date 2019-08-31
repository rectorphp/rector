<?php declare(strict_types=1);

namespace Rector\App;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EntityWithRelation
{
    /**
     * @ORM\ManyToOne(targetEntity="Rector\App\SomeEntityWithIntegerId", cascade={"persist", "merge"})
     * @ORM\JoinColumn(nullable=false)
     */
    private $amenity;

}
