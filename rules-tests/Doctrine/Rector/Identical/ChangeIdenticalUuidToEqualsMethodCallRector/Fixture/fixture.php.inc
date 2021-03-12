<?php

namespace Rector\Tests\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector\Fixture;

use Rector\Tests\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector\Source\Building;

class SomeClass
{
    public function match($checkedId): int
    {
        $building = new Building();

        $isLeft = $building->getId() === $checkedId;
        $isRight = $checkedId === $building->getId();
    }
}

?>
-----
<?php

namespace Rector\Tests\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector\Fixture;

use Rector\Tests\Doctrine\Rector\Identical\ChangeIdenticalUuidToEqualsMethodCallRector\Source\Building;

class SomeClass
{
    public function match($checkedId): int
    {
        $building = new Building();

        $isLeft = $building->getId()->equals(\Ramsey\Uuid\Uuid::fromString($checkedId));
        $isRight = $building->getId()->equals(\Ramsey\Uuid\Uuid::fromString($checkedId));
    }
}

?>
