<?php declare(strict_types=1);

namespace Rector\Doctrine\Tests\Rector\Class_\AddUuidMirrorForRelationPropertyRector\Source;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 */
class EntityWithoutUuidProperty
{
    /**
     * @var int
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;
}
