<?php

declare(strict_types=1);

namespace Rector\Caching\Tests\Detector;

use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ChangedFilesDetectorTest extends AbstractRectorTestCase
{
    /**
     * @var ChangedFilesDetector
     */
    private $changedFilesDetector;

    protected function setUp(): void
    {
        parent::setUp();

        $this->changedFilesDetector = self::$container->get(ChangedFilesDetector::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        $this->changedFilesDetector->clear();
    }

    public function testHasFileChanged(): void
    {
        $smartFileInfo = $this->getSmartFileInfo();

        $this->assertTrue($this->changedFilesDetector->hasFileChanged($smartFileInfo));
        $this->changedFilesDetector->addFileWithDependencies($smartFileInfo, []);
        $this->assertFalse($this->changedFilesDetector->hasFileChanged($smartFileInfo));
    }

    public function testInvalidateFile(): void
    {
        $smartFileInfo = $this->getSmartFileInfo();

        $this->changedFilesDetector->addFileWithDependencies($smartFileInfo, []);
        $this->changedFilesDetector->invalidateFile($smartFileInfo);
        $this->assertTrue($this->changedFilesDetector->hasFileChanged($smartFileInfo));
    }

    public function testGetDependentFileInfos(): void
    {
        $smartFileInfo = $this->getSmartFileInfo();
        $dependantFile = $this->filePathToTest();

        $this->changedFilesDetector->addFileWithDependencies($smartFileInfo, [$dependantFile]);
        $dependantSmartFileInfos = $this->changedFilesDetector->getDependentFileInfos($smartFileInfo);

        $this->assertCount(1, $dependantSmartFileInfos);
        $this->assertSame($dependantFile, $dependantSmartFileInfos[0]->getPathname());
    }

    protected function provideConfigFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo(__DIR__ . '/config.php');
    }

    private function getSmartFileInfo(): SmartFileInfo
    {
        return new SmartFileInfo($this->filePathToTest());
    }

    private function filePathToTest(): string
    {
        return __DIR__ . '/Source/file.php';
    }
}
