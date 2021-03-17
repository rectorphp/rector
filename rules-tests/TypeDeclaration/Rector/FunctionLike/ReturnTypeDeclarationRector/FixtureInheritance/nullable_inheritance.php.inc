<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureInheritance;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo;

class A
{
    /**
     * @return null|SomeFoo
     */
    public function test($value)
    {
        return $value;
    }
}

class B extends A
{
    public function test($value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureInheritance;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo;

class A
{
    public function test($value): ?\Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo
    {
        return $value;
    }
}

class B extends A
{
    public function test($value): ?\Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo
    {
        return $value;
    }
}

?>
