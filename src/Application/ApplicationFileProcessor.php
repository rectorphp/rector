<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\ChangesReporting\Application\ErrorAndDiffCollector;
use Rector\Core\Configuration\Configuration;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\FileSystem\FilesFinder;
use Rector\Core\ValueObject\Application\File;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ApplicationFileProcessor
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
     * @param FileProcessorInterface[] $fileProcessors
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
     * @param File[] $files
     */
    public function run(array $files): void
    {
//        $newContent = null;

        foreach ($files as $file) {
            foreach ($this->fileProcessors as $fileProcessor) {
                if (! $fileProcessor->supports($file)) {
                    continue;
                }

                $fileProcessor->process($file);
//                $smartFileInfo = $file->getSmartFileInfo();
//                if ($smartFileInfo->getContents() === $newContent) {
//                    continue;
//                }

                // @todo run once in the end
//                $this->errorAndDiffCollector->addFileDiff($smartFileInfo, $smartFileInfo->getContents(), $newContent);
            }

            // has file changed?
//            if (! $this->configuration->isDryRun() && $newContent !== null) {
//                $this->dumpFileInfo($smartFileInfo, $newContent);
//            }

            $this->errorAndDiffCollector->addFileDiff($smartFileInfo, $smartFileInfo->getContents(), $newContent);
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

//    private function dumpFileInfo(SmartFileInfo $nonPhpFileInfo, string $newContent): void
//    {
//        $this->smartFileSystem->dumpFile($nonPhpFileInfo->getPathname(), $newContent);
//        $this->smartFileSystem->chmod($nonPhpFileInfo->getRealPath(), $nonPhpFileInfo->getPerms());
//    }
}
