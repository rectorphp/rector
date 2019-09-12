<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\InjectAnnotationClassRector;

use Rector\Configuration\Option;
use Rector\Rector\Property\InjectAnnotationClassRector;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeKernelClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class InjectAnnotationClassRectorTest extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ParameterProvider $parameterProvider */
        $parameterProvider = self::$container->get(ParameterProvider::class);
        $parameterProvider->changeParameter(Option::KERNEL_CLASS_PARAMETER, SomeKernelClass::class);
    }

    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    /**
     * @return string[]
     */
    public function provideDataForTest(): iterable
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
