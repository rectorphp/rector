<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\EntityReturningUuid;

final class ReturnUuid
{
    /**
     * @var EntityReturningUuid[]
     */
    private $amenityBuildings = [];

    /**
     * @return int[]
     */
    public function getBuildingIds(): array
    {
        $buildingIds = [];
        foreach ($this->amenityBuildings as $amenityBuilding) {
            $buildingIds[] = $amenityBuilding->getId();
        }

        return $buildingIds;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Source\EntityReturningUuid;

final class ReturnUuid
{
    /**
     * @var EntityReturningUuid[]
     */
    private $amenityBuildings = [];

    /**
     * @return \Ramsey\Uuid\UuidInterface[]
     */
    public function getBuildingIds(): array
    {
        $buildingIds = [];
        foreach ($this->amenityBuildings as $amenityBuilding) {
            $buildingIds[] = $amenityBuilding->getId();
        }

        return $buildingIds;
    }
}

?>
