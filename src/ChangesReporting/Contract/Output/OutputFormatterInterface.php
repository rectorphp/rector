<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Contract\Output;

use Rector\ValueObject\Configuration;
use Rector\ValueObject\ProcessResult;
interface OutputFormatterInterface
{
    public function getName() : string;
    public function report(ProcessResult $processResult, Configuration $configuration) : void;
}
