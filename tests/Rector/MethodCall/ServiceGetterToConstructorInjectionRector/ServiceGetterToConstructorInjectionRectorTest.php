<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;

use Iterator;
use Rector\Rector\MethodCall\ServiceGetterToConstructorInjectionRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\AnotherService;
use Rector\Tests\Rector\MethodCall\ServiceGetterToConstructorInjectionRector\Source\FirstService;

final class ServiceGetterToConstructorInjectionRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideDataForTest()
     */
    public function test(string $file): void
    {
        $this->doTestFile($file);
    }

    public function provideDataForTest(): Iterator
    {
        yield [__DIR__ . '/Fixture/fixture.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ServiceGetterToConstructorInjectionRector::class => [
                '$methodNamesByTypesToServiceTypes' => [
                    FirstService::class => [
                        'getAnotherService' => AnotherService::class,
                    ],
                ],
            ],
        ];
    }
}
