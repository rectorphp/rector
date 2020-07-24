<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\Core\NonPhpFile\NonPhpFileProcessor;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\FileSystemRector\FileSystemFileProcessor;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

abstract class AbstractFileSystemRectorTestCase extends AbstractGenericRectorTestCase
{
    /**
     * @var FileSystemFileProcessor
     */
    private $fileSystemFileProcessor;

    /**
     * @var RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;

    /**
     * @var FileProcessor
     */
    private $fileProcessor;

    /**
     * @var NonPhpFileProcessor
     */
    private $nonPhpFileProcessor;

    protected function setUp(): void
    {
        $this->createContainerWithProvidedRector();

        // so the files are removed and added
        $configuration = self::$container->get(Configuration::class);
        $configuration->setIsDryRun(false);

        $this->fileProcessor = self::$container->get(FileProcessor::class);
        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);
        $this->removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);
        $this->nonPhpFileProcessor = self::$container->get(NonPhpFileProcessor::class);
    }

    /**
     * @param string[] $extraFiles
     */
    protected function doTestFileInfo(
        SmartFileInfo $fileInfo,
        array $extraFiles = [],
        bool $autolaod = true
    ): SmartFileInfo {
        $temporaryFileInfo = $this->createTemporaryFilePathFromFilePath($fileInfo);

        if ($autolaod) {
            require_once $temporaryFileInfo->getRealPath();
        }

        $this->fileSystemFileProcessor->processFileInfo($temporaryFileInfo);

        $filesInfos = [$temporaryFileInfo];

        foreach ($extraFiles as $extraFile) {
            $extraFileInfo = new SmartFileInfo($extraFile);

            $temporaryExtraFileInfo = $this->createTemporaryFilePathFromFilePath($extraFileInfo);
            $this->fileSystemFileProcessor->processFileInfo($temporaryExtraFileInfo);

            $filesInfos[] = $temporaryExtraFileInfo;
        }

        foreach ($filesInfos as $fileInfo) {
            // maybe the file was removed
            if (! file_exists($fileInfo->getPathname())) {
                continue;
            }

            if (in_array($fileInfo->getSuffix(), StaticNonPhpFileSuffixes::SUFFIXES, true)) {
                $this->nonPhpFileProcessor->processFileInfo($fileInfo);
            } else {
                $this->fileProcessor->postFileRefactor($fileInfo);
                $this->fileProcessor->printToFile($fileInfo);
            }
        }

        $this->removedAndAddedFilesProcessor->run();

        return $temporaryFileInfo;
    }

    protected function getRectorInterface(): string
    {
        return FileSystemRectorInterface::class;
    }

    protected function getFixtureTempDirectory(): string
    {
        return sys_get_temp_dir() . '/rector_temp_tests';
    }

    private function createContainerWithProvidedRector(): void
    {
        $configFileTempPath = $this->createConfigFileTempPath();

        $listForConfig = [];
        foreach ($this->getCurrentTestRectorClassesWithConfiguration() as $rectorClass => $configuration) {
            $listForConfig[$rectorClass] = $configuration;
        }

        $yamlContent = Yaml::dump([
            'services' => $listForConfig,
        ], Yaml::DUMP_OBJECT_AS_MAP);

        $smartFileSystem = new SmartFileSystem();
        $smartFileSystem->dumpFile($configFileTempPath, $yamlContent);

        // for 3rd party testing with services defined in configs
        $configFileInfos = [new SmartFileInfo($configFileTempPath)];
        if ($this->provideConfigFileInfo() !== null) {
            $configFileInfos[] = $this->provideConfigFileInfo();
        }

        $this->bootKernelWithConfigInfos(RectorKernel::class, $configFileInfos);
    }

    private function createTemporaryFilePathFromFilePath(SmartFileInfo $fileInfo): SmartFileInfo
    {
        // 1. get test case directory
        $reflectionClass = new ReflectionClass(static::class);
        $testCaseDirectory = dirname((string) $reflectionClass->getFileName());

        // 2. relative test case file path
        $relativeFilePath = $fileInfo->getRelativeFilePathFromDirectory($testCaseDirectory);
        $temporaryFilePath = $this->getFixtureTempDirectory() . '/' . $relativeFilePath;

        FileSystem::delete($temporaryFilePath);
        FileSystem::copy($fileInfo->getRealPath(), $temporaryFilePath, true);

        return new SmartFileInfo($temporaryFilePath);
    }

    private function createConfigFileTempPath(): string
    {
        $thisClass = Strings::after(Strings::webalize(static::class), '-', -1);

        return sprintf($this->getFixtureTempDirectory() . '/' . $thisClass . 'file_system_rector.yaml');
    }
}
