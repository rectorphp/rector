<?php

declare(strict_types=1);

namespace Rector\Php74\Tests\Rector\Property\TypedPropertyRector;

use Iterator;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ClassLikeTypesOnlyTest extends AbstractRectorTestCase
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
        return $this->yieldFilesFromDirectory(__DIR__ . '/FixtureClassLikeTypeOnly');
    }

    protected function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/class_types_only.php';
    }
}
