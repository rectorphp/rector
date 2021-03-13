<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\FirstChildPHPUnitTestCase;

class FirstChild extends FirstChildPHPUnitTestCase
{
    public function tearDown()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Fixture;

use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\FirstChildPHPUnitTestCase;

class FirstChild extends FirstChildPHPUnitTestCase
{
    public function tearDown(): void
    {
    }
}

?>
