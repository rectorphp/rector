<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Configuration\Configuration;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\HttpKernel\RectorKernel;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

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
        if (FileSystem::isAbsolute(__DIR__ . '/Fixture')) {
            FileSystem::delete(__DIR__ . '/Fixture');
        }

        if (FileSystem::isAbsolute(__DIR__ . '/Source/Fixture')) {
            FileSystem::delete(__DIR__ . '/Source/Fixture');
        }
    }

    protected function doTestFile(string $file): string
    {
        $fileInfo = new SmartFileInfo($file);

        $temporaryFilePath = $this->createTemporaryFilePath($fileInfo, $file);
        require_once $temporaryFilePath;

        $this->fileSystemFileProcessor->processFileInfo(new SmartFileInfo($temporaryFilePath));
        $this->removedAndAddedFilesProcessor->run();

        return $temporaryFilePath;
    }

    protected function createTemporaryFilePath(SmartFileInfo $fileInfo, string $file): string
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

    protected function getRectorInterface(): string
    {
        return FileSystemRectorInterface::class;
    }

    private function createContainerWithProvidedRector(): void
    {
        $configFileTempPath = $this->createConfigFileTempPath();

        $listForConfig = [];
        foreach ($this->getCurrentTestRectorClasses() as $rectorClass => $configuration) {
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
}
