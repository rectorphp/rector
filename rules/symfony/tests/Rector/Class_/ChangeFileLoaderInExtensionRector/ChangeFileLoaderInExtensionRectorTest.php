<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Class_\ChangeFileLoaderInExtensionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Class_\ChangeFileLoaderInExtensionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeFileLoaderInExtensionRectorTest extends AbstractRectorTestCase
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
            ChangeFileLoaderInExtensionRector::class => [
                '$from' => 'xml',
                '$to' => 'yaml',
            ],
        ];
    }
}
