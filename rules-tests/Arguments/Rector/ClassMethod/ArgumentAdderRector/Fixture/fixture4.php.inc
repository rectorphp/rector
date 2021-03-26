<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Fixture;

class Value
{
    public function doSomething()
    {

    }
}

class Property
{
    public $value;
}

trait InteractsWithProperty
{
    public function doSomething(Property $property)
    {
        $property->value->doSomething();
    }
}

final class SomeExampleClass
{
    use InteractsWithProperty;
}

?>
-----
<?php

namespace Rector\Tests\Arguments\Rector\ClassMethod\ArgumentAdderRector\Fixture;

class Value
{
    public function doSomething()
    {

    }
}

class Property
{
    public $value;
}

trait InteractsWithProperty
{
    public function doSomething(Property $property)
    {
        $property->value->doSomething();
    }
}

final class SomeExampleClass
{
    use InteractsWithProperty;
}

?>
