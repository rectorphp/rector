<?php declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;

use Rector\Configuration\Option;
use Rector\Symfony\Rector\FrameworkBundle\ContainerGetToConstructorInjectionRector;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeKernelClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ContainerGetToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $parameterProvider = self::$container->get(ParameterProvider::class);
        $parameterProvider->changeParameter(Option::KERNEL_CLASS_PARAMETER, SomeKernelClass::class);
    }

    public function test(): void
    {
        $this->doTestFiles([__DIR__ . '/Fixture/fixture.php.inc', __DIR__ . '/Fixture/fixture2.php.inc']);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [ContainerGetToConstructorInjectionRector::class => [
            '$containerAwareParentTypes' => [
                'Rector\Symfony\Tests\FrameworkBundle\ContainerGetToConstructorInjectionRector\Source\ContainerAwareParentClass',
            ],
        ]];
    }
}
