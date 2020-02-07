<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

class Mamma
{

}

class Child extends Mamma
{

}

class KnownStaticObjectParent
{
    public function run(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Child
    {
        $value = new Child();

        return $value;
    }
}

?>
