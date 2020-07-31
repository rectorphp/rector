<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\New_\NewToStaticCallRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\New_\NewToStaticCallRector;
use Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\Source\FromNewClass;
use Rector\Generic\Tests\Rector\New_\NewToStaticCallRector\Source\IntoStaticClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class NewToStaticCallRectorTest extends AbstractRectorTestCase
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

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            NewToStaticCallRector::class => [
                NewToStaticCallRector::TYPE_TO_STATIC_CALLS => [
                    FromNewClass::class => [IntoStaticClass::class, 'run'],
                ],
            ],
        ];
    }
}
