<?php

declare(strict_types=1);

namespace Rector\Parallel;

use Clue\React\NDJson\Decoder;
use Clue\React\NDJson\Encoder;
use Rector\Core\Application\FileProcessor;
use Rector\Core\ValueObject\Application\File;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Parallel\ValueObject\Bridge;
use Symplify\EasyParallel\Enum\Action;
use Symplify\EasyParallel\Enum\ReactCommand;
use Symplify\EasyParallel\Enum\ReactEvent;
use Symplify\PackageBuilder\Yaml\ParametersMerger;
use Symplify\SmartFileSystem\SmartFileInfo;
use Throwable;

final class WorkerRunner
{
    /**
     * @var string
     */
    private const RESULT = 'result';

    public function __construct(
        private readonly FileProcessor $fileProcessor,
        private readonly ParametersMerger $parametersMerger
    ) {
    }

    public function run(Encoder $encoder, Decoder $decoder, Configuration $configuration): void
    {
        // 1. handle system error
        $handleErrorCallback = static function (Throwable $throwable) use ($encoder): void {
            $systemErrors = new SystemError($throwable->getLine(), $throwable->getMessage(), $throwable->getFile());

            $encoder->write([
                ReactCommand::ACTION => Action::RESULT,
                self::RESULT => [
                    Bridge::SYSTEM_ERRORS => [$systemErrors],
                    Bridge::FILES_COUNT => 0,
                    Bridge::SYSTEM_ERRORS_COUNT => 1,
                ],
            ]);
            $encoder->end();
        };

        $encoder->on(ReactEvent::ERROR, $handleErrorCallback);

        // 2. collect diffs + errors from file processor
        $decoder->on(ReactEvent::DATA, function (array $json) use ($encoder, $configuration): void {
            $action = $json[ReactCommand::ACTION];
            if ($action !== Action::MAIN) {
                return;
            }

            $systemErrorsCount = 0;

            /** @var string[] $filePaths */
            $filePaths = $json[Bridge::FILES] ?? [];

            $errorAndFileDiffs = [];
            $systemErrors = [];

            foreach ($filePaths as $filePath) {
                try {
                    $smartFileInfo = new SmartFileInfo($filePath);
                    $file = new File($smartFileInfo, $smartFileInfo->getContents());
                    $currentErrorsAndFileDiffs = $this->fileProcessor->refactor($file, $configuration);

                    $errorAndFileDiffs = $this->parametersMerger->merge(
                        $errorAndFileDiffs,
                        $currentErrorsAndFileDiffs
                    );
                } catch (Throwable $throwable) {
                    ++$systemErrorsCount;

                    $errorMessage = sprintf('System error: "%s"', $throwable->getMessage());
                    $errorMessage .= 'Run Rector with "--debug" option and post the report here: https://github.com/rectorphp/rector/issues/new';
                    $systemErrors[] = new SystemError($throwable->getLine(), $errorMessage, $filePath);
                }
            }

            /**
             * this invokes all listeners listening $decoder->on(...) @see \Symplify\EasyParallel\Enum\ReactEvent::DATA
             */
            $encoder->write([
                ReactCommand::ACTION => Action::RESULT,
                self::RESULT => [
                    Bridge::FILE_DIFFS => $errorAndFileDiffs[Bridge::FILE_DIFFS] ?? [],
                    Bridge::FILES_COUNT => count($filePaths),
                    Bridge::SYSTEM_ERRORS => $systemErrors,
                    Bridge::SYSTEM_ERRORS_COUNT => $systemErrorsCount,
                ],
            ]);
        });

        $decoder->on(ReactEvent::ERROR, $handleErrorCallback);
    }
}
