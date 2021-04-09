<?php
declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\NonPhpFileProcessorInterface;
use Rector\Core\FileSystem\FilesFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class NonPhpFileProcessorService
{
    /**
     * @var NonPhpFileProcessorInterface[]
     */
    private $nonPhpFileProcessors;

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
     * @param NonPhpFileProcessorInterface[] $nonPhpFileProcessors
     */
    public function __construct(
        FilesFinder $filesFinder,
        ErrorAndDiffCollector $errorAndDiffCollector,
        Configuration $configuration,
        SmartFileSystem $smartFileSystem,
        array $nonPhpFileProcessors = []
    ) {
        $this->nonPhpFileProcessors = $nonPhpFileProcessors;
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
        $nonPhpFileInfos = $this->collectNonPhpFiles($paths);
        $this->runNonPhpFileProcessors($nonPhpFileInfos);
    }

    /**
     * @param SmartFileInfo[] $nonPhpFileInfos
     */
    private function runNonPhpFileProcessors(array $nonPhpFileInfos): void
    {
        foreach ($nonPhpFileInfos as $nonPhpFileInfo) {
            foreach ($this->nonPhpFileProcessors as $nonPhpFileProcessor) {
                if (! $nonPhpFileProcessor->supports($nonPhpFileInfo)) {
                    continue;
                }
                $nonPhpFileChange = $nonPhpFileProcessor->process($nonPhpFileInfo);

                if ($nonPhpFileChange === null) {
                    continue;
                }

                $this->errorAndDiffCollector->addFileDiff(
                    $nonPhpFileInfo,
                    $nonPhpFileChange->getNewContent(),
                    $nonPhpFileChange->getOldContent()
                );
                if (! $this->configuration->isDryRun()) {
                    $this->smartFileSystem->dumpFile(
                        $nonPhpFileInfo->getPathname(),
                        $nonPhpFileChange->getNewContent()
                    );
                    $this->smartFileSystem->chmod($nonPhpFileInfo->getRealPath(), $nonPhpFileInfo->getPerms());
                }
            }
        }
    }

    /**
     * @param string[] $paths
     *
     * @return SmartFileInfo[]
     */
    private function collectNonPhpFiles(array $paths): array
    {
        $nonPhpFileExtensions = [];
        foreach ($this->nonPhpFileProcessors as $nonPhpFileProcessor) {
            $nonPhpFileExtensions = array_merge(
                $nonPhpFileProcessor->getSupportedFileExtensions(),
                $nonPhpFileExtensions
            );
        }
        $nonPhpFileExtensions = array_unique($nonPhpFileExtensions);

        $nonPhpFileInfos = $this->filesFinder->findInDirectoriesAndFiles($paths, $nonPhpFileExtensions);

        $composerJsonFilePath = getcwd() . '/composer.json';
        if ($this->smartFileSystem->exists($composerJsonFilePath)) {
            $nonPhpFileInfos[] = new SmartFileInfo($composerJsonFilePath);
        }

        return $nonPhpFileInfos;
    }
}
