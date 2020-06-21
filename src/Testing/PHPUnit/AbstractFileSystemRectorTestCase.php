<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\HttpKernel\RectorKernel;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\FileSystemRector\FileSystemFileProcessor;
use ReflectionClass;
use Symfony\Component\Yaml\Yaml;
use Symplify\SmartFileSystem\SmartFileInfo;

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

    protected function setUp(): void
    {
        $this->createContainerWithProvidedRector();

        // so the files are removed and added
        $configuration = self::$container->get(Configuration::class);
        $configuration->setIsDryRun(false);

        $this->fileProcessor = self::$container->get(FileProcessor::class);
        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);
        $this->removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);
    }

    /**
     * @param string[] $extraFiles
     */
    protected function doTestFileWithoutAutoload(string $file, array $extraFiles = []): string
    {
        return $this->doTestFile($file, $extraFiles, false);
    }

    /**
     * @param string[] $extraFiles
     */
    protected function doTestFile(string $file, array $extraFiles = [], bool $autolaod = true): string
    {
        $temporaryFilePath = $this->createTemporaryFilePathFromFilePath($file);

        if ($autolaod) {
            require_once $temporaryFilePath;
        }

        $fileInfo = new SmartFileInfo($temporaryFilePath);

        $this->fileSystemFileProcessor->processFileInfo($fileInfo);

        $filesInfos = [$fileInfo];

        foreach ($extraFiles as $extraFile) {
            $temporaryExtraFilePath = $this->createTemporaryFilePathFromFilePath($extraFile);
            $extraFileInfo = new SmartFileInfo($temporaryExtraFilePath);
            $this->fileSystemFileProcessor->processFileInfo($extraFileInfo);

            $filesInfos[] = $extraFileInfo;
        }

        foreach ($filesInfos as $fileInfo) {
            // maybe the file was removed
            if (! file_exists($fileInfo->getPathname())) {
                continue;
            }

            $this->fileProcessor->postFileRefactor($fileInfo);
            $this->fileProcessor->printToFile($fileInfo);
        }

        $this->removedAndAddedFilesProcessor->run();

        return $temporaryFilePath;
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

        FileSystem::write($configFileTempPath, $yamlContent);

        // for 3rd party testing with services defined in configs
        $configFilePaths = [$configFileTempPath];
        if ($this->provideConfig() !== '') {
            $configFilePaths[] = $this->provideConfig();
        }

        $this->bootKernelWithConfigs(RectorKernel::class, $configFilePaths);
    }

    private function createTemporaryFilePathFromFilePath(string $file): string
    {
        $fileInfo = new SmartFileInfo($file);

        // 1. get test case directory
        $reflectionClass = new ReflectionClass(static::class);
        $testCaseDirectory = dirname((string) $reflectionClass->getFileName());

        // 2. relative test case file path
        $relativeFilePath = $fileInfo->getRelativeFilePathFromDirectory($testCaseDirectory);
        $temporaryFilePath = $this->getFixtureTempDirectory() . '/' . $relativeFilePath;

        FileSystem::delete($temporaryFilePath);
        FileSystem::copy($file, $temporaryFilePath, true);

        return $temporaryFilePath;
    }

    private function createConfigFileTempPath(): string
    {
        $thisClass = Strings::after(Strings::webalize(static::class), '-', -1);

        return sprintf($this->getFixtureTempDirectory() . '/' . $thisClass . 'file_system_rector.yaml');
    }
}
