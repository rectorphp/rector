<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector;

use Rector\Configuration\Option;
use Rector\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector;
use Rector\Rector\Architecture\DependencyInjection\ReplaceVariableByPropertyFetchRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\Architecture\DependencyInjection\ActionInjectionToConstructorInjectionRector\Source\SomeKernelClass;
use Symplify\PackageBuilder\Parameter\ParameterProvider;

final class ActionInjectionToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

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
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
        yield [__DIR__ . '/Fixture/fixture2.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ActionInjectionToConstructorInjectionRector::class => [],
            ReplaceVariableByPropertyFetchRector::class => [],
        ];
    }
}
