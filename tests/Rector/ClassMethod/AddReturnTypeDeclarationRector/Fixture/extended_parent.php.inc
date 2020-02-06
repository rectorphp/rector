<?php

namespace {
    use Rector\Core\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

    abstract class Abstract_Test_Case extends PHPUnitTestCase
    {
        protected function tearDown()
        {
        }
    }
}

namespace Rector\Core\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture
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
    use Rector\Core\Tests\Rector\ClassMethod\AddReturnTypeDeclarationRector\Source\PHPUnitTestCase;

    abstract class Abstract_Test_Case extends PHPUnitTestCase
    {
        protected function tearDown(): void
        {
        }
    }
}

namespace Rector\Core\Tests\Rector\Typehint\AddReturnTypeDeclarationRector\Fixture
{
    final class FinalTestCase extends \Abstract_Test_Case
    {
        public function tearDown(): void
        {
        }
    }
}

?>
