<?php

namespace Rector\Tests\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Fixture;

use Rector\Tests\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Source\Car;

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

namespace Rector\Tests\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Fixture;

use Rector\Tests\Doctrine\Rector\MethodCall\ChangeGetUuidMethodCallToGetIdRector\Source\Car;

class SomeClass
{
    public function run()
    {
        $car = new Car();

        return $car->getId()->toString();
    }
}

?>
