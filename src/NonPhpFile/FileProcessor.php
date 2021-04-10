<?php
declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\FileSystem\FilesFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class FileProcessor
{
    /**
     * @var FileProcessorInterface[]
     */
    private $fileProcessors = [];

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var ErrorAndDiffCollector
     */
    private $errorAndDiffCollector;

    /**
     * @var FilesFinder
     */
    private $filesFinder;

    /**
<<<<<<< HEAD:src/NonPhpFile/NonPhpFileProcessorService.php
     * @param FileProcessorInterface[] $nonPhpFileProcessors
=======
     * @param FileProcessorInterface[] $fileProcessors
>>>>>>> 7bc65c3cc4... rename non-php file processor to file processor:src/NonPhpFile/FileProcessor.php
     */
    public function __construct(
        FilesFinder $filesFinder,
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        array $fileProcessors = []
    ) {
        $this->fileProcessors = $fileProcessors;
        $this->smartFileSystem = $smartFileSystem;
        $this->filesFinder = $filesFinder;
        $this->errorAndDiffCollector = $errorAndDiffCollector;
        $this->configuration = $configuration;
    }

    /**
     * @param string[] $paths
     */
    public function runOnPaths(array $paths): void
    {
        $fileInfos = $this->findFileInfos($paths);
        $this->runNonPhpFileProcessors($fileInfos);
    }

    /**
     * @param SmartFileInfo[] $nonPhpFileInfos
     */
    private function runNonPhpFileProcessors(array $nonPhpFileInfos): void
    {
        foreach ($nonPhpFileInfos as $nonPhpFileInfo) {
            foreach ($this->fileProcessors as $nonPhpFileProcessor) {
                if (! $nonPhpFileProcessor->supports($nonPhpFileInfo)) {
                    continue;
                }

                $oldContent = $nonPhpFileInfo->getContents();
                $newContent = $nonPhpFileProcessor->process($nonPhpFileInfo);
                if ($oldContent === $newContent) {
                    continue;
                }

                $this->errorAndDiffCollector->addFileDiff($nonPhpFileInfo, $oldContent, $newContent);

                if ($this->configuration->isDryRun()) {
                    return;
                }

                $this->dumpFileInfo($nonPhpFileInfo, $newContent);
            }
        }
    }

    /**
     * @param string[] $paths
     * @return SmartFileInfo[]
     */
    private function findFileInfos(array $paths): array
    {
        $fileExtensions = $this->resolveSupportedFileExtensions();

        $fileInfos = $this->filesFinder->findInDirectoriesAndFiles($paths, $fileExtensions);

        $composerJsonFilePath = getcwd() . '/composer.json';
        if ($this->smartFileSystem->exists($composerJsonFilePath)) {
            $fileInfos[] = new SmartFileInfo($composerJsonFilePath);
        }

        return $fileInfos;
    }

    /**
     * @return string[]
     */
    private function resolveSupportedFileExtensions(): array
    {
        $fileExtensions = [];

        foreach ($this->fileProcessors as $nonPhpFileProcessor) {
            $fileExtensions = array_merge($nonPhpFileProcessor->getSupportedFileExtensions(), $fileExtensions);
        }

        return array_unique($fileExtensions);
    }

    private function dumpFileInfo(SmartFileInfo $nonPhpFileInfo, string $newContent): void
    {
        $this->smartFileSystem->dumpFile($nonPhpFileInfo->getPathname(), $newContent);
        $this->smartFileSystem->chmod($nonPhpFileInfo->getRealPath(), $nonPhpFileInfo->getPerms());
    }
}
