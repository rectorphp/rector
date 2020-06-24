<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source\ClassWithStaticMethods;
use PHPUnit\Framework\TestCase;

final class SetupAlreadyExistsTest extends TestCase
{
    /**
     * @var string
     */
    private $legitimation;

    protected function setUp(): void
    {
        $this->legitimation = 'GO';
    }

    public function test()
    {
        ClassWithStaticMethods::create('product');
    }
}

?>
-----
<?php

namespace Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Fixture;

use Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source\ClassWithStaticMethods;
use PHPUnit\Framework\TestCase;

final class SetupAlreadyExistsTest extends \Symfony\Bundle\FrameworkBundle\Test\KernelTestCase
{
    private \Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source\ClassWithStaticMethods $classWithStaticMethods;
    /**
     * @var string
     */
    private $legitimation;

    protected function setUp(): void
    {
        parent::setUp();
        $this->classWithStaticMethods = self::$container->get(\Rector\RemovingStatic\Tests\Rector\Class_\PHPUnitStaticToKernelTestCaseGetRector\Source\ClassWithStaticMethods::class);
        $this->legitimation = 'GO';
    }

    public function test()
    {
        $this->classWithStaticMethods->create('product');
    }
}

?>
