<?php

declare(strict_types=1);

namespace Rector\Symfony\Tests\Rector\Class_\ChangeXmlToYamlFileLoaderInExtensionRector;

use Iterator;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Rector\Symfony\Rector\Class_\ChangeXmlToYamlFileLoaderInExtensionRector;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangeXmlToYamlFileLoaderInExtensionRectorTest extends AbstractRectorTestCase
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

    protected function getRectorClass(): string
    {
        return ChangeXmlToYamlFileLoaderInExtensionRector::class;
    }
}
