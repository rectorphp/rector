<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests;

use Iterator;
use Nette\Utils\Strings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
use Symplify\PackageBuilder\Tests\StaticFixtureLoader;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ScopingTest extends TestCase
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
        [$content, $expectedContent] = Strings::split($fileInfo->getContents(), "#-----\n#");

        foreach ($this->patcherCallbacks as $patcherCallback) {
            $relativeFilePath = $fileInfo->getRelativeFilePathFromDirectory(__DIR__ . '/Fixture');
            $content = $patcherCallback($relativeFilePath, self::PREFIX, $content);
        }

        // normalize end-line spaces
        $expectedContent = rtrim($expectedContent);
        $content = rtrim($content);
        $this->assertSame($expectedContent, $content, $fileInfo->getRelativeFilePath());
    }

    public function provideData(): Iterator
    {
        return StaticFixtureLoader::loadFromDirectory(__DIR__ . '/Fixture');
    }
}
