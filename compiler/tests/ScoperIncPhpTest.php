<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests;

use Iterator;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ScoperIncPhpTest extends TestCase
{
    /**
     * @var string
     */
    private const PREFIX = 'Prefix__';

    /**
     * @var callable[]
     */
    private $patcherCallbacks = [];

    protected function setUp(): void
    {
        $scoper = require __DIR__ . '/../build/scoper.inc.php';
        $this->patcherCallbacks = $scoper['patchers'];
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        [$inputContent, $expectedContent] = StaticFixtureSplitter::splitFileInfoToInputAndExpected($fileInfo);

        foreach ($this->patcherCallbacks as $patcherCallback) {
            $relativeFilePath = $fileInfo->getRelativeFilePathFromDirectory(__DIR__ . '/Fixture');
            $inputContent = $patcherCallback($relativeFilePath, self::PREFIX, $inputContent);
        }

        // normalize end-line spaces
        $expectedContent = rtrim($expectedContent);
        $inputContent = rtrim($inputContent);

        $this->assertSame($expectedContent, $inputContent, $fileInfo->getRelativeFilePath());
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture', '#(\.php|\.yaml|rector)$#');
    }
}
