<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Ramsey\Uuid\Uuid;
use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class WithConstant
{
    const INTEGER_CONSTANT = 1;
    const UUID_CONSTANT = 'a3bfab84-e207-4ddd-b96d-488151de9e96';

    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setUuid(Uuid::fromString(self::UUID_CONSTANT));
        $buildingFirst->setId(self::INTEGER_CONSTANT);
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Ramsey\Uuid\Uuid;
use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class WithConstant
{
    const INTEGER_CONSTANT = 1;
    const UUID_CONSTANT = 'a3bfab84-e207-4ddd-b96d-488151de9e96';

    public function run()
    {
        $buildingFirst = new Building();
        $buildingFirst->setId(Uuid::fromString(self::UUID_CONSTANT));
    }
}

?>
