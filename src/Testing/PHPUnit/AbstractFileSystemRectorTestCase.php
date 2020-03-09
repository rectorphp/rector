<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
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

    protected function setUp(): void
    {
        $this->createContainerWithProvidedRector();

        // so the files are removed and added
        $configuration = self::$container->get(Configuration::class);
        $configuration->setIsDryRun(false);

        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);
        $this->removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);
    }

    protected function tearDown(): void
    {
        $testDirectory = $this->resolveTestDirectory();

        if (file_exists($testDirectory . '/Source/Fixture')) {
            FileSystem::delete($testDirectory . '/Source/Fixture');
        }

        if (file_exists($testDirectory . '/Fixture')) {
            FileSystem::delete($testDirectory . '/Fixture');
        }
    }

    protected function doTestFileWithoutAutoload(string $file): string
    {
        $temporaryFilePath = $this->createTemporaryFilePathFromFilePath($file);

        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($temporaryFilePath));
        $this->removedAndAddedFilesProcessor->run();

        return $temporaryFilePath;
    }

    protected function doTestFile(string $file): string
    {
        $temporaryFilePath = $this->createTemporaryFilePathFromFilePath($file);

        require_once $temporaryFilePath;

        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($temporaryFilePath));
        $this->removedAndAddedFilesProcessor->run();

        return $temporaryFilePath;
    }

    protected function getRectorInterface(): string
    {
        return FileSystemRectorInterface::class;
    }

    private function createTemporaryFilePath(SmartFileInfo $fileInfo, string $file): string
    {
        $temporaryFilePath = sprintf(
            '%s%sFixture%s%s',
            dirname($fileInfo->getPath()),
            DIRECTORY_SEPARATOR,
            DIRECTORY_SEPARATOR,
            $fileInfo->getBasename()
        );

        FileSystem::copy($file, $temporaryFilePath);

        return $temporaryFilePath;
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

        $this->bootKernelWithConfigs(RectorKernel::class, [$configFileTempPath]);
    }

    private function createConfigFileTempPath(): string
    {
        $thisClass = Strings::after(Strings::webalize(static::class), '-', -1);

        return sprintf(sys_get_temp_dir() . '/rector_temp_tests/' . $thisClass . 'file_system_rector.yaml');
    }

    private function createTemporaryFilePathFromFilePath(string $file): string
    {
        $fileInfo = new SmartFileInfo($file);
        return $this->createTemporaryFilePath($fileInfo, $file);
    }

    private function resolveTestDirectory(): string
    {
        $testReflectionClass = new ReflectionClass(static::class);

        return (string) dirname((string) $testReflectionClass->getFileName());
    }
}
