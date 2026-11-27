<?php

declare (strict_types=1);
namespace Rector\Reporting;

use RectorPrefix202611\Nette\Utils\FileSystem;
use Rector\Application\FileProcessor;
use Rector\Application\Provider\CurrentFileProvider;
use Rector\FileSystem\FilePathHelper;
use Rector\Skipper\Skipper\Skipper;
use Rector\ValueObject\Application\File;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\ProcessResult;
/**
 * Re-runs each rule-scoped skip on its exact file with only that skip lifted. A skip whose rule still does
 * not change the file never prevented anything and can be removed. The re-processing is forced to a
 * dry run, so it never writes, no matter the mode of the real run.
 *
 * @see \Rector\Tests\Reporting\NeededSkipVerifierTest
 */
final class NeededSkipVerifier
{
    /**
     * @readonly
     */
    private Skipper $skipper;
    /**
     * @readonly
     */
    private FileProcessor $fileProcessor;
    /**
     * @readonly
     */
    private CurrentFileProvider $currentFileProvider;
    /**
     * @readonly
     */
    private FilePathHelper $filePathHelper;
    public function __construct(Skipper $skipper, FileProcessor $fileProcessor, CurrentFileProvider $currentFileProvider, FilePathHelper $filePathHelper)
    {
        $this->skipper = $skipper;
        $this->fileProcessor = $fileProcessor;
        $this->currentFileProvider = $currentFileProvider;
        $this->filePathHelper = $filePathHelper;
    }
    /**
     * Unnecessary skips grouped under their rule, ready for "->listing()".
     *
     * @return string[]
     */
    public function verify(ProcessResult $processResult): array
    {
        // collected in the process that handled the files - the workers in a parallel run
        $skippedFilePathsByRule = $processResult->getSkippedFilePathsByRule();
        if ($skippedFilePathsByRule === []) {
            return [];
        }
        // force a dry run, so re-processing detects the change but never writes the skipped file
        $dryRunConfiguration = new Configuration(\true);
        $unnecessarySkips = [];
        foreach ($skippedFilePathsByRule as $rectorClass => $filePaths) {
            $unnecessaryRelativePaths = [];
            foreach ($filePaths as $filePath) {
                if ($this->isSkipNeeded($rectorClass, $filePath, $dryRunConfiguration)) {
                    continue;
                }
                $unnecessaryRelativePaths[] = $this->filePathHelper->relativePath($filePath);
            }
            if ($unnecessaryRelativePaths === []) {
                continue;
            }
            // rule on its own line, with each unnecessary path nested below it as a sub-item
            $unnecessarySkips[] = $rectorClass . ':' . "\n     * " . implode("\n     * ", $unnecessaryRelativePaths);
        }
        return $unnecessarySkips;
    }
    /**
     * Lifts only this one skip, so other skipped rules cannot make the same change first
     */
    private function isSkipNeeded(string $rectorClass, string $filePath, Configuration $configuration): bool
    {
        $this->skipper->startVerifyingSkip($rectorClass);
        try {
            $file = new File($filePath, FileSystem::read($filePath));
            $this->currentFileProvider->setFile($file);
            $fileProcessResult = $this->fileProcessor->processFile($file, $configuration);
        } finally {
            $this->skipper->stopVerifyingSkip();
        }
        // a broken file is not verifiable, keep the skip to stay safe
        if ($fileProcessResult->getSystemErrors() !== []) {
            return \true;
        }
        $fileDiff = $fileProcessResult->getFileDiff();
        return in_array($rectorClass, (($nullsafeVariable1 = $fileDiff) ? $nullsafeVariable1->getRectorClasses() : null) ?? [], \true);
    }
}
