<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector;

use Iterator;
use Rector\Configuration\Option;
use Rector\Rector\Property\InjectAnnotationClassRector;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeKernelClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class InjectAnnotationClassRectorTest extends AbstractRectorTestCase
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
        yield [
            // JMS
            __DIR__ . '/Fixture/fixture.php.inc',
        ];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
        yield [__DIR__ . '/Fixture/fixture3.php.inc'];
        yield [__DIR__ . '/Fixture/fixture4.php.inc'];
        yield [__DIR__ . '/Fixture/fixture5.php.inc'];
        yield [
            // PHP DI
            __DIR__ . '/Fixture/inject_from_var.php.inc',
        ];
        yield [__DIR__ . '/Fixture/inject_from_var2.php.inc'];
        yield [__DIR__ . '/Fixture/inject_from_var3.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            InjectAnnotationClassRector::class => [
                '$annotationClasses' => ['JMS\DiExtraBundle\Annotation\Inject', 'DI\Annotation\Inject'],
            ],
        ];
    }
}
