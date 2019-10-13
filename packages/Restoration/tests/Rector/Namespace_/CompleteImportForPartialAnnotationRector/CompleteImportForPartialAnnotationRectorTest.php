<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\Namespace_\CompleteImportForPartialAnnotationRector;

use Iterator;
use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;

final class CompleteImportForPartialAnnotationRectorTest extends AbstractRectorTestCase
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
        yield [__DIR__ . '/Fixture/keep_non_annotations.php.inc'];
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            CompleteImportForPartialAnnotationRector::class => [
                '$useImportsToRestore' => [['Doctrine\ORM\Mapping', 'ORM']],
            ],
        ];
    }
}
