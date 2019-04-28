<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector;

use Rector\Configuration\Option;
use Rector\Symfony\Rector\FrameworkBundle\GetToConstructorInjectionRector;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeKernelClass;
use Rector\Symfony\Tests\Rector\FrameworkBundle\GetToConstructorInjectionRector\Source\GetTrait;
use Rector\Symfony\Tests\Rector\Source\SymfonyController;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class GetToConstructorInjectionRectorTest extends AbstractRectorTestCase
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
        ]);
    }

    protected function getRectorClass(): string
    {
        return GetToConstructorInjectionRector::class;
    }

    /**
     * @return mixed[]
     */
    protected function getRectorConfiguration(): array
    {
        return [
            '$getMethodAwareTypes' => [SymfonyController::class, GetTrait::class],
        ];
    }
}
