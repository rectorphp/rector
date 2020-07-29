<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\ValueObject\StaticNonPhpFileSuffixes;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\FileSystemRector\FileSystemFileProcessor;
use ReflectionClass;
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
        parent::setUp();

        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);
        $this->removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);

        // so the files are removed and added
        $configuration = self::$container->get(Configuration::class);
        $configuration->setIsDryRun(false);
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

        if ($this->fileSystemFileProcessor->getFileSystemRectorsCount() === 0) {
            throw new ShouldNotHappenException(
                'No rector rules found in filesytem. Check the configuration of the test case.'
            );
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
}
