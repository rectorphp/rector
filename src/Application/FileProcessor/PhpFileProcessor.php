<?php

declare (strict_types=1);
namespace Rector\Core\Application\FileProcessor;

use RectorPrefix202308\Nette\Utils\Strings;
use PHPStan\AnalysedCodeException;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\ChangesReporting\ValueObjectFactory\ErrorFactory;
use Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory;
use Rector\Core\Application\FileProcessor;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\FileSystem\FilePathHelper;
use Rector\Core\PhpParser\Printer\FormatPerservingPrinter;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use Rector\PostRector\Application\PostFileProcessor;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix202308\Symfony\Component\Console\Style\SymfonyStyle;
use Throwable;
final class PhpFileProcessor implements FileProcessorInterface
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Printer\FormatPerservingPrinter
     */
    private $formatPerservingPrinter;
    /**
     * @readonly
     * @var \Rector\Core\Application\FileProcessor
     */
    private $fileProcessor;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\FileDiffFactory
     */
    private $fileDiffFactory;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @readonly
     * @var \Rector\PostRector\Application\PostFileProcessor
     */
    private $postFileProcessor;
    /**
     * @readonly
     * @var \Rector\ChangesReporting\ValueObjectFactory\ErrorFactory
     */
    private $errorFactory;
    /**
     * @readonly
     * @var \Rector\Core\FileSystem\FilePathHelper
     */
    private $filePathHelper;
    /**
     * @var string
     * @see https://regex101.com/r/xP2MGa/1
     */
    private const OPEN_TAG_SPACED_REGEX = '#^(?<open_tag_spaced>[^\\S\\r\\n]+\\<\\?php)#m';
    public function __construct(FormatPerservingPrinter $formatPerservingPrinter, FileProcessor $fileProcessor, SymfonyStyle $symfonyStyle, FileDiffFactory $fileDiffFactory, ChangedFilesDetector $changedFilesDetector, PostFileProcessor $postFileProcessor, ErrorFactory $errorFactory, FilePathHelper $filePathHelper)
    {
        $this->formatPerservingPrinter = $formatPerservingPrinter;
        $this->fileProcessor = $fileProcessor;
        $this->symfonyStyle = $symfonyStyle;
        $this->fileDiffFactory = $fileDiffFactory;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->postFileProcessor = $postFileProcessor;
        $this->errorFactory = $errorFactory;
        $this->filePathHelper = $filePathHelper;
    }
    /**
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    public function process(File $file, Configuration $configuration) : array
    {
        $systemErrorsAndFileDiffs = [Bridge::SYSTEM_ERRORS => [], Bridge::FILE_DIFFS => []];
        // 1. parse files to nodes
        $parsingSystemErrors = $this->parseFileAndDecorateNodes($file);
        if ($parsingSystemErrors !== []) {
            // we cannot process this file as the parsing and type resolving itself went wrong
            $systemErrorsAndFileDiffs[Bridge::SYSTEM_ERRORS] = $parsingSystemErrors;
            return $systemErrorsAndFileDiffs;
        }
        $fileHasChanged = \false;
        // 2. change nodes with Rectors
        $rectorWithLineChanges = null;
        do {
            $file->changeHasChanged(\false);
            $this->fileProcessor->refactor($file);
            // 3. apply post rectors
            $newStmts = $this->postFileProcessor->traverse($file->getNewStmts());
            // this is needed for new tokens added in "afterTraverse()"
            $file->changeNewStmts($newStmts);
            // 4. print to file or string
            // important to detect if file has changed
            $this->printFile($file, $configuration);
            $fileHasChangedInCurrentPass = $file->hasChanged();
            if ($fileHasChangedInCurrentPass) {
                $file->setFileDiff($this->fileDiffFactory->createTempFileDiff($file));
                $rectorWithLineChanges = $file->getRectorWithLineChanges();
                $fileHasChanged = \true;
            }
        } while ($fileHasChangedInCurrentPass);
        // 5. add as cacheable if not changed at all
        if (!$fileHasChanged) {
            $this->changedFilesDetector->addCachableFile($file->getFilePath());
        }
        if ($configuration->shouldShowDiffs() && $rectorWithLineChanges !== null) {
            $file->setFileDiff($this->fileDiffFactory->createFileDiffWithLineChanges($file, $file->getOriginalFileContent(), $file->getFileContent(), $rectorWithLineChanges));
        }
        // return json here
        $fileDiff = $file->getFileDiff();
        if (!$fileDiff instanceof FileDiff) {
            return $systemErrorsAndFileDiffs;
        }
        $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS] = [$fileDiff];
        return $systemErrorsAndFileDiffs;
    }
    public function supports(File $file, Configuration $configuration) : bool
    {
        return \true;
    }
    /**
     * @return string[]
     */
    public function getSupportedFileExtensions() : array
    {
        return ['php'];
    }
    /**
     * @return SystemError[]
     */
    private function parseFileAndDecorateNodes(File $file) : array
    {
        $this->notifyFile($file);
        try {
            $this->fileProcessor->parseFileInfoToLocalCache($file);
        } catch (ShouldNotHappenException $shouldNotHappenException) {
            throw $shouldNotHappenException;
        } catch (AnalysedCodeException $analysedCodeException) {
            // inform about missing classes in tests
            if (StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $analysedCodeException;
            }
            $autoloadSystemError = $this->errorFactory->createAutoloadError($analysedCodeException, $file->getFilePath());
            return [$autoloadSystemError];
        } catch (Throwable $throwable) {
            if ($this->symfonyStyle->isVerbose() || StaticPHPUnitEnvironment::isPHPUnitRun()) {
                throw $throwable;
            }
            $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
            $systemError = new SystemError($throwable->getMessage(), $relativeFilePath, $throwable->getLine());
            return [$systemError];
        }
        return [];
    }
    private function printFile(File $file, Configuration $configuration) : void
    {
        // only save to string first, no need to print to file when not needed
        $newContent = $this->formatPerservingPrinter->printParsedStmstAndTokensToString($file);
        /**
         * When no diff applied, the PostRector may still change the content, that's why printing still needed
         * On printing, the space may be wiped, these below check compare with original file content used to verify
         * that no change actually needed
         */
        if (!$file->getFileDiff() instanceof FileDiff) {
            /**
             * Handle new line or space before <?php or InlineHTML node wiped on print format preserving
             * On very first content level
             */
            $originalFileContent = $file->getOriginalFileContent();
            $ltrimOriginalFileContent = \ltrim($originalFileContent);
            if ($ltrimOriginalFileContent === $newContent) {
                return;
            }
            $cleanOriginalContent = Strings::replace($ltrimOriginalFileContent, self::OPEN_TAG_SPACED_REGEX, '<?php');
            $cleanNewContent = Strings::replace($newContent, self::OPEN_TAG_SPACED_REGEX, '<?php');
            /**
             * Handle space before <?php wiped on print format preserving
             * On inside content level
             */
            if ($cleanOriginalContent === $cleanNewContent) {
                return;
            }
        }
        if (!$configuration->isDryRun()) {
            $this->formatPerservingPrinter->dumpFile($file->getFilePath(), $newContent);
        }
        $file->changeFileContent($newContent);
    }
    private function notifyFile(File $file) : void
    {
        if (!$this->symfonyStyle->isVerbose()) {
            return;
        }
        $relativeFilePath = $this->filePathHelper->relativePath($file->getFilePath());
        $this->symfonyStyle->writeln($relativeFilePath);
    }
}
