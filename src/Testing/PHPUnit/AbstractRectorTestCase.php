<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\Strings;
use PHPUnit\Framework\ExpectationFailedException;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Option;
use Rector\Core\Contract\Rector\PhpRectorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Testing\Contract\RunnableInterface;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractRectorTestCase extends AbstractGenericRectorTestCase
{
    /**
     * @var SmartFileInfo
     */
    protected $originalTempFileInfo;

    /**
     * @var bool
     */
    private $autoloadTestFixture = true;

    protected function doTestFileInfoWithoutAutoload(SmartFileInfo $fileInfo): void
    {
        $this->autoloadTestFixture = false;
        $this->doTestFileInfo($fileInfo);
        $this->autoloadTestFixture = true;
    }

    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo): void
    {
        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $fixtureFileInfo,
            $this->autoloadTestFixture
        );

        $inputFileInfo = $inputFileInfoAndExpectedFileInfo->getInputFileInfo();
        $this->nodeScopeResolver->setAnalysedFiles([$inputFileInfo->getRealPath()]);

        $expectedFileInfo = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();
        $this->doTestFileMatchesExpectedContent($inputFileInfo, $expectedFileInfo, $fixtureFileInfo);

        $this->originalTempFileInfo = $inputFileInfo;

        // runnable?
        if (Strings::contains($inputFileInfo->getContents(), RunnableInterface::class)) {
            $this->assertOriginalAndFixedFileResultEquals($inputFileInfo, $expectedFileInfo);
        }
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

    protected function getRectorInterface(): string
    {
        return PhpRectorInterface::class;
    }

    protected function doTestExtraFile(string $expectedExtraFileName, string $expectedExtraContentFilePath): void
    {
        $temporaryPath = StaticFixtureSplitter::getTemporaryPath();
        $expectedFilePath = $temporaryPath . '/' . $expectedExtraFileName;
        $this->assertFileExists($expectedFilePath);

        $this->assertFileEquals($expectedExtraContentFilePath, $expectedFilePath);
    }

    private function doTestFileMatchesExpectedContent(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo,
        SmartFileInfo $fixtureFileInfo
    ): void {
        $this->setParameter(Option::SOURCE, [$originalFileInfo->getRealPath()]);

        if ($originalFileInfo->getSuffix() === 'php') {
            // life-cycle trio :)
            $this->fileProcessor->parseFileInfoToLocalCache($originalFileInfo);
            $this->fileProcessor->refactor($originalFileInfo);

            $this->fileProcessor->postFileRefactor($originalFileInfo);

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
