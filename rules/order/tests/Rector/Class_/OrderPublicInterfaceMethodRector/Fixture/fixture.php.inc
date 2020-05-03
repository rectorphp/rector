<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\Fixture;

use Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\Source\FoodRecipeInterface;

class SomeClass implements FoodRecipeInterface
{
    public function process()
    {
    }

    public function getDescription()
    {
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\Fixture;

use Rector\Order\Tests\Rector\Class_\OrderPublicInterfaceMethodRector\Source\FoodRecipeInterface;

class SomeClass implements FoodRecipeInterface
{
    public function getDescription()
    {
    }
    public function process()
    {
    }
}

?>
