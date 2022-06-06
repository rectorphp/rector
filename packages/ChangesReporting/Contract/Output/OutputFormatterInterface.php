<?php

declare (strict_types=1);
namespace Rector\ChangesReporting\Contract\Output;

use Rector\Core\ValueObject\Configuration;
use Rector\Core\ValueObject\ProcessResult;
interface OutputFormatterInterface
{
    public function getName() : string;
    public function report(\Rector\Core\ValueObject\ProcessResult $processResult, \Rector\Core\ValueObject\Configuration $configuration) : void;
}
