<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\Class_\RenameClassRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\Renaming\Rector\Class_\RenameClassRector;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\NewClass;
use Rector\Renaming\Tests\Rector\Class_\RenameClassRector\Source\OldClass;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RenameNonPhpTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $this->doTestFileInfoWithoutAutoload($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(
            __DIR__ . '/FixtureRenameNonPhp',
            StaticNonPhpFileSuffixes::getSuffixRegexPattern()
        );
    }

    /**
     * @return mixed[]
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            RenameClassRector::class => [
                RenameClassRector::OLD_TO_NEW_CLASSES => [
                    OldClass::class => NewClass::class,
                ],
            ],
        ];
    }
}
