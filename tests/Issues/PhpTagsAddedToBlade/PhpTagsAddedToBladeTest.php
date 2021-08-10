<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\PhpTagsAddedToBlade;

use Iterator;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PhpTagsAddedToBladeTest extends AbstractRectorTestCase
{
    public function test(): void
    {
        $inputFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/php_tags_added_to_blade.input.php');
        $expectedFileInfo = new SmartFileInfo(__DIR__ . '/Fixture/php_tags_added_to_blade.expected.php');

        $configuration = new Configuration(isDryRun: false);
        $file = new File($inputFileInfo, $inputFileInfo->getContents());

        $applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $applicationFileProcessor->run([$file], $configuration);

        $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $file->getFileContent());
    }

    public function provideConfigFilePath(): string
    {
        return __DIR__ . '/config/php_tags_added_to_blade.php';
    }
}
