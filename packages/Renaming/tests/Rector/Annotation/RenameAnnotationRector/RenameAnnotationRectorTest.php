<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Annotation\RenameAnnotationRector;

use Iterator;
use Rector\Renaming\Rector\Annotation\RenameAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class RenameAnnotationRectorTest extends AbstractRectorTestCase
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
            RenameAnnotationRector::class => [
                '$classToAnnotationMap' => [
                    'PHPUnit\Framework\TestCase' => [
                        'scenario' => 'test',
                    ],
                ],
            ],
        ];
    }
}
