<?php

namespace Rector\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture;

use Rector\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

abstract class AbstractTestCase extends PHPUnitTestCase
{
    public function tearDown()
    {
    }
}

final class FinalTestCase extends AbstractTestCase
{
    public function tearDown()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture;

use Rector\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

abstract class AbstractTestCase extends PHPUnitTestCase
{
    public function tearDown(): void
    {
    }
}

final class FinalTestCase extends AbstractTestCase
{
    public function tearDown(): void
    {
    }
}

?>
