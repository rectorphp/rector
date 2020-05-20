<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests;

use Iterator;
use Nette\Utils\Strings;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Finder\Finder;
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

    public function test(): void
    {
        /** @var SmartFileInfo[] $fileInfos */
        $fileInfos = self::loadFromDirectory(__DIR__ . '/Fixture/');

        foreach ($fileInfos as $fileInfo) {
            [$content, $expectedContent] = Strings::split($fileInfo->getContents(), "#-----\n#");

            foreach ($this->patcherCallbacks as $patcherCallback) {
                $relativeFilePath = $fileInfo->getRelativeFilePathFromDirectory(__DIR__ . '/Fixture');
                $content = $patcherCallback($relativeFilePath, self::PREFIX, $content);
            }

            // normalize end-line spaces
            $expectedContent = rtrim($expectedContent);
            $content = rtrim($content);
            $this->assertSame($expectedContent, $content);
        }
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    private static function loadFromDirectory(string $directory): Iterator
    {
        $finder = (new Finder())->files()
            ->in($directory);

        foreach ($finder as $fileInfo) {
            yield new SmartFileInfo($fileInfo->getRealPath());
        }
    }
}
