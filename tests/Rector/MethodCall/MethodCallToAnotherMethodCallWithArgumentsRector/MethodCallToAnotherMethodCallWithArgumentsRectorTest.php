<?php declare(strict_types=1);

namespace Rector\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;

use Rector\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Tests\Rector\MethodCall\MethodCallToAnotherMethodCallWithArgumentsRector\Source\NetteServiceDefinition;

final class MethodCallToAnotherMethodCallWithArgumentsRectorTest extends AbstractRectorTestCase
{
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
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            MethodCallToAnotherMethodCallWithArgumentsRector::class => [
                '$oldMethodsToNewMethodsWithArgsByType' => [
                    NetteServiceDefinition::class => [
                        'setInject' => ['addTag', ['inject']],
                    ],
                ],
            ],
        ];
    }
}
