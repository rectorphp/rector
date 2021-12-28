<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\PhpTagsAddedToBlade;

use Rector\ChangesReporting\Output\ConsoleOutputFormatter;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Parallel\ValueObject\Bridge;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class PhpTagsAddedToBladeTest extends AbstractRectorTestCase
{
    private ApplicationFileProcessor $applicationFileProcessor;

    protected function setUp(): void
    {
        parent::setUp();
        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
    }

    public function test(): void
    {
        $inputFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/php_tags_added_to_blade.input.php');
        $inputFileInfoContent = $inputFileInfo->getContents();

        $expectedFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/php_tags_added_to_blade.expected.php');

        $configuration = new Configuration(
            false,
            false,
            true,
            ConsoleOutputFormatter::NAME,
            ['php'],
            [__DIR__ . '/Fixture/php_tags_added_to_blade.input.php']
        );

        $systemErrorsAndFileDiffs = $this->applicationFileProcessor->run($configuration, new ArrayInput([]));

        $fileDiffs = $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS];
        $this->assertCount(1, $fileDiffs);

        $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $inputFileInfo->getContents());

        // restore original file
        $smartFileSystem = new SmartFileSystem();
        $smartFileSystem->dumpFile($inputFileInfo->getRealPath(), $inputFileInfoContent);
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/php_tags_added_to_blade.php';
    }
}
