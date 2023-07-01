<?php

declare (strict_types=1);
namespace Rector\Parallel;

use RectorPrefix202307\Clue\React\NDJson\Decoder;
use RectorPrefix202307\Clue\React\NDJson\Encoder;
use RectorPrefix202307\Nette\Utils\FileSystem;
use Rector\Caching\Detector\ChangedFilesDetector;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\Console\Style\RectorConsoleOutputStyle;
use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\Provider\CurrentFileProvider;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\Util\ArrayParametersMerger;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Core\ValueObject\Reporting\FileDiff;
use Rector\Parallel\ValueObject\Bridge;
use RectorPrefix202307\Symplify\EasyParallel\Enum\Action;
use RectorPrefix202307\Symplify\EasyParallel\Enum\ReactCommand;
use RectorPrefix202307\Symplify\EasyParallel\Enum\ReactEvent;
use Throwable;
final class WorkerRunner
{
    /**
     * @readonly
     * @var \Rector\Core\Util\ArrayParametersMerger
     */
    private $arrayParametersMerger;
    /**
     * @readonly
     * @var \Rector\Core\Provider\CurrentFileProvider
     */
    private $currentFileProvider;
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @readonly
     * @var \Rector\Core\Console\Style\RectorConsoleOutputStyle
     */
    private $rectorConsoleOutputStyle;
    /**
     * @readonly
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @readonly
     * @var \Rector\Caching\Detector\ChangedFilesDetector
     */
    private $changedFilesDetector;
    /**
     * @var FileProcessorInterface[]
     * @readonly
     */
    private $fileProcessors = [];
    /**
     * @var string
     */
    private const RESULT = 'result';
    /**
     * @param FileProcessorInterface[] $fileProcessors
     */
    public function __construct(ArrayParametersMerger $arrayParametersMerger, CurrentFileProvider $currentFileProvider, DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, RectorConsoleOutputStyle $rectorConsoleOutputStyle, ApplicationFileProcessor $applicationFileProcessor, ChangedFilesDetector $changedFilesDetector, iterable $fileProcessors = [])
    {
        $this->arrayParametersMerger = $arrayParametersMerger;
        $this->currentFileProvider = $currentFileProvider;
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        $this->rectorConsoleOutputStyle = $rectorConsoleOutputStyle;
        $this->applicationFileProcessor = $applicationFileProcessor;
        $this->changedFilesDetector = $changedFilesDetector;
        $this->fileProcessors = $fileProcessors;
    }
    public function run(Encoder $encoder, Decoder $decoder, Configuration $configuration) : void
    {
        $this->dynamicSourceLocatorDecorator->addPaths($configuration->getPaths());
        // 1. handle system error
        $handleErrorCallback = static function (Throwable $throwable) use($encoder) : void {
            $systemErrors = new SystemError($throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
            $encoder->write([ReactCommand::ACTION => Action::RESULT, self::RESULT => [Bridge::SYSTEM_ERRORS => [$systemErrors], Bridge::FILES_COUNT => 0, Bridge::SYSTEM_ERRORS_COUNT => 1]]);
            $encoder->end();
        };
        $encoder->on(ReactEvent::ERROR, $handleErrorCallback);
        // 2. collect diffs + errors from file processor
        $decoder->on(ReactEvent::DATA, function (array $json) use($encoder, $configuration) : void {
            $action = $json[ReactCommand::ACTION];
            if ($action !== Action::MAIN) {
                return;
            }
            $systemErrorsCount = 0;
            /** @var string[] $filePaths */
            $filePaths = $json[Bridge::FILES] ?? [];
            $errorAndFileDiffs = [];
            $systemErrors = [];
            // 1. allow PHPStan to work with static reflection on provided files
            $this->applicationFileProcessor->configurePHPStanNodeScopeResolver($filePaths, $configuration);
            foreach ($filePaths as $filePath) {
                $file = null;
                try {
                    $file = new File($filePath, FileSystem::read($filePath));
                    $this->currentFileProvider->setFile($file);
                    $errorAndFileDiffs = $this->processFile($file, $configuration, $errorAndFileDiffs);
                    if ($errorAndFileDiffs[Bridge::SYSTEM_ERRORS] !== []) {
                        $this->invalidateFile($file);
                    } elseif (!$configuration->isDryRun() || $errorAndFileDiffs[Bridge::FILE_DIFFS] === []) {
                        $this->changedFilesDetector->cacheFileWithDependencies($file->getFilePath());
                    }
                } catch (Throwable $throwable) {
                    ++$systemErrorsCount;
                    $systemErrors = $this->collectSystemErrors($systemErrors, $throwable, $filePath);
                    $this->invalidateFile($file);
                }
            }
            /**
             * this invokes all listeners listening $decoder->on(...) @see \Symplify\EasyParallel\Enum\ReactEvent::DATA
             */
            $encoder->write([ReactCommand::ACTION => Action::RESULT, self::RESULT => [Bridge::FILE_DIFFS => $errorAndFileDiffs[Bridge::FILE_DIFFS] ?? [], Bridge::FILES_COUNT => \count($filePaths), Bridge::SYSTEM_ERRORS => $systemErrors, Bridge::SYSTEM_ERRORS_COUNT => $systemErrorsCount]]);
        });
        $decoder->on(ReactEvent::ERROR, $handleErrorCallback);
    }
    /**
     * @param array{system_errors: SystemError[], file_diffs: FileDiff[]}|mixed[] $errorAndFileDiffs
     * @return array{system_errors: SystemError[], file_diffs: FileDiff[]}
     */
    private function processFile(File $file, Configuration $configuration, array $errorAndFileDiffs) : array
    {
        foreach ($this->fileProcessors as $fileProcessor) {
            if (!$fileProcessor->supports($file, $configuration)) {
                continue;
            }
            $currentErrorsAndFileDiffs = $fileProcessor->process($file, $configuration);
            $errorAndFileDiffs = $this->arrayParametersMerger->merge($errorAndFileDiffs, $currentErrorsAndFileDiffs);
        }
        return $errorAndFileDiffs;
    }
    /**
     * @param SystemError[] $systemErrors
     * @return SystemError[]
     */
    private function collectSystemErrors(array $systemErrors, Throwable $throwable, string $filePath) : array
    {
        $errorMessage = \sprintf('System error: "%s"', $throwable->getMessage()) . \PHP_EOL;
        if ($this->rectorConsoleOutputStyle->isDebug()) {
            $systemErrors[] = new SystemError($errorMessage . \PHP_EOL . 'Stack trace:' . \PHP_EOL . $throwable->getTraceAsString(), $filePath, $throwable->getLine());
            return $systemErrors;
        }
        $errorMessage .= 'Run Rector with "--debug" option and post the report here: https://github.com/rectorphp/rector/issues/new';
        $systemErrors[] = new SystemError($errorMessage, $filePath, $throwable->getLine());
        return $systemErrors;
    }
    private function invalidateFile(?File $file) : void
    {
        if (!$file instanceof File) {
            return;
        }
        $this->changedFilesDetector->invalidateFile($file->getFilePath());
    }
}
