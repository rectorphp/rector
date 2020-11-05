<?php

declare(strict_types=1);

namespace Rector\Compiler\Tests\PhpScoper;

use Iterator;
use PHPUnit\Framework\TestCase;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PatchersCallbackTest extends TestCase
{
    /**
     * @var string
     */
    private const SCOPER_CONFIG_FILE_PATH = __DIR__ . '/../../../scoper.inc.php';

    /**
     * @var string
     */
    private const PREFIX = 'Prefix_';

    /**
     * @var array<string, mixed|callable>
     */
    private $scoperConfig = [];

    protected function setUp(): void
    {
        $this->scoperConfig = require self::SCOPER_CONFIG_FILE_PATH;
    }

    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fixtureFileInfo): void
    {
        $inputFileInfoAndExpected = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpected($fixtureFileInfo);

        $fileContent = $inputFileInfoAndExpected->getInputFileContent();
        foreach ($this->scoperConfig['patchers'] as $patcherCallback) {
            $fileContent = $patcherCallback($fixtureFileInfo->getRealPathWithoutSuffix(), self::PREFIX, $fileContent);
        }

        $this->assertSame($inputFileInfoAndExpected->getExpected(), $fileContent);
    }

    public function provideData(): Iterator
    {
        return StaticFixtureFinder::yieldDirectory(__DIR__ . '/Fixture');
    }
}
