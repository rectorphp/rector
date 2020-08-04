<?php

use Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector\Source\Driver;

final class AnyOtherClass
{
    public function something()
    {
        $driver = new Driver();
        $driver->getCar()->getCarType()->getType();
    }
}

?>
-----
<?php

use Rector\Generic\Tests\Rector\MethodCall\MethodCallRemoverRector\Source\Driver;

final class AnyOtherClass
{
    public function something()
    {
        $driver = new Driver();
        $driver->getCar()->getType();
    }
}

?>
