<?php declare(strict_types=1);

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector;

use Rector\Configuration\Option;
use Rector\Jms\Rector\Property\JmsInjectAnnotationRector;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeKernelClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class JmsInjectAnnotationRectorTest extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $parameterProvider = self::$container->get(ParameterProvider::class);
        $parameterProvider->changeParameter(Option::KERNEL_CLASS_PARAMETER, SomeKernelClass::class);
    }

    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture2.php.inc',
            __DIR__ . '/Fixture/fixture3.php.inc',
            __DIR__ . '/Fixture/fixture4.php.inc',
            __DIR__ . '/Fixture/fixture5.php.inc',
        ]);
    }

    protected function getRectorClass(): string
    {
        return JmsInjectAnnotationRector::class;
    }
}
