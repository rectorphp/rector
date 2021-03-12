<?php

namespace Rector\Tests\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector\Fixture;

use Rector\Tests\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector\Source\Building;

class SomeClass
{
    public function getBuildingId(): int
    {
        $building = new Building();

        return $building->getId();
    }
}

?>
-----
<?php

namespace Rector\Tests\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector\Fixture;

use Rector\Tests\Doctrine\Rector\ClassMethod\ChangeReturnTypeOfClassMethodWithGetIdRector\Source\Building;

class SomeClass
{
    public function getBuildingId(): \Ramsey\Uuid\UuidInterface
    {
        $building = new Building();

        return $building->getId();
    }
}

?>
