<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureInheritance;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo;

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

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureInheritance;

use Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo;

class A
{
    public function test($value): ?\Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo
    {
        return $value;
    }
}

class B extends A
{
    public function test($value): ?\Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Source\SomeFoo
    {
        return $value;
    }
}

?>
