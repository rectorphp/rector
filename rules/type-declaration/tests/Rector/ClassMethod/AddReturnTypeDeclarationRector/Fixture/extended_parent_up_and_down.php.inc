<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\FirstChildPHPUnitTestCase;

class FirstChild extends FirstChildPHPUnitTestCase
{
    public function tearDown()
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\FirstChildPHPUnitTestCase;

class FirstChild extends FirstChildPHPUnitTestCase
{
    public function tearDown(): void
    {
    }
}

?>
