<?php declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\FileSystem;
use Nette\Utils\Strings;
use Rector\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Configuration\Configuration;
use Rector\Exception\ShouldNotHappenException;
use Rector\FileSystemRector\Contract\FileSystemRectorInterface;
use Rector\FileSystemRector\FileSystemFileProcessor;
use Rector\HttpKernel\RectorKernel;
use Symfony\Component\Yaml\Yaml;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;
use Symplify\PackageBuilder\Tests\AbstractKernelTestCase;

abstract class AbstractFileSystemRectorTestCase extends AbstractKernelTestCase
{
    /**
     * @var FileSystemFileProcessor
     */
    protected $fileSystemFileProcessor;

    /**
     * @var RemovedAndAddedFilesProcessor
     */
    private $removedAndAddedFilesProcessor;

    protected function setUp(): void
    {
        $this->createContainerWithProvidedRector();

        $this->fileSystemFileProcessor = self::$container->get(FileSystemFileProcessor::class);

        // so the files are removed and added
        $configuration = self::$container->get(Configuration::class);
        $configuration->setIsDryRun(false);

        $this->removedAndAddedFilesProcessor = self::$container->get(RemovedAndAddedFilesProcessor::class);
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

    protected function getRectorClass(): string
    {
        // can be implemented
        return '';
    }

    private function getCurrentTestRectorClass(): string
    {
        $rectorClass = $this->getRectorClass();
        $this->ensureRectorClassIsValid($rectorClass, 'getRectorClass');

        return $rectorClass;
    }

    private function ensureRectorClassIsValid(string $rectorClass, string $methodName): void
    {
        if (is_a($rectorClass, FileSystemRectorInterface::class, true)) {
            return;
        }

        throw new ShouldNotHappenException(sprintf(
            'Class "%s" in "%s()" method must be type of "%s"',
            $rectorClass,
            $methodName,
            FileSystemRectorInterface::class
        ));
    }

    private function createContainerWithProvidedRector(): void
    {
        $thisClass = Strings::after(Strings::webalize(static::class), '-', -1);

        $configFileTempPath = sprintf(
            sys_get_temp_dir() . '/rector_temp_tests/' . $thisClass . 'file_system_rector.yaml'
        );
        $yamlContent = Yaml::dump([
            'services' => [
                $this->getCurrentTestRectorClass() => null,
            ],
        ], Yaml::DUMP_OBJECT_AS_MAP);

        FileSystem::write($configFileTempPath, $yamlContent);

        $configFile = $configFileTempPath;
        $this->bootKernelWithConfigs(RectorKernel::class, [$configFile]);
    }
}
