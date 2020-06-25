<?php

declare(strict_types=1);

namespace Rector\Restoration\Tests\Rector\Namespace_\CompleteImportForPartialAnnotationRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Restoration\Rector\Namespace_\CompleteImportForPartialAnnotationRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class CompleteImportForPartialAnnotationRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $file): void
    {
        $this->doTestFileInfo($file);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
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
