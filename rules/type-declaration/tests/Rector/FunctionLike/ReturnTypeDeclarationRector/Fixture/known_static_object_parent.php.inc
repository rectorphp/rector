<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class Mamma
{

}

class Child extends Mamma
{

}

class KnownStaticObjectParent
{
    public function run(): \Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\Child
    {
        $value = new Child();

        return $value;
    }
}

?>
