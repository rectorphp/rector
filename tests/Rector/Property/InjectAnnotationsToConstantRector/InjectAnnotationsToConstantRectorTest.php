<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Property\InjectAnnotationsToConstantRector;

use Rector\Configuration\Option;
use Rector\Rector\Property\InjectAnnotationsToConstantRector;
use Rector\Symfony\Tests\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeKernelClass;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class InjectAnnotationsToConstantRectorTest extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        /** @var ParameterProvider $parameterProvider */
        $parameterProvider = self::$container->get(ParameterProvider::class);
        $parameterProvider->changeParameter(Option::KERNEL_CLASS_PARAMETER, SomeKernelClass::class);
    }

    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/fixture_with_parent_class.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            InjectAnnotationsToConstantRector::class => [
                '$annotationClasses' => ['Inject'],
                '$constantNameThatContainsDependencyMap' => 'DEPENDENCIES',
            ],
        ];
    }
}
