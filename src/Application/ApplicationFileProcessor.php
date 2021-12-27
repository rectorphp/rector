<?php

declare(strict_types=1);

namespace Rector\Core\Application;

use Rector\Core\Application\FileDecorator\FileDiffFileDecorator;
use Rector\Core\Application\FileSystem\RemovedAndAddedFilesProcessor;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Application\SystemError;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\FileFormatter\FileFormatter;
use Rector\Parallel\ValueObject\Bridge;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\PackageBuilder\Yaml\ParametersMerger;
use Symplify\SmartFileSystem\SmartFileSystem;

final class ApplicationFileProcessor
{
    /**
     * @var SystemError[]
     */
    private array $systemErrors = [];

    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(
        private readonly SmartFileSystem $smartFileSystem,
        private readonly FileDiffFileDecorator $fileDiffFileDecorator,
        private readonly FileFormatter $fileFormatter,
        private readonly RemovedAndAddedFilesProcessor $removedAndAddedFilesProcessor,
        private readonly SymfonyStyle $symfonyStyle,
        private readonly ParametersMerger $parametersMerger,
        private readonly array $fileProcessors = []
    ) {
    }

    /**
     * @param File[] $files
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function run(array $files, Configuration $configuration): array
    {
        $this->configureCustomErrorHandler();

        $systemErrorsAndFileDiffs = $this->processFiles($files, $configuration);
        $this->fileFormatter->format($files);

        $this->fileDiffFileDecorator->decorate($files);
        $this->printFiles($files, $configuration);

        $this->restoreErrorHandler();

        $systemErrorsAndFileDiffs['system_errors'] = array_merge(
            $systemErrorsAndFileDiffs['system_errors'],
            $this->systemErrors
        );

        return $systemErrorsAndFileDiffs;
    }

    /**
     * @param File[] $files
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    private function processFiles(array $files, Configuration $configuration): array
    {
        if ($configuration->shouldShowProgressBar()) {
            $fileCount = count($files);
            $this->symfonyStyle->progressStart($fileCount);
        }

        $systemErrorsAndFileDiffs = [
            Bridge::SYSTEM_ERRORS => [],
            Bridge::FILE_DIFFS => [],
        ];

        foreach ($files as $file) {
            foreach ($this->fileProcessors as $fileProcessor) {
                if (! $fileProcessor->supports($file, $configuration)) {
                    continue;
                }

                $result = $fileProcessor->process($file, $configuration);

                if (is_array($result)) {
                    $systemErrorsAndFileDiffs = $this->parametersMerger->merge($systemErrorsAndFileDiffs, $result);
                }
            }

            // progress bar +1
            if ($configuration->shouldShowProgressBar()) {
                $this->symfonyStyle->progressAdvance();
            }
        }

        $this->removedAndAddedFilesProcessor->run($configuration);

        return $systemErrorsAndFileDiffs;
    }

    /**
     * @param File[] $files
     */
    private function printFiles(array $files, Configuration $configuration): void
    {
        if ($configuration->isDryRun()) {
            return;
        }

        foreach ($files as $file) {
            if (! $file->hasChanged()) {
                continue;
            }

            $this->printFile($file);
        }
    }

    private function printFile(File $file): void
    {
        $smartFileInfo = $file->getSmartFileInfo();

        $this->smartFileSystem->dumpFile($smartFileInfo->getPathname(), $file->getFileContent());
        $this->smartFileSystem->chmod($smartFileInfo->getRealPath(), $smartFileInfo->getPerms());
    }

    /**
     * Inspired by @see https://github.com/phpstan/phpstan-src/blob/89af4e7db257750cdee5d4259ad312941b6b25e8/src/Analyser/Analyser.php#L134
     */
    private function configureCustomErrorHandler(): void
    {
        $errorHandlerCallback = function (int $code, string $message, string $file, int $line): bool {
            if ((error_reporting() & $code) === 0) {
                // silence @ operator
                return true;
            }

            // not relevant for us
            if ($code === E_DEPRECATED) {
                return true;
            }

            $this->systemErrors[] = new SystemError($message, $file, $line);

            return true;
        };

        set_error_handler($errorHandlerCallback);
    }

    private function restoreErrorHandler(): void
    {
        restore_error_handler();
    }
}
