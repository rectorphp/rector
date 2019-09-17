<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocInfo\PhpDocInfoPrinter\Source\Class_;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table(name="amenity_building", uniqueConstraints={
 *      @ORM\UniqueConstraint(name="building_id_amenity_id", columns={"building_id", "amenity_id"})
 * })
 * @ORM\Entity
 */
final class SomeEntityClass
{

}
