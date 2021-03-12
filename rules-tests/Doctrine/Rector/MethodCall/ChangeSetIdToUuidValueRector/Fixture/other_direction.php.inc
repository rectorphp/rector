<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Ramsey\Uuid\Uuid;
use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class OtherDirection
{
    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setUuid(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
        $buildingFirst->setId(1);
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Ramsey\Uuid\Uuid;
use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class OtherDirection
{
    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(Uuid::fromString('a3bfab84-e207-4ddd-b96d-488151de9e96'));
    }
}

?>
