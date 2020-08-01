<?php

use Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector\Source\Car;

final class AnyClass
{
    public function something()
    {
        $car = new Car();
        $car->getCarType()->getType();
    }
}

?>
-----
<?php

use Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector\Source\Car;

final class AnyClass
{
    public function something()
    {
        $car = new Car();
        $car->getType();
    }
}

?>
