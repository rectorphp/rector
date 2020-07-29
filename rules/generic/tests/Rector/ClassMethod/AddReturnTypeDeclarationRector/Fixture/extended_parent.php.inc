<?php

namespace {
    use Rector\Generic\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

    abstract class Abstract_Test_Case extends PHPUnitTestCase
    {
        protected function tearDown()
        {
        }
    }
}

namespace Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture
{
    final class FinalTestCase extends \Abstract_Test_Case
    {
        public function tearDown()
        {
        }
    }
}

?>
-----
<?php

namespace {
    use Rector\Generic\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

    abstract class Abstract_Test_Case extends PHPUnitTestCase
    {
        protected function tearDown(): void
        {
        }
    }
}

namespace Rector\Generic\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture
{
    final class FinalTestCase extends \Abstract_Test_Case
    {
        public function tearDown(): void
        {
        }
    }
}

?>
