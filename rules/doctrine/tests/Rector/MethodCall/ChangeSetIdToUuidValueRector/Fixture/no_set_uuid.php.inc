<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

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

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Fixture;

use Doctrine\ORM\Mapping as ORM;
use Rector\Doctrine\Tests\Rector\MethodCall\ChangeSetIdToUuidValueRector\Source\Building;

class NoSetUuid
{
    public function run()
    {
        $car = new Building();
        $car->setId(\Ramsey\Uuid\Uuid::fromString('%s-%s-%s-%s-%s'));
    }
}

?>
