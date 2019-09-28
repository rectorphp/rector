<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;

use Iterator;
use Rector\Configuration\Option;
use Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeKernelClass;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentCommand;
use Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ThisClassCallsMethodInConstructor;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ContainerGetToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->setParameter(Option::KERNEL_CLASS_PARAMETER, SomeKernelClass::class);
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/my_command.php.inc'];
        yield [__DIR__ . '/Fixture/first_class.php.inc'];
        yield [__DIR__ . '/Fixture/some_controller.inc'];
        yield [__DIR__ . '/Fixture/parent_class_with_in_construct_call.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ContainerGetToConstructorInjectionRector::class => [
                '$containerAwareParentTypes' => [
                    ContainerAwareParentClass::class,
                    ContainerAwareParentCommand::class,
                    ThisClassCallsMethodInConstructor::class,
                ],
            ],
        ];
    }
}
