<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

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

namespace Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture;

class Mamma
{

}

class Child extends Mamma
{

}

class KnownStaticObjectParent
{
    public function run(): \Rector\Php\Tests\Rector\ClassMethod\ParamAndReturnTypeDeclarationRector\Fixture\Child
    {
        $value = new Child();

        return $value;
    }
}

?>
