<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\Strings;
use PHPUnit\Framework\ExpectationFailedException;
use Rector\Autodiscovery\Tests\Rector\FileNode\MoveInterfacesToContractNamespaceDirectoryRector\ValueObject\InputFilePathWithExpectedFile;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\FileSystemRector\Contract\MovedFileInterface;
use Rector\Testing\Contract\RunnableInterface;
use Rector\Testing\PHPUnit\Behavior\MovingFilesTrait;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractRectorTestCase extends AbstractGenericRectorTestCase
{
    use MovingFilesTrait;

    /**
     * @var SmartFileInfo
     */
    protected $originalTempFileInfo;

    /**
     * @var bool
     */
    private $autoloadTestFixture = true;

    public function getRectorInterface(): string
    {
        return PhpRectorInterface::class;
    }

    protected function doTestFileInfoWithoutAutoload(SmartFileInfo $fileInfo): void
    {
        $this->autoloadTestFixture = false;
        $this->doTestFileInfo($fileInfo);
        $this->autoloadTestFixture = true;
    }

    /**
     * @param InputFilePathWithExpectedFile[] $extraFiles
     */
    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo, array $extraFiles = []): void
    {
        $this->fixtureGuard->ensureFileInfoHasDifferentBeforeAndAfterContent($fixtureFileInfo);

        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $fixtureFileInfo,
            $this->autoloadTestFixture
        );

        $inputFileInfo = $inputFileInfoAndExpectedFileInfo->getInputFileInfo();
        $this->nodeScopeResolver->setAnalysedFiles([$inputFileInfo->getRealPath()]);

        $expectedFileInfo = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();

        $this->doTestFileMatchesExpectedContent($inputFileInfo, $expectedFileInfo, $fixtureFileInfo, $extraFiles);

        $this->originalTempFileInfo = $inputFileInfo;

        // runnable?
        if (! file_exists($inputFileInfo->getPathname())) {
            return;
        }

        if (! Strings::contains($inputFileInfo->getContents(), RunnableInterface::class)) {
            return;
        }

        $this->assertOriginalAndFixedFileResultEquals($inputFileInfo, $expectedFileInfo);
    }

    protected function assertOriginalAndFixedFileResultEquals(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo
    ): void {
        $runnable = $this->runnableRectorFactory->createRunnableClass($originalFileInfo);
        $expectedInstance = $this->runnableRectorFactory->createRunnableClass($expectedFileInfo);

        $actualResult = $runnable->run();
        $expectedResult = $expectedInstance->run();

        $this->assertSame($expectedResult, $actualResult);
    }

    protected function getTempPath(): string
    {
        return StaticFixtureSplitter::getTemporaryPath();
    }

    protected function doTestExtraFile(string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $temporaryPath = StaticFixtureSplitter::getTemporaryPath();
        $expectedFilePath = $temporaryPath . '/' . $expectedExtraFileName;
        $this->assertFileExists($expectedFilePath);

        $this->assertFileEquals($expectedExtraContentFilePath, $expectedFilePath);
    }

    protected function getFixtureTempDirectory(): string
    {
        return sys_get_temp_dir() . '/_temp_fixture_easy_testing';
    }

    protected function doTestFileIsDeleted(SmartFileInfo $smartFileInfo): void
    {
        $this->doTestFileInfo($smartFileInfo);
        $this->assertFileMissing($this->originalTempFileInfo->getPathname());
    }

    protected function matchMovedFile(): MovedFileInterface
    {
        return $this->removedAndAddedFilesCollector->getMovedFileByFileInfo($this->originalTempFileInfo);
    }

    /**
     * @param InputFilePathWithExpectedFile[] $extraFiles
     */
    private function doTestFileMatchesExpectedContent(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo,
        SmartFileInfo $fixtureFileInfo,
        array $extraFiles = []
    ): void {
        $this->setParameter(Option::SOURCE, [$originalFileInfo->getRealPath()]);

        if ($originalFileInfo->getSuffix() === 'php') {
            if ($extraFiles === []) {
                $this->fileProcessor->parseFileInfoToLocalCache($originalFileInfo);
                $this->fileProcessor->refactor($originalFileInfo);
                $this->fileProcessor->postFileRefactor($originalFileInfo);
            } else {
                $fileInfosToProcess = [$originalFileInfo];

                foreach ($extraFiles as $extraFile) {
                    $fileInfosToProcess[] = $extraFile->getInputFileInfo();
                }

                // life-cycle trio :)
                foreach ($fileInfosToProcess as $fileInfoToProcess) {
                    $this->fileProcessor->parseFileInfoToLocalCache($fileInfoToProcess);
                }

                foreach ($fileInfosToProcess as $fileInfoToProcess) {
                    $this->fileProcessor->refactor($fileInfoToProcess);
                }

                foreach ($fileInfosToProcess as $fileInfoToProcess) {
                    $this->fileProcessor->postFileRefactor($fileInfoToProcess);
                }
            }

            // mimic post-rectors
            $changedContent = $this->fileProcessor->printToString($originalFileInfo);

            $removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);
            $removedAndAddedFilesProcessor->run();
        } elseif (in_array($originalFileInfo->getSuffix(), StaticNonPhpFileSuffixes::SUFFIXES, true)) {
            $changedContent = $this->nonPhpFileProcessor->processFileInfo($originalFileInfo);
        } else {
            $message = sprintf('Suffix "%s" is not supported yet', $originalFileInfo->getSuffix());
            throw new ShouldNotHappenException($message);
        }

        $relativeFilePathFromCwd = $fixtureFileInfo->getRelativeFilePathFromCwd();

        try {
            $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $changedContent, $relativeFilePathFromCwd);
        } catch (ExpectationFailedException $expectationFailedException) {
            $contents = $expectedFileInfo->getContents();

            StaticFixtureUpdater::updateFixtureContent($originalFileInfo, $changedContent, $fixtureFileInfo);

            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($contents, $changedContent, $relativeFilePathFromCwd);
        }
    }
}
