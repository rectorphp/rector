<?php

declare(strict_types=1);

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector;

use Iterator;
use Rector\CakePHPToSymfony\Rector\Class_\CakePHPBeforeFilterToRequestEventSubscriberRector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;

final class CakePHPBeforeFilterToRequestEventSubscriberRectorExtraTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(string $inputFile, string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $this->doTestFile($inputFile);
        $this->doTestExtraFile($expectedExtraFileName, $expectedExtraContentFilePath);
    }

    public function provideData(): Iterator
    {
        yield [
            __DIR__ . '/Fixture/fixture.php.inc',
            'SuperadminControllerEventSubscriber.php',
            __DIR__ . '/Source/extra_file.php',
        ];
    }

    protected function getRectorClass(): string
    {
        return CakePHPBeforeFilterToRequestEventSubscriberRector::class;
    }
}
