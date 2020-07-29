<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFuncCallToStaticCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Renaming\Rector\FuncCall\RenameFuncCallToStaticCallRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameFuncCallToStaticCallRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfo($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameFuncCallToStaticCallRector::class => [
                RenameFuncCallToStaticCallRector::FUNCTIONS_TO_STATIC_CALLS => [
                    'strPee' => ['Strings', 'strPaa'],
                ],
            ],
        ];
    }
}
