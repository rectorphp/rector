<?php

namespace Rector\Tests\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class NoSetUuid
{
    public function run()
    {
        $car = new Building();
        $car->setId(1);
    }
}

?>
-----
<?php

namespace Rector\Tests\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Tests\Doctrine\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class NoSetUuid
{
    public function run()
    {
        $car = new Building();
        $car->setId(\Ramsey\Uuid\Uuid::fromString('%s'));
    }
}

?>
