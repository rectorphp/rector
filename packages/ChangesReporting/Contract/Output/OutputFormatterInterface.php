<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Contract\Output;

use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
interface OutputFormatterInterface
{
    public function getName() : string;
    /**
     * @param \Rector\Core\ValueObject\ProcessResult $processResult
     * @param \Rector\Core\ValueObject\Configuration $configuration
     */
    public function report($processResult, $configuration) : void;
}
