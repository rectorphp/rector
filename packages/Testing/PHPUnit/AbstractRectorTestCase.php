<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Iterator;
use Nette\Utils\Strings;
use PHPStan\Analyser\NodeScopeResolver;
use PHPUnit\Framework\ExpectationFailedException;
use Psr\Container\ContainerInterface;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesCollector;
use Rector\Core\Bootstrap\RectorConfigsResolver;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Configuration\Option;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\PhpParser\Printer\BetterStandardPrinter;
use Rector\Core\ValueObject\Application\File;
use Rector\NodeTypeResolver\Reflection\BetterReflection\SourceLocatorProvider\DynamicSourceLocatorProvider;
use Rector\Testing\Contract\RectorTestInterface;
use Rector\Testing\PHPUnit\Behavior\MovingFilesTrait;
use Symplify\EasyTesting\DataProvider\StaticFixtureFinder;
use Symplify\EasyTesting\DataProvider\StaticFixtureUpdater;
use Symplify\EasyTesting\StaticFixtureSplitter;
use Symplify\PackageBuilder\Parameter\ParameterProvider;
use Symplify\PackageBuilder\Testing\AbstractKernelTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractRectorTestCase extends AbstractKernelTestCase implements RectorTestInterface
{
    use MovingFilesTrait;

    /**
     * @var ParameterProvider
     */
    protected $parameterProvider;

    /**
     * @var RemovedAndAddedFilesCollector
     */
    protected $removedAndAddedFilesCollector;

    /**
     * @var SmartFileInfo
     */
    protected $originalTempFileInfo;

    /**
     * @var ContainerInterface|null
     */
    protected static $allRectorContainer;

    /**
     * @var BetterStandardPrinter
     */
    private $betterStandardPrinter;

    /**
     * @var DynamicSourceLocatorProvider
     */
    private $dynamicSourceLocatorProvider;

    /**
     * @var ApplicationFileProcessor
     */
    private $applicationFileProcessor;

    protected function setUp(): void
    {
        // speed up
        @ini_set('memory_limit', '-1');

        $configFileInfo = new SmartFileInfo($this->provideConfigFilePath());
        $rectorConfigsResolver = new RectorConfigsResolver();
        $configFileInfos = $rectorConfigsResolver->resolveFromConfigFileInfo($configFileInfo);

        $this->bootKernelWithConfigsAndStaticCache(RectorKernel::class, $configFileInfos);

        $this->applicationFileProcessor = $this->getService(ApplicationFileProcessor::class);
        $this->parameterProvider = $this->getService(ParameterProvider::class);
        $this->betterStandardPrinter = $this->getService(BetterStandardPrinter::class);
        $this->dynamicSourceLocatorProvider = $this->getService(DynamicSourceLocatorProvider::class);

        $this->removedAndAddedFilesCollector = $this->getService(RemovedAndAddedFilesCollector::class);
        $this->removedAndAddedFilesCollector->reset();

        /** @var Configuration $configuration */
        $configuration = $this->getService(Configuration::class);
        $configuration->setIsDryRun(true);
    }

    public function provideConfigFilePath(): string
    {
        // must be implemented
        return '';
    }

    /**
     * @return Iterator<SmartFileInfo>
     */
    protected function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc'): Iterator
    {
        return StaticFixtureFinder::yieldDirectoryExclusively($directory, $suffix);
    }

    protected function doTestFileInfo(SmartFileInfo $fixtureFileInfo): void
    {
        $inputFileInfoAndExpectedFileInfo = StaticFixtureSplitter::splitFileInfoToLocalInputAndExpectedFileInfos(
            $fixtureFileInfo
        );

        $inputFileInfo = $inputFileInfoAndExpectedFileInfo->getInputFileInfo();

        $expectedFileInfo = $inputFileInfoAndExpectedFileInfo->getExpectedFileInfo();
        $this->doTestFileMatchesExpectedContent($inputFileInfo, $expectedFileInfo, $fixtureFileInfo);

        $this->originalTempFileInfo = $inputFileInfo;
    }

    protected function getFixtureTempDirectory(): string
    {
        return sys_get_temp_dir() . '/_temp_fixture_easy_testing';
    }

    private function doTestFileMatchesExpectedContent(
        SmartFileInfo $originalFileInfo,
        SmartFileInfo $expectedFileInfo,
        SmartFileInfo $fixtureFileInfo
    ): void {
        $this->parameterProvider->changeParameter(Option::SOURCE, [$originalFileInfo->getRealPath()]);

        $changedContent = $this->processFileInfo($originalFileInfo);

        // file is removed, we cannot compare it
        if ($this->removedAndAddedFilesCollector->isFileRemoved($originalFileInfo)) {
            return;
        }

        $relativeFilePathFromCwd = $fixtureFileInfo->getRelativeFilePathFromCwd();

        try {
            $this->assertStringEqualsFile($expectedFileInfo->getRealPath(), $changedContent, $relativeFilePathFromCwd);
        } catch (ExpectationFailedException $expectationFailedException) {
            StaticFixtureUpdater::updateFixtureContent($originalFileInfo, $changedContent, $fixtureFileInfo);
            $contents = $expectedFileInfo->getContents();

            // make sure we don't get a diff in which every line is different (because of differences in EOL)
            $contents = $this->normalizeNewlines($contents);

            // if not exact match, check the regex version (useful for generated hashes/uuids in the code)
            $this->assertStringMatchesFormat($contents, $changedContent, $relativeFilePathFromCwd);
        }
    }

    private function normalizeNewlines(string $string): string
    {
        return Strings::replace($string, '#\r\n|\r|\n#', "\n");
    }

    private function processFileInfo(SmartFileInfo $fileInfo): string
    {
        $this->dynamicSourceLocatorProvider->setFileInfo($fileInfo);

        // needed for PHPStan, because the analyzed file is just created in /temp - need for trait and similar deps
        /** @var NodeScopeResolver $nodeScopeResolver */
        $nodeScopeResolver = $this->getService(NodeScopeResolver::class);
        $nodeScopeResolver->setAnalysedFiles([$fileInfo->getRealPath()]);

        $file = new File($fileInfo, $fileInfo->getContents());
        $this->applicationFileProcessor->run([$file]);

        return $file->getFileContent();
    }
}
