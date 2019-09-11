<?php declare(strict_types=1);

namespace Rector\PHPUnit\Tests\Rector\Class_\ArrayArgumentInTestToDataProviderRector;

use Rector\PHPUnit\Rector\Class_\ArrayArgumentInTestToDataProviderRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class ArrayArgumentInTestToDataProviderRectorTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $this->doTestFiles([
            __DIR__ . '/Fixture/fixture.php.inc',
            __DIR__ . '/Fixture/various_types.php.inc',
            __DIR__ . '/Fixture/two_arguments.php.inc',
        ]);
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            ArrayArgumentInTestToDataProviderRector::class => [
                '$configuration' => [
                    [
                        'class' => 'PHPUnit\Framework\TestCase',
                        'old_method' => 'doTestMultiple',
                        'new_method' => 'doTestSingle',
                        'variable_name' => 'variable',
                    ],
                ],
            ],
        ];
    }
}
