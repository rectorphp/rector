<?php

declare(strict_types=1);

namespace Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Generic\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector;
use Rector\Generic\Tests\Rector\ClassConstFetch\RenameClassConstantsUseToStringsRector\Source\OldClassWithConstants;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameClassConstantsUseToStringsRectorTest extends AbstractRectorTestCase
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
            RenameClassConstantsUseToStringsRector::class => [
                RenameClassConstantsUseToStringsRector::OLD_CONSTANTS_TO_NEW_VALUES_BY_TYPE => [
                    OldClassWithConstants::class => [
                        'DEVELOPMENT' => 'development',
                        'PRODUCTION' => 'production',
                    ],
                ],
            ],
        ];
    }
}
