<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Output;

use Rector\ChangesReporting\Contract\Output\OutputFormatterInterface;
use Rector\ChangesReporting\Output\Factory\JsonOutputFactory;
use Rector\ValueObject\Configuration;
use Rector\ValueObject\ProcessResult;
final class JsonOutputFormatter implements OutputFormatterInterface
{
    /**
     * @var string
     */
    public const NAME = 'json';
    public function getName(): string
    {
        return self::NAME;
    }
    public function report(ProcessResult $processResult, Configuration $configuration): void
    {
        echo JsonOutputFactory::create($processResult, $configuration) . \PHP_EOL;
    }
}
