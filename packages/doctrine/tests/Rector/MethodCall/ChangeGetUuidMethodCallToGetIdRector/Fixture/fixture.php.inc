<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Fixture;

use Rector\Doctrine\Tests\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Source\Car;

class SomeClass
{
    public function run()
    {
        $car = new Car();

        return $car->getUuid()->toString();
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Fixture;

use Rector\Doctrine\Tests\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Source\Car;

class SomeClass
{
    public function run()
    {
        $car = new Car();

        return $car->getId()->toString();
    }
}

?>
