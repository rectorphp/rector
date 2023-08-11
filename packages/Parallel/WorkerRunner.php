<?php

declare (strict_types=1);
namespace Rector\Parallel;

use RectorPrefix202308\Clue\React\NDJson\Decoder;
use RectorPrefix202308\Clue\React\NDJson\Encoder;
use Rector\Core\Application\ApplicationFileProcessor;
use Rector\Core\StaticReflection\DynamicSourceLocatorDecorator;
use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\Error\SystemError;
use Rector\Parallel\ValueObject\Bridge;
use RectorPrefix202308\Symplify\EasyParallel\Enum\Action;
use RectorPrefix202308\Symplify\EasyParallel\Enum\ReactCommand;
use RectorPrefix202308\Symplify\EasyParallel\Enum\ReactEvent;
use Throwable;
final class WorkerRunner
{
    /**
     * @readonly
     * @var \Rector\Core\StaticReflection\DynamicSourceLocatorDecorator
     */
    private $dynamicSourceLocatorDecorator;
    /**
     * @readonly
     * @var \Rector\Core\Application\ApplicationFileProcessor
     */
    private $applicationFileProcessor;
    /**
     * @var string
     */
    private const RESULT = 'result';
    public function __construct(DynamicSourceLocatorDecorator $dynamicSourceLocatorDecorator, ApplicationFileProcessor $applicationFileProcessor)
    {
        $this->dynamicSourceLocatorDecorator = $dynamicSourceLocatorDecorator;
        $this->applicationFileProcessor = $applicationFileProcessor;
    }
    public function run(Encoder $encoder, Decoder $decoder, Configuration $configuration) : void
    {
        $this->dynamicSourceLocatorDecorator->addPaths($configuration->getPaths());
        // 1. handle system error
        $handleErrorCallback = static function (Throwable $throwable) use($encoder) : void {
            $systemError = new SystemError($throwable->getMessage(), $throwable->getFile(), $throwable->getLine());
            $encoder->write([ReactCommand::ACTION => Action::RESULT, self::RESULT => [Bridge::SYSTEM_ERRORS => [$systemError], Bridge::FILES_COUNT => 0, Bridge::SYSTEM_ERRORS_COUNT => 1]]);
            $encoder->end();
        };
        $encoder->on(ReactEvent::ERROR, $handleErrorCallback);
        // 2. collect diffs + errors from file processor
        $decoder->on(ReactEvent::DATA, function (array $json) use($encoder, $configuration) : void {
            $action = $json[ReactCommand::ACTION];
            if ($action !== Action::MAIN) {
                return;
            }
            /** @var string[] $filePaths */
            $filePaths = $json[Bridge::FILES] ?? [];
            $systemErrorsAndFileDiffs = $this->applicationFileProcessor->processFiles($filePaths, $configuration);
            /**
             * this invokes all listeners listening $decoder->on(...) @see \Symplify\EasyParallel\Enum\ReactEvent::DATA
             */
            $encoder->write([ReactCommand::ACTION => Action::RESULT, self::RESULT => [Bridge::FILE_DIFFS => $systemErrorsAndFileDiffs[Bridge::FILE_DIFFS], Bridge::FILES_COUNT => \count($filePaths), Bridge::SYSTEM_ERRORS => $systemErrorsAndFileDiffs[Bridge::SYSTEM_ERRORS], Bridge::SYSTEM_ERRORS_COUNT => $systemErrorsAndFileDiffs[Bridge::SYSTEM_ERRORS_COUNT]]]);
        });
        $decoder->on(ReactEvent::ERROR, $handleErrorCallback);
    }
}
