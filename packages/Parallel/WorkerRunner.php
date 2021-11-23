<?php

declare (strict_types=1);
namespace Rector\Parallel;

use RectorPrefix20211123\Clue\React\NDJson\Decoder;
use RectorPrefix20211123\Clue\React\NDJson\Encoder;
use Rector\Core\Application\FileProcessor;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Parallel\ValueObject\Bridge;
use RectorPrefix20211123\Symplify\EasyParallel\Enum\Action;
use RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactCommand;
use RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactEvent;
use RectorPrefix20211123\Symplify\PackageBuilder\Yaml\ParametersMerger;
use Symplify\SmartFileSystem\SmartFileInfo;
use Throwable;
final class WorkerRunner
{
    /**
     * @var string
     */
    private const RESULT = 'result';
    /**
     * @var \Rector\Core\Application\FileProcessor
     */
    private $fileProcessor;
    /**
     * @var \Symplify\PackageBuilder\Yaml\ParametersMerger
     */
    private $parametersMerger;
    public function __construct(\Rector\Core\Application\FileProcessor $fileProcessor, \RectorPrefix20211123\Symplify\PackageBuilder\Yaml\ParametersMerger $parametersMerger)
    {
        $this->fileProcessor = $fileProcessor;
        $this->parametersMerger = $parametersMerger;
    }
    public function run(\RectorPrefix20211123\Clue\React\NDJson\Encoder $encoder, \RectorPrefix20211123\Clue\React\NDJson\Decoder $decoder, \Rector\Core\ValueObject\Configuration $configuration) : void
    {
        // 1. handle system error
        $handleErrorCallback = static function (\Throwable $throwable) use($encoder) : void {
            $systemErrors = new \Rector\Core\ValueObject\Error\SystemError($throwable->getLine(), $throwable->getMessage(), $throwable->getFile());
            $encoder->write([\RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactCommand::ACTION => \RectorPrefix20211123\Symplify\EasyParallel\Enum\Action::RESULT, self::RESULT => [\Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => [$systemErrors], \Rector\Parallel\ValueObject\Bridge::FILES_COUNT => 0, \Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS_COUNT => 1]]);
            $encoder->end();
        };
        $encoder->on(\RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactEvent::ERROR, $handleErrorCallback);
        // 2. collect diffs + errors from file processor
        $decoder->on(\RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactEvent::DATA, function (array $json) use($encoder, $configuration) : void {
            $action = $json[\RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactCommand::ACTION];
            if ($action !== \RectorPrefix20211123\Symplify\EasyParallel\Enum\Action::MAIN) {
                return;
            }
            $systemErrorsCount = 0;
            /** @var string[] $filePaths */
            $filePaths = $json[\Rector\Parallel\ValueObject\Bridge::FILES] ?? [];
            $errorAndFileDiffs = [];
            $systemErrors = [];
            foreach ($filePaths as $filePath) {
                try {
                    $smartFileInfo = new \Symplify\SmartFileSystem\SmartFileInfo($filePath);
                    $file = new \Rector\Core\ValueObject\Application\File($smartFileInfo, $smartFileInfo->getContents());
                    $currentErrorsAndFileDiffs = $this->fileProcessor->refactor($file, $configuration);
                    $errorAndFileDiffs = $this->parametersMerger->merge($errorAndFileDiffs, $currentErrorsAndFileDiffs);
                } catch (\Throwable $throwable) {
                    ++$systemErrorsCount;
                    $errorMessage = \sprintf('System error: "%s"', $throwable->getMessage());
                    $errorMessage .= 'Run Rector with "--debug" option and post the report here: https://github.com/rectorphp/rector/issues/new';
                    $systemErrors[] = new \Rector\Core\ValueObject\Error\SystemError($throwable->getLine(), $errorMessage, $filePath);
                }
            }
            /**
             * this invokes all listeners listening $decoder->on(...) @see \Symplify\EasyParallel\Enum\ReactEvent::DATA
             */
            $encoder->write([\RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactCommand::ACTION => \RectorPrefix20211123\Symplify\EasyParallel\Enum\Action::RESULT, self::RESULT => [\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS => $errorAndFileDiffs[\Rector\Parallel\ValueObject\Bridge::FILE_DIFFS] ?? [], \Rector\Parallel\ValueObject\Bridge::FILES_COUNT => \count($filePaths), \Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS => $systemErrors, \Rector\Parallel\ValueObject\Bridge::SYSTEM_ERRORS_COUNT => $systemErrorsCount]]);
        });
        $decoder->on(\RectorPrefix20211123\Symplify\EasyParallel\Enum\ReactEvent::ERROR, $handleErrorCallback);
    }
}
