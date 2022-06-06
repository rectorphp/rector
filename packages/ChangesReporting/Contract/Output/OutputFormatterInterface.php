<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\ChangesReporting\Contract\Output;

use RectorPrefix20220606\Rector\Core\ValueObject\Configuration;
use RectorPrefix20220606\Rector\Core\ValueObject\ProcessResult;
interface OutputFormatterInterface
{
    public function getName() : string;
    public function report(ProcessResult $processResult, Configuration $configuration) : void;
}
