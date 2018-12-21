<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class Mamma
{

}

class Child extends Mamma
{

}

class KnownStaticObjectParent
{
    public function run()
    {
        $value = new Child();

        return $value;
    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture;

class Mamma
{

}

class Child extends Mamma
{

}

class KnownStaticObjectParent
{
    public function run(): \Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnScalarTypehintsRector\Fixture\Child
    {
        $value = new Child();

        return $value;
    }
}

?>
